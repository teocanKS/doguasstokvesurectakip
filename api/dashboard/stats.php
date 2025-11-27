<?php
/**
 * Dashboard Statistics API
 * GET /api/dashboard/stats.php
 */

error_reporting(0);
ini_set('display_errors', 0);

require_once __DIR__ . '/../../src/helpers/helpers.php';

// Giriş kontrolü
requireLogin();

try {
    $pdo = require __DIR__ . '/../../src/database/db.php';

    // Toplam ürün sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM urun");
    $totalProducts = $stmt->fetch()['total'];

    // Toplam stok değeri (toplam stok miktarı)
    $stmt = $pdo->query("SELECT COALESCE(SUM(toplam_stok), 0) as total FROM urun_stok");
    $totalStock = $stmt->fetch()['total'];

    // Toplam tedarikçi sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tedarikci");
    $totalSuppliers = $stmt->fetch()['total'];

    // Toplam müşteri sayısı
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM musteri");
    $totalCustomers = $stmt->fetch()['total'];

    // En çok satılan ürünler (top 5)
    $stmt = $pdo->query("
        SELECT
            u.urun_adi,
            SUM(umi.satilan_adet) as total_sold
        FROM urun_musteri_islem umi
        JOIN urun u ON umi.urun_id = u.urun_id
        GROUP BY u.urun_id, u.urun_adi
        ORDER BY total_sold DESC
        LIMIT 5
    ");
    $topProducts = $stmt->fetchAll();

    // En az satılan ürünler (top 5)
    $stmt = $pdo->query("
        SELECT
            u.urun_adi,
            COALESCE(SUM(umi.satilan_adet), 0) as total_sold
        FROM urun u
        LEFT JOIN urun_musteri_islem umi ON u.urun_id = umi.urun_id
        GROUP BY u.urun_id, u.urun_adi
        ORDER BY total_sold ASC
        LIMIT 5
    ");
    $leastProducts = $stmt->fetchAll();

    // En aktif tedarikçiler (top 5)
    $stmt = $pdo->query("
        SELECT
            t.tedarikci_adi,
            COUNT(uta.alis_id) as transaction_count,
            SUM(uta.toplam_alis_tutari) as total_amount
        FROM tedarikci t
        LEFT JOIN urun_tedarikci_alis uta ON t.tedarikci_id = uta.tedarikci_id
        GROUP BY t.tedarikci_id, t.tedarikci_adi
        ORDER BY transaction_count DESC
        LIMIT 5
    ");
    $topSuppliers = $stmt->fetchAll();

    // En aktif müşteriler (top 5)
    $stmt = $pdo->query("
        SELECT
            m.musteri_adi,
            COUNT(umi.islem_id) as transaction_count,
            SUM(umi.toplam_satis_tutari) as total_amount
        FROM musteri m
        LEFT JOIN urun_musteri_islem umi ON m.musteri_id = umi.musteri_id
        GROUP BY m.musteri_id, m.musteri_adi
        ORDER BY transaction_count DESC
        LIMIT 5
    ");
    $topCustomers = $stmt->fetchAll();

    // Kar analizi
    $stmt = $pdo->query("
        SELECT
            COALESCE(SUM(toplam_alis_tutari), 0) as total_cost
        FROM urun_tedarikci_alis
    ");
    $totalCost = $stmt->fetch()['total_cost'];

    $stmt = $pdo->query("
        SELECT
            COALESCE(SUM(toplam_satis_tutari), 0) as total_revenue
        FROM urun_musteri_islem
    ");
    $totalRevenue = $stmt->fetch()['total_revenue'];

    $profit = $totalRevenue - $totalCost;

    // Aktif işler sayısı
    $stmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM urun_musteri_islem
        WHERE islemin_durumu = 'DEVAM EDIYOR'
    ");
    $activeSales = $stmt->fetch()['total'];

    $stmt = $pdo->query("
        SELECT COUNT(*) as total
        FROM urun_tedarikci_alis
        WHERE alis_durumu = false OR alis_durumu IS NULL
    ");
    $activePurchases = $stmt->fetch()['total'];

    // Son 30 günün satış trendi (basit forecast için)
    $stmt = $pdo->query("
        SELECT
            DATE(islem_tarihi) as date,
            SUM(satilan_adet) as total_sold
        FROM urun_musteri_islem
        WHERE islem_tarihi >= CURRENT_DATE - INTERVAL '30 days'
        GROUP BY DATE(islem_tarihi)
        ORDER BY date DESC
    ");
    $salesTrend = $stmt->fetchAll();

    successResponse([
        'overview' => [
            'total_products' => (int)$totalProducts,
            'total_stock' => (int)$totalStock,
            'total_suppliers' => (int)$totalSuppliers,
            'total_customers' => (int)$totalCustomers,
            'active_sales' => (int)$activeSales,
            'active_purchases' => (int)$activePurchases
        ],
        'top_products' => $topProducts,
        'least_products' => $leastProducts,
        'top_suppliers' => $topSuppliers,
        'top_customers' => $topCustomers,
        'profit_analysis' => [
            'total_cost' => (float)$totalCost,
            'total_revenue' => (float)$totalRevenue,
            'profit' => (float)$profit,
            'profit_margin' => $totalRevenue > 0 ? ($profit / $totalRevenue) * 100 : 0
        ],
        'sales_trend' => $salesTrend
    ]);

} catch (PDOException $e) {
    error_log('Dashboard stats error: ' . $e->getMessage());
    errorResponse('İstatistikler yüklenirken bir hata oluştu', 500);
}
