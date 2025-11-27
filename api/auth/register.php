<?php
/**
 * Register API endpoint
 * POST /api/auth/register.php
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

// Gerekli alanları al ve kontrol et
$name = trim($input['name'] ?? '');
$surname = trim($input['surname'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$passwordConfirm = $input['password_confirm'] ?? '';

// Validasyon
if (empty($name)) {
    errorResponse('Ad alanı gereklidir');
}

if (empty($email)) {
    errorResponse('Email alanı gereklidir');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    errorResponse('Geçersiz email formatı');
}

if (empty($password)) {
    errorResponse('Şifre alanı gereklidir');
}

if (strlen($password) < 6) {
    errorResponse('Şifre en az 6 karakter olmalıdır');
}

if ($password !== $passwordConfirm) {
    errorResponse('Şifreler eşleşmiyor');
}

try {
    // Email daha önce kullanılmış mı kontrol et
    $stmt = $pdo->prepare("SELECT users_id FROM users WHERE email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->fetch()) {
        errorResponse('Bu email adresi zaten kayıtlı');
    }

    // Şifreyi hash'le
    $hashedPassword = hashPassword($password);

    // Kullanıcıyı kaydet
    $stmt = $pdo->prepare("
        INSERT INTO users (name, surname, email, password, role, is_approved, created_at)
        VALUES (:name, :surname, :email, :password, 'personel', false, NOW())
    ");

    $stmt->execute([
        'name' => $name,
        'surname' => $surname,
        'email' => $email,
        'password' => $hashedPassword
    ]);

    // Başarılı kayıt
    successResponse([], 'Kayıt alındı. Yönetici onayından sonra hesabınız aktif olacaktır.');

} catch (PDOException $e) {
    error_log('Register error: ' . $e->getMessage());
    errorResponse('Kayıt olurken bir hata oluştu', 500);
}
