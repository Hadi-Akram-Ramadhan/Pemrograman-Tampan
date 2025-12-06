<?php
require_once __DIR__ . '/functions.php';

$books = readData(BOOKS_FILE);
$errors = [];

// handle POST actions: add, edit, delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $judul = trim($_POST['judul'] ?? '');
        $penulis = trim($_POST['penulis'] ?? '');
        $kategori = trim($_POST['kategori'] ?? '');
        $tahun = trim($_POST['tahun'] ?? '');
        $status = trim($_POST['status'] ?? 'tersedia');
        $kond = $_POST['kondisi'] ?? [];
        if (strlen($judul) < 3) $errors[] = 'Judul minimal 3 karakter';
        if (!is_numeric($tahun) || intval($tahun) < 1900) $errors[] = 'Tahun tidak valid';
        if (empty($errors)) {
            $id = nextID(BOOKS_FILE, 'B');
            appendRow(BOOKS_FILE, [$id, $judul, $penulis, $kategori, $tahun, $status, implode(',', $kond)]);
            header('Location: books.php');
            exit;
        }
    }
    if ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        foreach ($books as &$b) {
            if ($b[0] === $id) {
                $b[1] = trim($_POST['judul'] ?? $b[1]);
                $b[2] = trim($_POST['penulis'] ?? $b[2]);
                $b[3] = trim($_POST['kategori'] ?? $b[3]);
                $b[4] = trim($_POST['tahun'] ?? $b[4]);
                $b[5] = trim($_POST['status'] ?? $b[5]);
                $b[6] = implode(',', $_POST['kondisi'] ?? explode(',', $b[6]));
                break;
            }
        }
        writeTable(BOOKS_FILE, $books);
        header('Location: books.php');
        exit;
    }
    if ($action === 'delete') {
        $id = $_POST['id'] ?? '';
        $new = array_filter($books, function($r) use ($id){ return $r[0] !== $id; });
        writeTable(BOOKS_FILE, $new);
        header('Location: books.php');
        exit;
    }
}

// search / filter
$search = trim($_GET['search'] ?? '');
$category = trim($_GET['category'] ?? '');

$display_books = array_values(array_filter($books, function($b) use ($search, $category) {
    if ($category && strtolower($b[3]) !== strtolower($category)) return false;
    if (!$search) return true;
    $s = strtolower($search);
    return (strpos(strtolower($b[1]), $s) !== false) || (strpos(strtolower($b[2]), $s) !== false) || (strpos(strtolower($b[3]), $s) !== false);
}));

// categories for dropdown
$cats = [];
foreach ($books as $b) if (!empty($b[3])) $cats[strtolower($b[3])] = $b[3];
ksort($cats);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Buku - Perpustakaan</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <div class="header">
    <h2>Buku</h2>
    <div class="nav">
      <a class="btn" href="index.php">Menu</a>
      <a class="btn" href="members.php">Anggota</a>
      <a class="btn" href="loans.php">Peminjaman</a>
    </div>
  </div>

  <div class="card">
    <form method="GET">
      <input type="text" name="search" class="input" placeholder="Cari judul/penulis/kategori" value="<?=h($search)?>">
      <select name="category" class="input">
        <option value="">Semua Kategori</option>
        <?php foreach ($cats as $c): ?>
          <option value="<?=h($c)?>" <?=strtolower($category)===strtolower($c)?'selected':''?>><?=h($c)?></option>
        <?php endforeach; ?>
      </select>
      <button class="btn">Cari</button>
    </form>
  </div>

  <div class="card">
    <h3>Daftar Buku</h3>
    <table class="table">
      <thead><tr><th>ID</th><th>Judul</th><th>Penulis</th><th>Kategori</th><th>Tahun</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($display_books as $b): ?>
          <tr>
            <td><?=h($b[0])?></td>
            <td><?=h($b[1])?></td>
            <td><?=h($b[2])?></td>
            <td><?=h($b[3])?></td>
            <td><?=h($b[4])?></td>
            <td><?=h($b[5])?></td>
            <td>
              <a href="books.php?edit=<?=h($b[0])?>" class="small">Edit</a>
              |
              <form method="POST" style="display:inline" onsubmit="return confirm('Hapus buku?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?=h($b[0])?>">
                <button class="small" style="background:none;border:none;color:#cbd5e1;cursor:pointer">Hapus</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>

  <div class="card">
    <?php if (!empty($errors)): ?>
      <div style="color:#ffb4b4;background:#330000;padding:8px;border-radius:6px;margin-bottom:8px">
        <?php foreach ($errors as $e) echo h($e)."<br>"; ?>
      </div>
    <?php endif; ?>

    <?php if (!empty($_GET['edit'])):
        $eid = $_GET['edit'];
        $edit = array_values(array_filter($books, fn($x)=>$x[0]===$eid))[0] ?? null;
    ?>
      <h3>Edit Buku <?=h($eid)?></h3>
      <form method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?=h($eid)?>">
        <div class="form-row"><label>Judul</label><input class="input" name="judul" value="<?=h($edit[1] ?? '')?>"></div>
        <div class="form-row"><label>Penulis</label><input class="input" name="penulis" value="<?=h($edit[2] ?? '')?>"></div>
        <div class="form-row"><label>Kategori</label><input class="input" name="kategori" value="<?=h($edit[3] ?? '')?>"></div>
        <div class="form-row"><label>Tahun</label><input class="input" name="tahun" value="<?=h($edit[4] ?? '')?>"></div>
        <div class="form-row"><label>Status</label>
          <label><input type="radio" name="status" value="tersedia" <?=($edit[5] ?? '') === 'tersedia' ? 'checked' : ''?>> Tersedia</label>
          <label><input type="radio" name="status" value="dipinjam" <?=($edit[5] ?? '') === 'dipinjam' ? 'checked' : ''?>> Dipinjam</label>
        </div>
        <div class="form-row"><label>Kondisi</label>
          <?php $k = explode(',', $edit[6] ?? ''); ?>
          <label><input type="checkbox" name="kondisi[]" value="baru" <?=in_array('baru',$k)?'checked':''?>> Baru</label>
          <label><input type="checkbox" name="kondisi[]" value="bekas" <?=in_array('bekas',$k)?'checked':''?>> Bekas</label>
        </div>
        <div><button class="btn">Simpan</button></div>
      </form>
    <?php else: ?>
      <h3>Tambah Buku</h3>
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="form-row"><label>Judul</label><input class="input" name="judul"></div>
        <div class="form-row"><label>Penulis</label><input class="input" name="penulis"></div>
        <div class="form-row"><label>Kategori</label><input class="input" name="kategori" value="Umum"></div>
        <div class="form-row"><label>Tahun</label><input class="input" name="tahun" placeholder="2024"></div>
        <div class="form-row"><label>Status</label>
          <label><input type="radio" name="status" value="tersedia" checked> Tersedia</label>
          <label><input type="radio" name="status" value="dipinjam"> Dipinjam</label>
        </div>
        <div class="form-row"><label>Kondisi</label>
          <label><input type="checkbox" name="kondisi[]" value="baru"> Baru</label>
          <label><input type="checkbox" name="kondisi[]" value="bekas"> Bekas</label>
        </div>
        <div><button class="btn">Tambah Buku</button></div>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
