<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$location_id = (int) sanitizeInput($_GET['id']);

// Pastikan kolom is_active ada di tabel storage_locations
$sql = "DELETE FROM storage_locations WHERE location_id = $location_id";

if ($conn->query($sql)) {
    header("Location: list.php?success=deleted");
} else {
    header("Location: list.php?error=1");
}
exit();
