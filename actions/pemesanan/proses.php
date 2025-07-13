<?php
// File: actions/pemesanan/proses.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

// Pastikan hanya pelanggan yang bisa mengakses
check_auth('Pelanggan');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $id_mobil = (int)$_POST['id_mobil'];
    $id_pengguna = (int)$_POST['id_pengguna'];
    $tanggal_mulai = $_POST['tanggal_mulai'];
    $tanggal_selesai = $_POST['tanggal_selesai'];
    $harga_sewa_harian = (float)$_POST['harga_sewa_harian'];

    // Lakukan validasi...
    if ($tanggal_selesai < $tanggal_mulai) {
        redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Tanggal selesai tidak boleh sebelum tanggal mulai.', 'error');
    }

    // Hitung total biaya
    $durasi = hitung_durasi_sewa($tanggal_mulai, $tanggal_selesai);
    $total_biaya = $durasi * $harga_sewa_harian;
    
    // Simpan ke database pemesanan
    try {
        $sql = "INSERT INTO pemesanan (id_pengguna, id_mobil, tanggal_mulai, tanggal_selesai, total_biaya, status_pemesanan) 
                VALUES (?, ?, ?, ?, ?, 'Menunggu Pembayaran')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id_pengguna, $id_mobil, $tanggal_mulai, $tanggal_selesai, $total_biaya]);
        
        $id_pemesanan_baru = $pdo->lastInsertId();

        redirect_with_message(BASE_URL . "pelanggan/pembayaran.php?id=$id_pemesanan_baru", 'Pemesanan berhasil dibuat! Silakan lakukan pembayaran.');
    } catch (PDOException $e) {
        redirect_with_message(BASE_URL . "actions/mobil/detail.php?id=$id_mobil", 'Gagal membuat pemesanan: ' . $e->getMessage(), 'error');
    }
} else {
    header("Location: " . BASE_URL . "index.php");
    exit;
}