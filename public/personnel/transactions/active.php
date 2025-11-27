<?php
/**
 * Active Tasks Page - Personnel
 * Aktif işlemler sayfası
 */

require_once __DIR__ . '/../../../src/helpers/helpers.php';
requireLogin();

$pageTitle = 'Aktif İşler';
$pageDescription = 'Devam eden alış ve satış işlemleri';

include __DIR__ . '/../../../views/layouts/header.php';
?>

<?php include __DIR__ . '/../../../views/layouts/personnel_sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen">
    <?php include __DIR__ . '/../../../views/layouts/topbar.php'; ?>

    <div class="p-6 space-y-6">
        <!-- Info Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
            <div class="flex items-center">
                <svg class="w-6 h-6 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="font-bold text-blue-900">Aktif İşlemler</h3>
                    <p class="text-sm text-blue-700">Bu sayfada devam eden alış ve satış işlemlerini görüntüleyebilirsiniz. İşlemleri tamamlamak için durum güncellemeleri yapabilirsiniz.</p>
                </div>
            </div>
        </div>

        <!-- Tabs -->
        <div class="flex space-x-4 border-b border-gray-200">
            <button onclick="switchTab('purchases')" id="tabPurchases" class="px-4 py-2 font-medium text-blue-600 border-b-2 border-blue-600">
                Alış İşlemleri
            </button>
            <button onclick="switchTab('sales')" id="tabSales" class="px-4 py-2 font-medium text-gray-600 hover:text-gray-900">
                Satış İşlemleri
            </button>
        </div>

        <!-- Purchases Tab Content -->
        <div id="purchasesContent" class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Aktif Alış İşlemleri</h3>
            <p class="text-gray-600">Alış işlemleri burada listelenecektir.</p>
            <p class="text-sm text-gray-500 mt-2">Özellik geliştirme aşamasında...</p>
        </div>

        <!-- Sales Tab Content -->
        <div id="salesContent" class="hidden bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Aktif Satış İşlemleri</h3>
            <p class="text-gray-600">Satış işlemleri burada listelenecektir.</p>
            <p class="text-sm text-gray-500 mt-2">Özellik geliştirme aşamasında...</p>
        </div>
    </div>
</div>

<script>
    function switchTab(tab) {
        // Hide all tabs
        document.getElementById('purchasesContent').classList.add('hidden');
        document.getElementById('salesContent').classList.add('hidden');

        // Reset tab buttons
        document.getElementById('tabPurchases').className = 'px-4 py-2 font-medium text-gray-600 hover:text-gray-900';
        document.getElementById('tabSales').className = 'px-4 py-2 font-medium text-gray-600 hover:text-gray-900';

        // Show selected tab
        if (tab === 'purchases') {
            document.getElementById('purchasesContent').classList.remove('hidden');
            document.getElementById('tabPurchases').className = 'px-4 py-2 font-medium text-blue-600 border-b-2 border-blue-600';
        } else {
            document.getElementById('salesContent').classList.remove('hidden');
            document.getElementById('tabSales').className = 'px-4 py-2 font-medium text-blue-600 border-b-2 border-blue-600';
        }
    }
</script>

<?php include __DIR__ . '/../../../views/layouts/footer.php'; ?>
