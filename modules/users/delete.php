<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();
// checkRole('admin');

if (!isset($_GET['id'])) {
    redirect('list.php');
}

$user_id = (int)$_GET['id'];

// Tidak boleh menghapus diri sendiri
if ($user_id == $_SESSION['user_id']) {
    $_SESSION['error'] = 'Anda tidak dapat menghapus akun sendiri';
    redirect('list.php');
}

// Hapus user
$delete_sql = "DELETE FROM users WHERE user_id = $user_id";
if ($conn->query($delete_sql)) {
    $_SESSION['success'] = 'Pengguna berhasil dihapus';
} else {
    $_SESSION['error'] = 'Gagal menghapus pengguna: ' . $conn->error;
}

redirect('list.php');
