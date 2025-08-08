<?php
// File: includes/functions.php

/**
 * Mengubah angka menjadi format mata uang Rupiah.
 * Contoh: 350000 akan menjadi "Rp 350.000"
 *
 * @param int|float $angka Angka yang akan diformat.
 * @return string Angka dalam format Rupiah.
 */
function format_rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

/**
 * Menghasilkan kode pemesanan yang unik dan mudah dibaca.
 * Terus mencoba hingga menemukan kode yang belum ada di database.
 * @param PDO $pdo Objek koneksi database.
 * @return string Kode pemesanan unik (contoh: BOOK-A4D9F1).
 */
function generate_booking_code(PDO $pdo) {
    do {
        // Hasilkan 6 karakter acak (kombinasi huruf besar dan angka)
        $karakter = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $panjang_karakter = strlen($karakter);
        $random_string = '';
        for ($i = 0; $i < 6; $i++) {
            $random_string .= $karakter[rand(0, $panjang_karakter - 1)];
        }
        
        $booking_code = 'BOOK-' . $random_string;

        // Cek apakah kode sudah ada di database untuk memastikan keunikan
        $stmt = $pdo->prepare("SELECT id_pemesanan FROM pemesanan WHERE kode_pemesanan = ?");
        $stmt->execute([$booking_code]);
        $exists = $stmt->fetch();

    } while ($exists); // Ulangi proses jika kode yang dihasilkan sudah ada

    return $booking_code;
}

/**
 * Menghitung selisih hari antara dua tanggal.
 * Berguna untuk menghitung total biaya sewa.
 *
 * @param string $tanggal_mulai Tanggal awal (format: Y-m-d H:i:s).
 * @param string $tanggal_selesai Tanggal akhir (format: Y-m-d H:i:s).
 * @return int Jumlah hari.
 */
function hitung_durasi_sewa($tanggal_mulai, $tanggal_selesai) {
    $mulai = new DateTime($tanggal_mulai);
    $selesai = new DateTime($tanggal_selesai);
    $interval = $mulai->diff($selesai);
    
    // Menggunakan ceil untuk membulatkan ke atas. 
    // Jika sewa lebih dari 24 jam, dihitung sebagai 2 hari.
    $total_jam = ($interval->days * 24) + $interval->h;
    if ($total_jam <= 0) {
        return 1;
    }
    return ceil($total_jam / 24);
}

/**
 * Mengunggah file ke direktori yang ditentukan dengan aman.
 *
 * @param array $file_input Data file dari $_FILES['nama_input'].
 * @param string $upload_dir Direktori tujuan (contoh: '../assets/img/mobil/').
 * @param array $allowed_types Tipe file yang diizinkan (contoh: ['jpg', 'jpeg', 'png']).
 * @param int $max_size Ukuran file maksimum dalam byte.
 * @return string|array Mengembalikan nama file jika berhasil, atau array berisi error jika gagal.
 */
function upload_file($file_input, $upload_dir, $allowed_types = ['jpg', 'jpeg', 'png', 'gif'], $max_size = 5000000) {
    $file_name = $file_input['name'];
    $file_tmp = $file_input['tmp_name'];
    $file_size = $file_input['size'];
    $file_error = $file_input['error'];

    // 1. Cek jika ada error saat upload
    if ($file_error !== UPLOAD_ERR_OK) {
        return ['error' => 'Terjadi error saat mengunggah file. Kode: ' . $file_error];
    }

    // 2. Ambil ekstensi file
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // 3. Validasi tipe file
    if (!in_array($file_ext, $allowed_types)) {
        return ['error' => 'Tipe file tidak diizinkan. Hanya: ' . implode(', ', $allowed_types)];
    }

    // 4. Validasi ukuran file
    if ($file_size > $max_size) {
        return ['error' => 'Ukuran file terlalu besar. Maksimum: ' . ($max_size / 1000000) . ' MB'];
    }

    // 5. Buat nama file baru yang unik untuk menghindari penimpaan file
    $new_file_name = uniqid('', true) . '.' . $file_ext;
    $destination = $upload_dir . $new_file_name;

    // 6. Pindahkan file ke direktori tujuan
    if (move_uploaded_file($file_tmp, $destination)) {
        return $new_file_name; // Sukses, kembalikan nama file baru
    } else {
        return ['error' => 'Gagal memindahkan file yang diunggah.'];
    }
}


/**
 * Mengalihkan pengguna ke halaman lain sambil mengirimkan pesan (flash message).
 *
 * @param string $url URL tujuan.
 * @param string $message Pesan yang akan ditampilkan.
 * @param string $type Tipe pesan ('success' atau 'error').
 * @return void
 */
function redirect_with_message($url, $message, $type = 'success') {
    // Cek apakah URL sudah memiliki parameter
    $separator = (strpos($url, '?') === false) ? '?' : '&';
    
    // Bangun URL baru dengan parameter status
    $redirect_url = $url . $separator . 'status_type=' . $type . '&status_msg=' . urlencode($message);
    
    header("Location: " . $redirect_url);
    exit;
}