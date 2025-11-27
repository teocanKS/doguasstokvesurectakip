<?php
/**
 * Logout
 * Oturumu sonlandırır ve login sayfasına yönlendirir
 */

// Helper fonksiyonlarını yükle
require_once __DIR__ . '/../../src/helpers/helpers.php';

// Session başlat
startSession();

// Tüm session verilerini temizle
$_SESSION = array();

// Session cookie'sini sil
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Session'ı yok et
session_destroy();

// Login sayfasına yönlendir
header('Location: /auth/login.php');
exit;
