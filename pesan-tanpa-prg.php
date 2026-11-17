<?php
// FILE DEMO: sengaja tidak memakai redirect. Refresh setelah sukses dapat menyimpan pesanan ganda.
declare(strict_types=1);

require __DIR__ . '/data.php';
require __DIR__ . '/functions.php';

$input = [
    'nama' => '',
    'whatsapp' => '',
    'kode_produk' => (string)filter_input(INPUT_GET, 'produk'),
    'jumlah' => '1',
    'catatan' => ''
];
$kesalahan = [];
$kodeTersimpan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = [
        'nama' => trim((string)filter_input(INPUT_POST, 'nama')),
        'whatsapp' => trim((string)filter_input(INPUT_POST, 'whatsapp')),
        'kode_produk' => (string)filter_input(INPUT_POST, 'kode_produk'),
        'jumlah' => trim((string)filter_input(INPUT_POST, 'jumlah')),
        'catatan' => trim((string)filter_input(INPUT_POST, 'catatan'))
    ];

    $kesalahan = validasiPesanan($input, $daftarProduk);

    if (count($kesalahan) === 0) {
        $produk = cariProdukByKode($daftarProduk, $input['kode_produk']);
        $jumlah = (int)$input['jumlah'];
        $kode = buatKodePesanan(count(bacaPesanan(FILE_PESANAN)) + 1);

        simpanPesanan(FILE_PESANAN, [
            'kode' => $kode,
            'waktu' => date('Y-m-d H:i'),
            'nama' => $input['nama'],
            'whatsapp' => $input['whatsapp'],
            'produk' => $produk['nama'],
            'harga' => $produk['harga'],
            'jumlah' => $jumlah,
            'total' => $produk['harga'] * $jumlah,
            'catatan' => $input['catatan']
        ]);

        $kodeTersimpan = $kode;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo Tanpa PRG · <?= e($namaToko) ?></title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="kepala">
        <h1><?= e($namaToko) ?></h1>
        <nav class="menu">
            <a href="index.php">Katalog</a>
            <a href="pesan.php">Pesan (PRG)</a>
            <a href="pesan-tanpa-prg.php" class="aktif">Demo Tanpa PRG</a>
            <a href="daftar-pesanan.php">Daftar Pesanan</a>
        </nav>
    </header>

    <main class="wadah sempit">
        <h2>Demo Form Tanpa PRG</h2>
        <p class="alert alert--gagal">
            <strong>Perhatian:</strong> Halaman ini khusus demonstrasi Hands-On 5 (HO-5).
            Form ini sengaja tidak melakukan redirect, sehingga jika halaman direfresh (F5),
            browser akan meminta konfirmasi pengiriman ulang form dan menyebabkan data ganda.
        </p>

        <?php if ($kodeTersimpan !== ''): ?>
            <p class="alert alert--sukses">
                Pesanan <strong><?= e($kodeTersimpan) ?></strong> tersimpan.
                Coba tekan <strong>F5 (Refresh)</strong> untuk melihat risiko duplikasi pesanan tanpa pola PRG!
            </p>
        <?php endif; ?>

        <?php if (count($kesalahan) > 0): ?>
            <p class="alert alert--gagal">Periksa kembali isian formulir.</p>
        <?php endif; ?>

        <form method="post" action="pesan-tanpa-prg.php" class="form" novalidate>
            <div class="isian">
                <label for="nama">Nama lengkap</label>
                <input id="nama" name="nama" value="<?= e($input['nama']) ?>">
                <?php if (isset($kesalahan['nama'])): ?>
                    <small class="galat"><?= e($kesalahan['nama']) ?></small>
                <?php endif; ?>
            </div>

            <div class="isian">
                <label for="whatsapp">Nomor WhatsApp</label>
                <input id="whatsapp" name="whatsapp" value="<?= e($input['whatsapp']) ?>">
                <?php if (isset($kesalahan['whatsapp'])): ?>
                    <small class="galat"><?= e($kesalahan['whatsapp']) ?></small>
                <?php endif; ?>
            </div>

            <div class="isian">
                <label for="kode_produk">Kode produk</label>
                <select id="kode_produk" name="kode_produk">
                    <option value="">-- pilih --</option>
                    <?php foreach ($daftarProduk as $p): ?>
                        <option value="<?= e($p['kode']) ?>"
                            <?= $p['kode'] === $input['kode_produk'] ? 'selected' : '' ?>
                            <?= $p['stok'] === 0 ? 'disabled' : '' ?>>
                            <?= e($p['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($kesalahan['kode_produk'])): ?>
                    <small class="galat"><?= e($kesalahan['kode_produk']) ?></small>
                <?php endif; ?>
            </div>

            <div class="isian">
                <label for="jumlah">Jumlah</label>
                <input id="jumlah" name="jumlah" type="number" min="1" max="20" value="<?= e($input['jumlah']) ?>">
                <?php if (isset($kesalahan['jumlah'])): ?>
                    <small class="galat"><?= e($kesalahan['jumlah']) ?></small>
                <?php endif; ?>
            </div>

            <div class="isian">
                <label for="catatan">Catatan</label>
                <textarea id="catatan" name="catatan"><?= e($input['catatan']) ?></textarea>
                <?php if (isset($kesalahan['catatan'])): ?>
                    <small class="galat"><?= e($kesalahan['catatan']) ?></small>
                <?php endif; ?>
            </div>

            <button type="submit">Kirim (Demo Tanpa PRG)</button>
        </form>

        <p style="margin-top: 18px;">
            <a href="pesan.php">Kembali ke form PRG</a> · <a href="daftar-pesanan.php">Daftar pesanan</a>
        </p>
    </main>

    <footer class="kaki">
        <p>&copy; <?= date('Y') ?> Pemrograman Web · Informatika TSU</p>
    </footer>
</body>
</html>
