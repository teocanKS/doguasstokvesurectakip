<?php
/**
 * Login API endpoint
 * POST /api/auth/login.php
 */

// Hataları gizle (production)
error_reporting(0);
ini_set('display_errors', 0);

// Helper fonksiyonlarını yükle
require_once __DIR__ . '/../../src/helpers/helpers.php';

// Veritabanı bağlantısı
try {
    $pdo = require __DIR__ . '/../../src/database/db.php';
} catch (Exception $e) {
    errorResponse('Veritabanı bağlantısı kurulamadı', 500);
}

// Sadece POST isteklerine izin ver
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errorResponse('Geçersiz istek metodu', 405);
}

// JSON input al
$input = json_decode(file_get_contents('php://input'), true);

// Email ve password kontrolü
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    errorResponse('Email ve şifre gereklidir');
}

// Email formatı kontrolü
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    errorResponse('Geçersiz email formatı');
}

try {
    // Kullanıcıyı veritabanından getir
    $stmt = $pdo->prepare("
        SELECT users_id, name, surname, email, password, role, is_approved
        FROM users
        WHERE email = :email
    ");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    // Kullanıcı bulunamadı
    if (!$user) {
        errorResponse('Email veya şifre hatalı');
    }

    // Şifre kontrolü
    if (!verifyPassword($password, $user['password'])) {
        errorResponse('Email veya şifre hatalı');
    }

    // Onay kontrolü
    if (!$user['is_approved']) {
        errorResponse('Hesabınız henüz yönetici tarafından onaylanmamış. Lütfen onay bekleyiniz.');
    }

    // Session başlat
    startSession();
    $_SESSION['user_id'] = $user['users_id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_surname'] = $user['surname'] ?? '';
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['login_time'] = time();

    // Başarılı giriş
    successResponse([
        'user' => [
            'id' => $user['users_id'],
            'name' => $user['name'],
            'surname' => $user['surname'],
            'email' => $user['email'],
            'role' => $user['role']
        ],
        'redirect' => $user['role'] === 'yonetici' ? '/admin/dashboard/index.php' : '/personnel/dashboard/index.php'
    ], 'Giriş başarılı');

} catch (PDOException $e) {
    error_log('Login error: ' . $e->getMessage());
    errorResponse('Giriş yapılırken bir hata oluştu', 500);
}
