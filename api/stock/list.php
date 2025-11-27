<?php
/**
 * Stock List API
 * Stok listesini getir
 */

error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../src/helpers/helpers.php';
requireLogin();

try {
    $pdo = require __DIR__ . '/../../src/database/db.php';

    // Arama parametresi
    $search = $_GET['search'] ?? '';

    $sql = "
        SELECT
            u.urun_id,
            u.urun_adi,
            u.birim,
            u.kategori,
            COALESCE(s.toplam_stok, 0) as toplam_stok,
            COALESCE(s.referans_degeri, 0) as referans_degeri
        FROM urun u
        LEFT JOIN urun_stok s ON u.urun_id = s.urun_id
        WHERE 1=1
    ";

    $params = [];

    if ($search) {
        $sql .= " AND (u.urun_adi ILIKE :search OR u.kategori ILIKE :search)";
        $params['search'] = '%' . $search . '%';
    }

    $sql .= " ORDER BY u.urun_adi";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $products = $stmt->fetchAll();

    // Kategori bazlı stok özeti
    $stmt = $pdo->query("
        SELECT
            u.kategori,
            COUNT(u.urun_id) as product_count,
            SUM(COALESCE(s.toplam_stok, 0)) as total_stock
        FROM urun u
        LEFT JOIN urun_stok s ON u.urun_id = s.urun_id
        GROUP BY u.kategori
        ORDER BY u.kategori
    ");
    $categoryStats = $stmt->fetchAll();

    successResponse([
        'products' => $products,
        'category_stats' => $categoryStats,
        'total_products' => count($products)
    ]);

} catch (PDOException $e) {
    error_log('Stock list error: ' . $e->getMessage());
    errorResponse('Stok listesi yüklenirken hata oluştu', 500);
}
