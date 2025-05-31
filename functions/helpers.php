<?php

/**
 * Fungsi-fungsi bantuan umum untuk sistem inventory
 */

/**
 * Membersihkan input dari user
 * @param string $data - Data yang akan dibersihkan
 * @return string - Data yang sudah dibersihkan
 */
function sanitizeInput($data)
{
    global $conn;
    if (is_array($data)) {
        return array_map('sanitizeInput', $data);
    }
    return htmlspecialchars(strip_tags($conn->real_escape_string(trim($data))));
}

/**
 * Redirect ke halaman tertentu
 * @param string $location - Lokasi tujuan redirect
 */
function redirect($location)
{
    header("Location: $location");
    exit();
}

/**
 * Format tanggal untuk tampilan
 * @param string $date - Tanggal dalam format database
 * @return string - Tanggal yang sudah diformat
 */
function formatDate($date, $format = 'd M Y')
{
    return date($format, strtotime($date));
}

/**
 * Menampilkan pesan flash
 * @param string $message - Pesan yang akan ditampilkan
 * @param string $type - Jenis pesan (success, error, warning, info)
 */
function flashMessage($message, $type = 'success')
{
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Menampilkan pesan flash jika ada
 */
function showFlashMessage()
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';

        echo "<div class='flash-message $type'>$message</div>";

        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
    }
}

/**
 * Mengecek apakah request adalah AJAX
 * @return bool
 */
function isAjaxRequest()
{
    return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
}

/**
 * Generate random string
 * @param int $length - Panjang string yang diinginkan
 * @return string - Random string
 */
function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';

    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }

    return $randomString;
}
