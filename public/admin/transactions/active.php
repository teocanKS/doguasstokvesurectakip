<?php
/**
 * Active Tasks Page - Admin
 * Admin aktif işlemler sayfası
 */

require_once __DIR__ . '/../../../src/helpers/helpers.php';
requireAdmin();

$pageTitle = 'Aktif İşler';
$pageDescription = 'Devam eden alış ve satış işlemleri';

include __DIR__ . '/../../../views/layouts/header.php';
include __DIR__ . '/../../../views/layouts/admin_sidebar.php';
?>

<div class="lg:ml-64 min-h-screen">
    <?php include __DIR__ . '/../../../views/layouts/topbar.php'; ?>

    <div class="p-6 space-y-6">
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-orange-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <div>
                    <h3 class="font-bold text-orange-900">Aktif İşlemler - Yönetici</h3>
                    <p class="text-sm text-orange-700">Tüm aktif alış ve satış işlemlerini yönetin.</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Aktif İşlemler</h3>
            <p class="text-gray-600">İşlemler burada listelenecektir.</p>
            <p class="text-sm text-gray-500 mt-2">Özellik geliştirme aşamasında...</p>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../../../views/layouts/footer.php'; ?>
