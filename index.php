<?php
require_once __DIR__ . '/functions.php';
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Perpustakaan - Menu</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <div class="header">
    <div>
      <h1>Sistem Perpustakaan (PHP Native)</h1>
      <div class="small">File-based (data disimpan di folder data/)</div>
    </div>
    <div class="nav">
      <a class="btn" href="books.php">Buku</a>
      <a class="btn" href="members.php">Anggota</a>
      <a class="btn" href="loans.php">Peminjaman</a>
      <a class="btn" href="returns.php">Pengembalian</a>
    </div>
  </div>

  <div class="card">
    <h3>Petunjuk singkat</h3>
    <ul>
      <li>Data disimpan di <code>data/*.txt</code> (pipe-separated).</li>
      <li>Gunakan tab Buku/Anggota/Peminjaman/Pengembalian untuk operasi.</li>
      <li>Script ini untuk pembelajaran/demo; jangan gunakan di produksi tanpa proteksi.</li>
    </ul>
  </div>
</div>
</body>
</html>
