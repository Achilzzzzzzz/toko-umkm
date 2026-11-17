<?php

declare(strict_types=1);

/**
 * Escape teks untuk mencegah serangan Cross-Site Scripting (XSS).
 */
function e(string $teks): string
{
    return htmlspecialchars($teks, ENT_QUOTES, 'UTF-8');
}

/**
 * Format angka nominal ke standar mata uang Rupiah.
 */
function formatRupiah(int|float $angka): string
{
    return 'Rp' . number_format($angka, 0, ',', '.');
}

/**
 * Tentukan label status ketersediaan berdasarkan jumlah stok.
 */
function statusStok(int $stok): string
{
    if ($stok === 0) {
        return 'Habis';
    }
    if ($stok <= 5) {
        return 'hampir habis';
    }
    return 'Tersedia';
}

/**
 * Tentukan kelas CSS badge berdasarkan status stok.
 */
function kelasBadge(string $status): string
{
    return match ($status) {
        'Tersedia' => 'badge badge--aman',
        'Menipis' => 'badge badge--hampir habis',
        default => 'badge badge--habis',
    };
}

/**
 * Filter daftar produk berdasarkan kategori.
 */
function filterKategori(array $produk, string $kategori): array
{
    if ($kategori === 'Semua') {
        return $produk;
    }
    return array_filter($produk, fn(array $p): bool => $p['kategori'] === $kategori);
}

/**
 * Cari produk berdasarkan kata kunci pada nama produk (case-insensitive).
 */
function cariProduk(array $produk, string $kataKunci): array
{
    if ($kataKunci === '') {
        return $produk;
    }
    return array_filter($produk, fn(array $p): bool => stripos($p['nama'], $kataKunci) !== false);
}

/**
 * Cari produk berdasarkan kode unik produk.
 */
function cariProdukByKode(array $produk, string $kode): ?array
{
    foreach ($produk as $item) {
        if ($item['kode'] === $kode) {
            return $item;
        }
    }
    return null;
}

/**
 * Validasi input form pemesanan produk.
 * Mengembalikan array associative kesalahan [field => pesan_galat].
 */
function validasiPesanan(array $input, array $daftarProduk): array
{
    $kesalahan = [];
    $panjangNama = mb_strlen($input['nama']);

    // Validasi Nama (3 - 50 karakter)
    if ($panjangNama === 0) {
        $kesalahan['nama'] = 'Nama wajib diisi.';
    } elseif ($panjangNama < 3 || $panjangNama > 50) {
        $kesalahan['nama'] = 'Nama harus 3-50 karakter.';
    }

    // Validasi Nomor WhatsApp (diawali 08 atau 628, panjang 10-14 digit)
    $waValid = filter_var($input['whatsapp'], FILTER_VALIDATE_REGEXP, [
        'options' => ['regexp' => '/^(08|628)[0-9]{8,12}$/']
    ]);

    if ($input['whatsapp'] === '') {
        $kesalahan['whatsapp'] = 'Nomor WhatsApp wajib diisi.';
    } elseif ($waValid === false) {
        $kesalahan['whatsapp'] = 'Gunakan format 08xxxxxxxxxx (angka saja).';
    }

    // Validasi Pilihan Produk & Ketersediaan Stok
    $produk = cariProdukByKode($daftarProduk, $input['kode_produk']);
    if ($produk === null) {
        $kesalahan['kode_produk'] = 'Pilih produk dari daftar.';
    } elseif ($produk['stok'] === 0) {
        $kesalahan['kode_produk'] = 'Maaf, stok produk ini habis.';
    }

    // Validasi Jumlah Pesanan (1 - 20 dan tidak melebihi stok yang ada)
    $jumlah = filter_var($input['jumlah'], FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1, 'max_range' => 20]
    ]);

    if ($jumlah === false) {
        $kesalahan['jumlah'] = 'Jumlah harus angka 1-20.';
    } elseif ($produk !== null && $jumlah > $produk['stok']) {
        $kesalahan['jumlah'] = "Jumlah melebihi stok (tersisa {$produk['stok']}).";
    }

    // Validasi Catatan (maksimal 200 karakter)
    if (mb_strlen($input['catatan']) > 200) {
        $kesalahan['catatan'] = 'Catatan maksimal 200 karakter.';
    }

    return $kesalahan;
}

/**
 * Baca seluruh daftar pesanan dari file JSON.
 */
function bacaPesanan(string $file): array
{
    if (!file_exists($file)) {
        return [];
    }
    $isi = json_decode((string)file_get_contents($file), true);
    return is_array($isi) ? $isi : [];
}

/**
 * Simpan data pesanan baru ke file JSON.
 */
function simpanPesanan(string $file, array $pesananBaru): void
{
    $semua = bacaPesanan($file);
    $semua[] = $pesananBaru;
    $json = json_encode($semua, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    file_put_contents($file, $json, LOCK_EX);
}

/**
 * Buat kode unik pesanan format PSN-0001, PSN-0002, dst.
 */
function buatKodePesanan(int $nomorUrut): string
{
    return sprintf('PSN-%04d', $nomorUrut);
}
