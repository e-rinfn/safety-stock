<?php
session_start();


function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function checkLogin()
{
    global $base_url;

    if (!isLoggedIn()) {
        // header("Location: {$base_url}/modules/auth/login.php");
        header("Location: /safety-stock/modules/auth/login.php");
        exit();
    }
}

function checkRole($required_role)
{
    global $base_url;

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $required_role) {
        // header("Location: {$base_url}/index.php");
        header("Location: /safety-stock/index.php");
        exit();
    }
}
