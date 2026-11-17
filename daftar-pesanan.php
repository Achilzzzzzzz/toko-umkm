<?php

declare(strict_types=1);

require __DIR__ . '/data.php';
require __DIR__ . '/functions.php';

$daftarPesanan = array_reverse(bacaPesanan(FILE_PESANAN));
$totalOmzet = array_sum(array_column($daftarPesanan, 'total'));
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar Pesanan · <?= e($namaToko) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="kepala">
        <h1><?= e($namaToko) ?></h1>
        <nav class="menu">
            <a href="index.php">Katalog</a>
            <a href="pesan.php">Pesan</a>
            <a href="daftar-pesanan.php" class="aktif">Daftar Pesanan</a>
        </nav>
    </header>

    <main class="wadah">
        <h2>Daftar Pesanan Masuk</h2>
        <p class="info">
            Total <strong><?= count($daftarPesanan) ?></strong> pesanan · Total Omzet: <strong><?= formatRupiah($totalOmzet) ?></strong>
        </p>

        <?php if (count($daftarPesanan) === 0): ?>
            <p class="kosong">Belum ada pesanan yang masuk.</p>
        <?php else: ?>
            <div class="tabel-scroll">
                <table class="tabel">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Waktu</th>
                            <th>Nama</th>
                            <th>WhatsApp</th>
                            <th>Produk</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($daftarPesanan as $pesanan): ?>
                            <tr>
                                <td><strong><?= e($pesanan['kode']) ?></strong></td>
                                <td><?= e($pesanan['waktu']) ?></td>
                                <td><?= e($pesanan['nama']) ?></td>
                                <td>
                                    <a href="https://wa.me/<?= preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $pesanan['whatsapp'])) ?>" target="_blank" style="color: #2e8b57; font-weight: 500;">
                                        <?= e($pesanan['whatsapp']) ?>
                                    </a>
                                </td>
                                <td><?= e($pesanan['produk']) ?></td>
                                <td style="text-align: center;"><?= (int)$pesanan['jumlah'] ?></td>
                                <td style="font-weight: 600; color: #b4610f;"><?= formatRupiah((int)$pesanan['total']) ?></td>
                                <td><?= e($pesanan['catatan'] !== '' ? $pesanan['catatan'] : '-') ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <footer class="kaki">
        <p>&copy; <?= date('Y') ?> Pemrograman Web · Informatika TSU</p>
    </footer>
</body>
</html>
