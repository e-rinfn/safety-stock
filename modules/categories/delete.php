<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
require_once __DIR__ . '../../../functions/helpers.php';

checkLogin();

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$category_id = sanitizeInput($_GET['id']);

// Soft delete
$sql = "UPDATE categories SET is_active = 0 WHERE category_id = $category_id";
if ($conn->query($sql)) {
    header("Location: list.php?success=1");
} else {
    header("Location: list.php?error=1");
}
exit();
