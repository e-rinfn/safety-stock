<?php
require_once '../../config/auth.php';
include_once '../../config/config.php';

session_destroy();

// Gunakan interpolasi variabel PHP secara benar
header("Location: {$base_url}/modules/auth/login.php");
exit();
