<?php
/**
 * Yardımcı fonksiyonlar
 * Genel kullanım için yardımcı fonksiyonlar
 */

/**
 * Oturum başlat (eğer başlamamışsa)
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Kullanıcı giriş yapmış mı kontrol et
 */
function isLoggedIn(): bool {
    startSession();
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

/**
 * Kullanıcı rolünü kontrol et
 */
function hasRole(string $role): bool {
    startSession();
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === $role;
}

/**
 * Admin mi kontrol et
 */
function isAdmin(): bool {
    return hasRole('yonetici');
}

/**
 * Giriş yapılmamışsa login sayfasına yönlendir
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /auth/login.php');
        exit;
    }
}

/**
 * Admin yetkisi gerekiyorsa kontrol et
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: /dashboard/index.php');
        exit;
    }
}

/**
 * XSS koruması için string temizle
 */
function clean(string $data): string {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * JSON response döndür
 */
function jsonResponse($data, int $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Hata response döndür
 */
function errorResponse(string $message, int $statusCode = 400) {
    jsonResponse(['success' => false, 'error' => $message], $statusCode);
}

/**
 * Başarı response döndür
 */
function successResponse($data = [], string $message = 'İşlem başarılı') {
    jsonResponse(['success' => true, 'message' => $message, 'data' => $data]);
}

/**
 * Tarihi formatla (Türkçe)
 */
function formatDate($date, string $format = 'd.m.Y'): string {
    if (empty($date)) {
        return '-';
    }

    if (is_string($date)) {
        $date = new DateTime($date);
    }

    return $date->format($format);
}

/**
 * Para formatla (TL)
 */
function formatMoney(float $amount): string {
    return number_format($amount, 2, ',', '.') . ' ₺';
}

/**
 * Sayı formatla
 */
function formatNumber($number, int $decimals = 0): string {
    return number_format($number, $decimals, ',', '.');
}

/**
 * Email gönder
 */
function sendEmail(string $to, string $subject, string $message): bool {
    // PHPMailer veya benzeri bir kütüphane kullanılabilir
    // Şimdilik basit mail() fonksiyonu
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= 'From: ' . ($_ENV['MAIL_FROM_EMAIL'] ?? 'noreply@localhost') . "\r\n";

    return mail($to, $subject, $message, $headers);
}

/**
 * Kullanıcı onay emaili gönder
 */
function sendApprovalEmail(string $email, string $name): bool {
    $subject = 'Hesabınız Onaylandı - Doğu AŞ';
    $message = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Hesap Onayı</title>
    </head>
    <body>
        <h2>Merhaba {$name},</h2>
        <p>Doğu AŞ Stok ve Süreç Takip Sistemi hesabınız yönetici tarafından onaylanmıştır.</p>
        <p>Artık sisteme giriş yapabilirsiniz.</p>
        <p><a href='" . ($_ENV['APP_URL'] ?? 'http://localhost') . "/auth/login.php'>Giriş Yap</a></p>
        <br>
        <p>İyi çalışmalar dileriz.</p>
        <p><strong>Doğu AŞ</strong></p>
    </body>
    </html>
    ";

    return sendEmail($email, $subject, $message);
}

/**
 * Rastgele token üret
 */
function generateToken(int $length = 32): string {
    return bin2hex(random_bytes($length));
}

/**
 * Şifre hash'le
 */
function hashPassword(string $password): string {
    return password_hash($password, PASSWORD_BCRYPT);
}

/**
 * Şifre doğrula
 */
function verifyPassword(string $password, string $hash): bool {
    return password_verify($password, $hash);
}

/**
 * Müşteri inaktiflik durumunu hesapla
 */
function getCustomerInactivityStatus(string $lastTransactionDate): array {
    $lastDate = new DateTime($lastTransactionDate);
    $now = new DateTime();
    $diff = $now->diff($lastDate);
    $days = $diff->days;

    $status = [
        'days' => $days,
        'color' => 'green',
        'alert' => false,
        'level' => 0
    ];

    if ($days >= 150) {
        $status['color'] = 'red-900';
        $status['alert'] = true;
        $status['level'] = 5;
    } elseif ($days >= 120) {
        $status['color'] = 'red-700';
        $status['alert'] = true;
        $status['level'] = 4;
    } elseif ($days >= 90) {
        $status['color'] = 'red-500';
        $status['alert'] = true;
        $status['level'] = 3;
    } elseif ($days >= 60) {
        $status['color'] = 'orange-500';
        $status['alert'] = true;
        $status['level'] = 2;
    } elseif ($days >= 45) {
        $status['color'] = 'yellow-500';
        $status['alert'] = true;
        $status['level'] = 1;
    } elseif ($days >= 30) {
        $status['color'] = 'yellow-300';
        $status['alert'] = true;
        $status['level'] = 1;
    }

    return $status;
}

/**
 * CSV export yap
 */
function exportCSV(array $data, string $filename = 'export.csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    $output = fopen('php://output', 'w');

    // UTF-8 BOM ekle (Excel için)
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Header'ları yaz
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]), ';');

        // Verileri yaz
        foreach ($data as $row) {
            fputcsv($output, $row, ';');
        }
    }

    fclose($output);
    exit;
}

/**
 * Stok durumu rengini hesapla
 */
function getStockStatusColor(int $currentStock, int $referenceValue): string {
    if ($currentStock <= 0) {
        return 'red-600';
    } elseif ($currentStock < $referenceValue) {
        return 'orange-500';
    } elseif ($currentStock < $referenceValue * 1.5) {
        return 'yellow-500';
    } else {
        return 'green-600';
    }
}

/**
 * Weighted average forecasting (basit AI tahmini)
 */
function forecastDemand(array $salesData, int $periods = 3): float {
    $count = count($salesData);
    if ($count === 0) {
        return 0;
    }

    // Son $periods kadar veriyi al
    $recentData = array_slice($salesData, -$periods);

    // Ağırlıklı ortalama hesapla (son veriler daha ağırlıklı)
    $totalWeight = 0;
    $weightedSum = 0;

    foreach ($recentData as $index => $value) {
        $weight = $index + 1; // Son veriler daha yüksek ağırlık
        $weightedSum += $value * $weight;
        $totalWeight += $weight;
    }

    return $totalWeight > 0 ? $weightedSum / $totalWeight : 0;
}

/**
 * Sayfa başlığı oluştur
 */
function getPageTitle(string $page): string {
    $titles = [
        'dashboard' => 'Anasayfa',
        'stock' => 'Stok Durumu',
        'active-tasks' => 'Aktif İşler',
        'history' => 'Geçmiş İşler',
        'user-approvals' => 'Kullanıcı Onayları',
        'logs' => 'Etkinlikler (Loglar)',
        'login' => 'Giriş Yap',
        'register' => 'Kayıt Ol'
    ];

    return $titles[$page] ?? 'Doğu AŞ';
}
