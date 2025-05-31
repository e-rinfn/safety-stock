<?php
require_once '../../../config/database.php';
require_once '../../../config/auth.php';
require_once '../../../functions/helpers.php';

checkLogin();

if (!isset($_GET['id'])) {
    header("Location: list.php");
    exit();
}

$product_id = sanitizeInput($_GET['id']);

// Soft delete (set is_active to 0)
$sql = "UPDATE products SET is_active = 0 WHERE product_id = $product_id";
if ($conn->query($sql)) {
    header("Location: list.php?success=1");
} else {
    header("Location: list.php?error=1");
}
exit();
