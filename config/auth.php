<?php
session_start();

function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

function checkLogin()
{
    if (!isLoggedIn()) {
        header("Location: /safety-stock/modules/auth/login.php");
        exit();
    }
}

function checkRole($required_role)
{
    if ($_SESSION['role'] !== $required_role) {
        header("Location: /safety-stock/index.php");
        exit();
    }
}
