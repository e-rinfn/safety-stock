<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/auth.php';
require_once __DIR__ . '/../../functions/helpers.php';

checkLogin();

if (!isset($_GET['id'])) {
    header("Location: list.php?error_msg=ID tidak ditemukan.");
    exit();
}

$unit_id = (int) sanitizeInput($_GET['id']);

$sql = "DELETE FROM units WHERE unit_id = $unit_id";

if ($conn->query($sql)) {
    header("Location: list.php?success=1");
} else {
    header("Location: list.php?error=1");
}
exit();
