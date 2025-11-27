<?php
/**
 * Logs API
 * Log kayıtlarını getir
 */

error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../src/helpers/helpers.php';
requireAdmin();

try {
    $pdo = require __DIR__ . '/../../src/database/db.php';

    // Filtreleme parametreleri
    $table = $_GET['table'] ?? '';
    $operation = $_GET['operation'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';
    $limit = (int)($_GET['limit'] ?? 100);

    // Base query
    $sql = "SELECT log_id, tablo_adi, islem_turu, kayit_id, eski_deger, yeni_deger, islem_zamani
            FROM log_islemler
            WHERE 1=1";
    $params = [];

    // Tablo filtresi
    if ($table) {
        $sql .= " AND tablo_adi = :table";
        $params['table'] = $table;
    }

    // İşlem turu filtresi
    if ($operation) {
        $sql .= " AND islem_turu = :operation";
        $params['operation'] = $operation;
    }

    // Tarih filtreleri
    if ($dateFrom) {
        $sql .= " AND islem_zamani >= :date_from";
        $params['date_from'] = $dateFrom . ' 00:00:00';
    }

    if ($dateTo) {
        $sql .= " AND islem_zamani <= :date_to";
        $params['date_to'] = $dateTo . ' 23:59:59';
    }

    $sql .= " ORDER BY islem_zamani DESC LIMIT :limit";
    $params['limit'] = $limit;

    $stmt = $pdo->prepare($sql);

    // Bind parameters
    foreach ($params as $key => $value) {
        if ($key === 'limit') {
            $stmt->bindValue(':' . $key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindValue(':' . $key, $value);
        }
    }

    $stmt->execute();
    $logs = $stmt->fetchAll();

    successResponse(['logs' => $logs, 'total' => count($logs)]);

} catch (PDOException $e) {
    error_log('Logs error: ' . $e->getMessage());
    errorResponse('Loglar yüklenirken bir hata oluştu', 500);
}
