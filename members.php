<?php
require_once __DIR__ . '/functions.php';
$members = readData(MEMBERS_FILE);
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'add') {
        $nama = trim($_POST['nama'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $tel = trim($_POST['telepon'] ?? '');
        if (strlen($nama) < 3) $errors[] = 'Nama minimal 3 karakter';
        if (!is_valid_email($email)) $errors[] = 'Email tidak valid';
        if (empty($errors)) {
            $id = nextID(MEMBERS_FILE, 'M');
            appendRow(MEMBERS_FILE, [$id, $nama, $email, $tel, 'aktif']);
            header('Location: members.php');
            exit;
        }
    }
    if ($action === 'edit') {
        $id = $_POST['id'] ?? '';
        foreach ($members as &$m) {
            if ($m[0] === $id) {
                $m[1] = trim($_POST['nama'] ?? $m[1]);
                $email = trim($_POST['email'] ?? $m[2]);
                if (is_valid_email($email)) $m[2] = $email;
                $m[3] = trim($_POST['telepon'] ?? $m[3]);
                $m[4] = trim($_POST['status'] ?? $m[4]);
                break;
            }
        }
        writeTable(MEMBERS_FILE, $members);
        header('Location: members.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Anggota - Perpustakaan</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <div class="header">
    <h2>Anggota</h2>
    <div class="nav">
      <a class="btn" href="index.php">Menu</a>
      <a class="btn" href="books.php">Buku</a>
    </div>
  </div>

  <div class="card">
    <h3>Daftar Anggota</h3>
    <table class="table">
      <thead><tr><th>ID</th><th>Nama</th><th>Email</th><th>Telp</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
        <?php foreach ($members as $m): ?>
          <tr>
            <td><?=h($m[0])?></td>
            <td><?=h($m[1])?></td>
            <td><?=h($m[2])?></td>
            <td><?=h($m[3])?></td>
            <td><?=h($m[4])?></td>
            <td><a href="members.php?edit=<?=h($m[0])?>" class="small">Edit</a></td>
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
        $edit = array_values(array_filter($members, fn($x)=>$x[0]===$eid))[0] ?? null;
    ?>
      <h3>Edit Anggota <?=h($eid)?></h3>
      <form method="POST">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" value="<?=h($eid)?>">
        <div class="form-row"><label>Nama</label><input class="input" name="nama" value="<?=h($edit[1] ?? '')?>"></div>
        <div class="form-row"><label>Email</label><input class="input" name="email" value="<?=h($edit[2] ?? '')?>"></div>
        <div class="form-row"><label>Telepon</label><input class="input" name="telepon" value="<?=h($edit[3] ?? '')?>"></div>
        <div class="form-row"><label>Status</label>
          <select class="input" name="status">
            <option value="aktif" <?=($edit[4] ?? '')==='aktif'?'selected':''?>>Aktif</option>
            <option value="nonaktif" <?=($edit[4] ?? '')==='nonaktif'?'selected':''?>>Nonaktif</option>
          </select>
        </div>
        <div><button class="btn">Simpan</button></div>
      </form>
    <?php else: ?>
      <h3>Registrasi Anggota</h3>
      <form method="POST">
        <input type="hidden" name="action" value="add">
        <div class="form-row"><label>Nama</label><input class="input" name="nama"></div>
        <div class="form-row"><label>Email</label><input class="input" name="email"></div>
        <div class="form-row"><label>Telepon</label><input class="input" name="telepon"></div>
        <div><button class="btn">Daftar</button></div>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
