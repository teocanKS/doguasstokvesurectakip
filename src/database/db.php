<?php
/**
 * Veritabanı bağlantı dosyası
 * PostgreSQL PDO bağlantısı
 */

// .env dosyasını yükle
function loadEnv($path) {
    if (!file_exists($path)) {
        throw new Exception('.env dosyası bulunamadı: ' . $path);
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        // Yorum satırlarını atla
        if (strpos(trim($line), '#') === 0) {
            continue;
        }

        // KEY=VALUE formatını parse et
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Zaten tanımlı değilse environment variable olarak kaydet
            if (!array_key_exists($key, $_ENV)) {
                $_ENV[$key] = $value;
                putenv("$key=$value");
            }
        }
    }
}

// .env dosyasını yükle
$envPath = __DIR__ . '/../../.env';
loadEnv($envPath);

// Veritabanı bağlantı ayarları
$dbConfig = [
    'host' => $_ENV['DB_HOST'] ?? 'localhost',
    'port' => $_ENV['DB_PORT'] ?? '5432',
    'dbname' => $_ENV['DB_NAME'] ?? 'dogu_as_db',
    'user' => $_ENV['DB_USER'] ?? 'teocan',
    'password' => $_ENV['DB_PASSWORD'] ?? ''
];

try {
    // PDO DSN oluştur
    $dsn = sprintf(
        'pgsql:host=%s;port=%s;dbname=%s',
        $dbConfig['host'],
        $dbConfig['port'],
        $dbConfig['dbname']
    );

    // PDO bağlantısı oluştur
    $pdo = new PDO(
        $dsn,
        $dbConfig['user'],
        $dbConfig['password'],
        [
            // Hata modunu exception olarak ayarla
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,

            // Varsayılan fetch modunu associative array olarak ayarla
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // Emulated prepares'i kapat (güvenlik için)
            PDO::ATTR_EMULATE_PREPARES => false,

            // Persistent connection kullanma (Raspberry Pi için hafif tut)
            PDO::ATTR_PERSISTENT => false,

            // UTF-8 encoding
            PDO::ATTR_STRINGIFY_FETCHES => false
        ]
    );

    // PostgreSQL client encoding'i ayarla
    $pdo->exec("SET NAMES 'UTF8'");

} catch (PDOException $e) {
    // Hata durumunda güvenli mesaj göster
    error_log('Veritabanı bağlantı hatası: ' . $e->getMessage());

    // Production'da detaylı hata mesajı gösterme
    if (($_ENV['APP_DEBUG'] ?? 'false') === 'true') {
        die('Veritabanı bağlantı hatası: ' . $e->getMessage());
    } else {
        die('Veritabanı bağlantısı kurulamadı. Lütfen sistem yöneticisine başvurun.');
    }
}

// Veritabanı bağlantısını döndür
return $pdo;
