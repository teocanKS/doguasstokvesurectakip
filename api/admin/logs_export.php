<?php
/**
 * Logs CSV Export
 * Logları CSV olarak dışa aktar
 */

error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../src/helpers/helpers.php';
requireAdmin();

try {
    $pdo = require __DIR__ . '/../../src/database/db.php';

    // Filtreleme parametreleri (logs.php ile aynı)
    $table = $_GET['table'] ?? '';
    $operation = $_GET['operation'] ?? '';
    $dateFrom = $_GET['date_from'] ?? '';
    $dateTo = $_GET['date_to'] ?? '';

    $sql = "SELECT log_id, tablo_adi, islem_turu, kayit_id, eski_deger, yeni_deger, islem_zamani
            FROM log_islemler
            WHERE 1=1";
    $params = [];

    if ($table) {
        $sql .= " AND tablo_adi = :table";
        $params['table'] = $table;
    }

    if ($operation) {
        $sql .= " AND islem_turu = :operation";
        $params['operation'] = $operation;
    }

    if ($dateFrom) {
        $sql .= " AND islem_zamani >= :date_from";
        $params['date_from'] = $dateFrom . ' 00:00:00';
    }

    if ($dateTo) {
        $sql .= " AND islem_zamani <= :date_to";
        $params['date_to'] = $dateTo . ' 23:59:59';
    }

    $sql .= " ORDER BY islem_zamani DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $logs = $stmt->fetchAll();

    // CSV formatına dönüştür
    $csvData = [];
    foreach ($logs as $log) {
        $csvData[] = [
            'Log ID' => $log['log_id'],
            'Tablo' => $log['tablo_adi'],
            'İşlem' => $log['islem_turu'],
            'Kayıt ID' => $log['kayit_id'],
            'Eski Değer' => substr($log['eski_deger'], 0, 100),
            'Yeni Değer' => substr($log['yeni_deger'], 0, 100),
            'Tarih' => $log['islem_zamani']
        ];
    }

    exportCSV($csvData, 'logs_' . date('Y-m-d_H-i-s') . '.csv');

} catch (PDOException $e) {
    error_log('Logs export error: ' . $e->getMessage());
    die('CSV oluşturulurken hata oluştu');
}
