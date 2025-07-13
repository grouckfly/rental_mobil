<?php
// File: actions/pengguna/hapus.php

require_once '../../includes/config.php';
require_once '../../includes/auth.php';
require_once '../../includes/functions.php';

check_auth('Admin');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_with_message('../../admin/user.php', 'Akses tidak sah.', 'error');
}

$id_pengguna = (int)$_POST['id_pengguna'];
if ($id_pengguna === 0) {
    redirect_with_message('../../admin/user.php', 'ID tidak valid.', 'error');
}
if ($id_pengguna === $_SESSION['id_pengguna']) {
    redirect_with_message('../../admin/user.php', 'Anda tidak bisa menghapus akun Anda sendiri.', 'error');
}

try {
    $stmt = $pdo->prepare("DELETE FROM pengguna WHERE id_pengguna = ?");
    $stmt->execute([$id_pengguna]);
    redirect_with_message('../../admin/user.php', 'Pengguna berhasil dihapus.');
} catch (PDOException $e) {
    redirect_with_message('../../admin/user.php', 'Gagal menghapus pengguna. Mungkin terkait data lain.', 'error');
}