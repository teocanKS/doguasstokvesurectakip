<?php
/**
 * Transaction History Page - Personnel
 * Geçmiş işlemler sayfası
 */

require_once __DIR__ . '/../../../src/helpers/helpers.php';
requireLogin();

$pageTitle = 'Geçmiş İşler';
$pageDescription = 'Tamamlanan işlemleri görüntüleyin';

include __DIR__ . '/../../../views/layouts/header.php';
?>

<?php include __DIR__ . '/../../../views/layouts/personnel_sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen">
    <?php include __DIR__ . '/../../../views/layouts/topbar.php'; ?>

    <div class="p-6 space-y-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Geçmiş İşlemler</h3>
            <p class="text-gray-600">Tamamlanmış tüm alış ve satış işlemlerini burada görüntüleyebilirsiniz.</p>
            <p class="text-sm text-gray-500 mt-2">Özellik geliştirme aşamasında...</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../views/layouts/footer.php'; ?>
