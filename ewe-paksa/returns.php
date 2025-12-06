<?php
require_once __DIR__ . '/functions.php';
$returns = readData(RETURNS_FILE);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Pengembalian - Perpustakaan</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="container">
  <div class="header">
    <h2>Riwayat Pengembalian</h2>
    <div class="nav">
      <a class="btn" href="index.php">Menu</a>
      <a class="btn" href="loans.php">Peminjaman</a>
    </div>
  </div>

  <div class="card">
    <table class="table">
      <thead><tr><th>ID</th><th>Loan ID</th><th>Tgl Kembali</th><th>Denda</th></tr></thead>
      <tbody>
        <?php foreach ($returns as $r): ?>
          <tr>
            <td><?=h($r[0])?></td>
            <td><?=h($r[1])?></td>
            <td><?=h($r[2])?></td>
            <td><?=rp($r[3])?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>
