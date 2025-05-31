<?php
require_once '../../config/auth.php';

session_destroy();
header("Location: /safety-stock/modules/auth/login.php");
exit();
