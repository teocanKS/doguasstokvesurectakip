<?php
/**
 * Stock Status Page - Admin
 * Admin stok durumu sayfası (personnel ile aynı, farklı sidebar)
 */

require_once __DIR__ . '/../../../src/helpers/helpers.php';
requireAdmin();

$pageTitle = 'Stok Durumu';
$pageDescription = 'Ürün stoklarını yönetin';

include __DIR__ . '/../../../views/layouts/header.php';
?>

<?php include __DIR__ . '/../../../views/layouts/admin_sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen">
    <?php include __DIR__ . '/../../../views/layouts/topbar.php'; ?>

    <div class="p-6 space-y-6">
        <div class="bg-white rounded-lg shadow-md p-4">
            <input
                type="text"
                id="searchInput"
                placeholder="Ürün ara..."
                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500"
            >
        </div>

        <div id="categoryStats" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4"></div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Stok Listesi</h3>
            <div id="stockTableContainer">
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let allProducts = [];
    let categoryStats = [];

    async function loadStock(search = '') {
        try {
            const params = new URLSearchParams({ search });
            const response = await fetch('/api/stock/list.php?' + params.toString());
            const data = await response.json();

            if (data.success) {
                allProducts = data.data.products;
                categoryStats = data.data.category_stats;
                renderCategoryStats();
                renderStockTable();
            }
        } catch (error) {
            console.error('Error loading stock:', error);
        }
    }

    function renderCategoryStats() {
        const container = document.getElementById('categoryStats');
        let html = '';

        categoryStats.forEach(cat => {
            html += `
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-md p-4 text-white">
                    <h4 class="text-sm font-medium mb-2">${cat.kategori || 'Diğer'}</h4>
                    <div class="text-2xl font-bold">${cat.total_stock.toLocaleString('tr-TR')}</div>
                    <div class="text-xs text-orange-100">${cat.product_count} ürün</div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function renderStockTable() {
        const container = document.getElementById('stockTableContainer');

        if (allProducts.length === 0) {
            container.innerHTML = '<p class="text-gray-600">Ürün bulunamadı.</p>';
            return;
        }

        let html = '<div class="overflow-x-auto"><table class="w-full">';
        html += '<thead class="bg-slate-800 text-white"><tr>';
        html += '<th class="px-6 py-3 text-left text-xs font-medium uppercase">Ürün Adı</th>';
        html += '<th class="px-6 py-3 text-left text-xs font-medium uppercase">Birim</th>';
        html += '<th class="px-6 py-3 text-left text-xs font-medium uppercase">Kategori</th>';
        html += '<th class="px-6 py-3 text-right text-xs font-medium uppercase">Toplam Stok</th>';
        html += '<th class="px-6 py-3 text-right text-xs font-medium uppercase">Referans</th>';
        html += '<th class="px-6 py-3 text-center text-xs font-medium uppercase">Durum</th>';
        html += '</tr></thead><tbody>';

        allProducts.forEach(product => {
            const stockLevel = product.toplam_stok;
            const reference = product.referans_degeri;

            let statusColor = 'bg-green-100 text-green-800';
            let statusText = 'Normal';

            if (stockLevel <= 0) {
                statusColor = 'bg-red-100 text-red-800';
                statusText = 'Stok Yok';
            } else if (stockLevel < reference) {
                statusColor = 'bg-orange-100 text-orange-800';
                statusText = 'Düşük';
            } else if (stockLevel < reference * 1.5) {
                statusColor = 'bg-yellow-100 text-yellow-800';
                statusText = 'Orta';
            }

            html += '<tr class="border-t border-gray-200 hover:bg-gray-50">';
            html += `<td class="px-6 py-4 text-sm font-medium text-gray-900">${product.urun_adi}</td>`;
            html += `<td class="px-6 py-4 text-sm text-gray-600">${product.birim || '-'}</td>`;
            html += `<td class="px-6 py-4 text-sm text-gray-600">${product.kategori || '-'}</td>`;
            html += `<td class="px-6 py-4 text-sm text-right font-semibold">${stockLevel.toLocaleString('tr-TR')}</td>`;
            html += `<td class="px-6 py-4 text-sm text-right text-gray-600">${reference.toLocaleString('tr-TR')}</td>`;
            html += `<td class="px-6 py-4 text-center"><span class="px-3 py-1 rounded-full text-xs font-semibold ${statusColor}">${statusText}</span></td>`;
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    document.getElementById('searchInput').addEventListener('input', function(e) {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => {
            loadStock(e.target.value);
        }, 300);
    });

    loadStock();
</script>

<?php include __DIR__ . '/../../../views/layouts/footer.php'; ?>
