<?php
require_once __DIR__ . '../../../config/database.php';
require_once __DIR__ . '../../../config/auth.php';
checkLogin();

$id = $_GET['id'];
$conn->query("DELETE FROM suppliers WHERE supplier_id = $id");

header("Location: list.php");
exit;
