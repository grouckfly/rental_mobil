<?php
// File: logout.php

require_once 'includes/config.php';
require_once 'includes/functions.php';

// Hapus semua data session
session_unset();
session_destroy();

// Redirect ke halaman login dengan pesan sukses
redirect_with_message('login.php', 'Anda telah berhasil logout.');
?>