<?php
require_once __DIR__ . '/functions.php';

$books = readData(BOOKS_FILE);
$members = readData(MEMBERS_FILE);
$loans = readData(LOANS_FILE);
$returns = readData(RETURNS_FILE);
global $BORROW_LIMIT, $BORROW_DAYS, $FINE_PER_DAY;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'borrow') {
        $member_id = $_POST['member_id'] ?? '';
        $book_id = $_POST['book_id'] ?? '';
        // validate member
        $foundMember = null; foreach ($members as $m) if ($m[0] === $member_id) { $foundMember = $m; break; }
        if (!$foundMember) $errors[] = 'Member tidak ditemukan';
        // validate book
        $foundBook = null; foreach ($books as $b) if ($b[0] === $book_id) { $foundBook = $b; break; }
        if (!$foundBook) $errors[] = 'Buku tidak ditemukan';
        if ($foundBook && ($foundBook[5] ?? '') !== 'tersedia') $errors[] = 'Buku tidak tersedia';
        // check limit
        $countBorrowed = 0;
        foreach ($loans as $l) if ($l[1] === $member_id && $l[5] !== '1') $countBorrowed++;
        if ($countBorrowed >= $BORROW_LIMIT) $errors[] = "Member telah mencapai batas peminjaman ({$BORROW_LIMIT})";
        if (empty($errors)) {
            $id = nextID(LOANS_FILE, 'L');
            $borrow_date = date('Y-m-d');
            $due = date('Y-m-d', strtotime("+{$BORROW_DAYS} days"));
            appendRow(LOANS_FILE, [$id, $member_id, $book_id, $borrow_date, $due, '0']);
            // update book status to dipinjam
            foreach ($books as &$b) if ($b[0] === $book_id) { $b[5] = 'dipinjam'; break; }
            writeTable(BOOKS_FILE, $books);
            header('Location: loans.php');
            exit;
        }
    }
    if ($action === 'return') {
        $loan_id = $_POST['loan_id'] ?? '';
        $foundLoan = null;
        foreach ($loans as &$l) {
            if ($l[0] === $loan_id) { $foundLoan = &$l; break; }
        }
        if ($foundLoan) {
            $return_date = date('Y-m-d');
            $due = $foundLoan[4];
            $daysLate = max(0, (int)floor((strtotime($return_date) - strtotime($due)) / 86400));
            $fine = $daysLate * $FINE_PER_DAY;
            $foundLoan[5] = '1'; // mark returned
            writeTable(LOANS_FILE, $loans);
            // update book status
            foreach ($books as &$b) if ($b[0] === $foundLoan[2]) { $b[5] = 'tersedia'; break; }
            writeTable(BOOKS_FILE, $books);
            // append return record
            $rid = nextID(RETURNS_FILE, 'R');
            appendRow(RETURNS_FILE, [$rid, $loan_id, $return_date, $fine]);
            header('Location: loans.php');
            exit;
        } else {
            $errors[] = 'Loan ID tidak ditemukan';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Peminjaman - Perpustakaan</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <div class="header">
    <h2>Peminjaman</h2>
    <div class="nav">
      <a class="btn" href="index.php">Menu</a>
      <a class="btn" href="books.php">Buku</a>
      <a class="btn" href="members.php">Anggota</a>
      <a class="btn" href="returns.php">Pengembalian</a>
    </div>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="card" style="color:#ffb4b4;background:#330000;">
      <?php foreach ($errors as $e) echo h($e)."<br>"; ?>
    </div>
  <?php endif; ?>

  <div class="card" style="display:flex;gap:12px">
    <div style="flex:1">
      <h3>Form Peminjaman</h3>
      <form method="POST">
        <input type="hidden" name="action" value="borrow">
        <div class="form-row"><label>Anggota</label>
          <select class="input" name="member_id">
            <?php foreach ($members as $m): ?>
              <option value="<?=h($m[0])?>"><?=h($m[1])?> (<?=h($m[0])?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row"><label>Buku (hanya tersedia)</label>
          <select class="input" name="book_id">
            <?php foreach ($books as $b): if (($b[5] ?? '') === 'tersedia'): ?>
              <option value="<?=h($b[0])?>"><?=h($b[1])?> - <?=h($b[3])?> (<?=h($b[0])?>)</option>
            <?php endif; endforeach; ?>
          </select>
        </div>
        <div><button class="btn">Proses Peminjaman</button></div>
      </form>
    </div>

    <div style="width:420px">
      <h4>Riwayat Peminjaman</h4>
      <table class="table"><thead><tr><th>ID</th><th>Anggota</th><th>Buku</th><th>Pinjam</th><th>Jatuh Tempo</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($loans as $l):
            $en = enrichLoan($l); $loan = $en[0]; $member = $en[1]; $book = $en[2];
            $isReturned = $loan[5] === '1';
            $late = 0;
            if (!$isReturned && strtotime(date('Y-m-d')) > strtotime($loan[4])) {
                $late = (int)floor((strtotime(date('Y-m-d')) - strtotime($loan[4]))/86400);
            }
        ?>
          <tr>
            <td><?=h($loan[0])?></td>
            <td><?=h($member[1] ?? 'Unknown')?></td>
            <td><?=h($book[1] ?? 'Unknown')?></td>
            <td><?=h($loan[3])?></td>
            <td><?=h($loan[4])?></td>
            <td><?= $isReturned ? 'Dikembalikan' : ($late ? "Terlambat {$late} hari" : 'Dipinjam') ?></td>
            <td>
              <?php if (!$isReturned): ?>
                <form method="POST" style="display:inline" onsubmit="return confirm('Proses pengembalian?')">
                  <input type="hidden" name="action" value="return">
                  <input type="hidden" name="loan_id" value="<?=h($loan[0])?>">
                  <button class="small">Kembalikan</button>
                </form>
              <?php else: ?> - <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody></table>
    </div>
  </div>

  <div class="card small">
    <p>Limit peminjaman: <?=h($BORROW_LIMIT)?> buku. Lama pinjam default: <?=h($BORROW_DAYS)?> hari. Denda: Rp <?=rp($FINE_PER_DAY)?>/hari.</p>
  </div>
</div>
</body>
</html>
