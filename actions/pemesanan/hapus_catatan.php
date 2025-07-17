<?php
// File: actions/pemesanan/hapus_catatan.php
require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Pelanggan');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { exit('Akses tidak sah'); }

$id_pemesanan = (int)$_POST['id_pemesanan'];

try {
    $stmt = $pdo->prepare("UPDATE pemesanan SET catatan_admin = NULL WHERE id_pemesanan = ? AND id_pengguna = ?");
    $stmt->execute([$id_pemesanan, $_SESSION['id_pengguna']]);
    header('Location: ' . BASE_URL . "actions/pemesanan/detail.php?id=$id_pemesanan");
    exit;
} catch (PDOException $e) {
    // Abaikan jika error
    header('Location: ' . BASE_URL . "actions/pemesanan/detail.php?id=$id_pemesanan");
    exit;
}
?>