<?php
/**
 * User Management API
 * Admin kullanıcı yönetimi
 */

error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../src/helpers/helpers.php';
requireAdmin();

try {
    $pdo = require __DIR__ . '/../../src/database/db.php';

    // GET - Onay bekleyen kullanıcıları listele
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $stmt = $pdo->query("
            SELECT users_id, name, surname, email, created_at
            FROM users
            WHERE is_approved = false
            ORDER BY created_at DESC
        ");
        $users = $stmt->fetchAll();

        successResponse(['users' => $users]);
    }

    // POST - Kullanıcıyı onayla veya reddet
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $userId = $input['user_id'] ?? null;
        $action = $input['action'] ?? ''; // 'approve' or 'reject'

        if (!$userId || !in_array($action, ['approve', 'reject'])) {
            errorResponse('Geçersiz istek parametreleri');
        }

        if ($action === 'approve') {
            // Kullanıcıyı onayla
            $stmt = $pdo->prepare("
                UPDATE users
                SET is_approved = true
                WHERE users_id = :user_id
            ");
            $stmt->execute(['user_id' => $userId]);

            // Kullanıcı bilgilerini al (email göndermek için)
            $stmt = $pdo->prepare("SELECT name, email FROM users WHERE users_id = :user_id");
            $stmt->execute(['user_id' => $userId]);
            $user = $stmt->fetch();

            // Email gönder
            if ($user) {
                sendApprovalEmail($user['email'], $user['name']);
            }

            successResponse([], 'Kullanıcı onaylandı ve email gönderildi');

        } else {
            // Kullanıcıyı reddet (sil)
            $stmt = $pdo->prepare("DELETE FROM users WHERE users_id = :user_id");
            $stmt->execute(['user_id' => $userId]);

            successResponse([], 'Kullanıcı reddedildi');
        }
    }

} catch (PDOException $e) {
    error_log('User management error: ' . $e->getMessage());
    errorResponse('İşlem sırasında bir hata oluştu', 500);
}
