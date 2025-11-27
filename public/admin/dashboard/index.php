<?php
/**
 * Admin Dashboard
 * Yönetici ana sayfa
 */

require_once __DIR__ . '/../../../src/helpers/helpers.php';
requireAdmin(); // Sadece admin erişebilir

$pageTitle = 'Yönetici Paneli';
$pageDescription = 'Hoş geldiniz, ' . clean($_SESSION['user_name']);

include __DIR__ . '/../../../views/layouts/header.php';
?>

<?php include __DIR__ . '/../../../views/layouts/admin_sidebar.php'; ?>

<!-- Main Content -->
<div class="lg:ml-64 min-h-screen">
    <?php include __DIR__ . '/../../../views/layouts/topbar.php'; ?>

    <div class="p-6 space-y-6">
        <!-- KPI Cards -->
        <div id="kpiCards" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Loading skeletons -->
            <div class="bg-white rounded-lg shadow-md p-6 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
                <div class="h-8 bg-gray-200 rounded w-3/4"></div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
                <div class="h-8 bg-gray-200 rounded w-3/4"></div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
                <div class="h-8 bg-gray-200 rounded w-3/4"></div>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6 animate-pulse">
                <div class="h-4 bg-gray-200 rounded w-1/2 mb-4"></div>
                <div class="h-8 bg-gray-200 rounded w-3/4"></div>
            </div>
        </div>

        <!-- Charts (Same as personnel but with admin theme) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">En Çok Satılan Ürünler</h3>
                <canvas id="topProductsChart"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">En Az Satılan Ürünler</h3>
                <canvas id="leastProductsChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">En Aktif Tedarikçiler</h3>
                <canvas id="topSuppliersChart"></canvas>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">En Aktif Müşteriler</h3>
                <canvas id="topCustomersChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-2 bg-white rounded-lg shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Kar Analizi</h3>
                <canvas id="profitChart"></canvas>
            </div>

            <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-lg shadow-md p-6 text-white">
                <div class="flex items-center mb-4">
                    <svg class="w-8 h-8 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                    <h3 class="text-lg font-bold">AI Tahmin</h3>
                </div>

                <div id="forecastCard">
                    <p class="text-orange-100 text-sm mb-4">Satış trend analizi ve gelecek tahmini</p>
                    <div class="bg-white/20 rounded-lg p-4 backdrop-blur-sm">
                        <div class="text-3xl font-bold mb-2" id="forecastValue">-</div>
                        <div class="text-sm text-orange-100">Tahmini günlük satış (adet)</div>
                    </div>

                    <div class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-orange-100">Trend:</span>
                            <span class="font-semibold" id="trendDirection">-</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-orange-100">Güven:</span>
                            <span class="font-semibold" id="confidenceLevel">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Same dashboard logic as personnel (reuse from personnel dashboard)
    async function loadDashboard() {
        try {
            const response = await fetch('/api/dashboard/stats.php');
            const data = await response.json();

            if (data.success) {
                renderKPIs(data.data.overview);
                renderTopProductsChart(data.data.top_products);
                renderLeastProductsChart(data.data.least_products);
                renderTopSuppliersChart(data.data.top_suppliers);
                renderTopCustomersChart(data.data.top_customers);
                renderProfitChart(data.data.profit_analysis);
                renderForecast(data.data.sales_trend);
            }
        } catch (error) {
            console.error('Dashboard loading error:', error);
        }
    }

    function renderKPIs(overview) {
        document.getElementById('kpiCards').innerHTML = `
            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-orange-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Toplam Ürün</p>
                        <p class="text-3xl font-bold text-gray-900">${overview.total_products}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Toplam Stok</p>
                        <p class="text-3xl font-bold text-gray-900">${overview.total_stock.toLocaleString('tr-TR')}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Aktif İşler</p>
                        <p class="text-3xl font-bold text-gray-900">${overview.active_sales + overview.active_purchases}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Müşteriler</p>
                        <p class="text-3xl font-bold text-gray-900">${overview.total_customers}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        `;
    }

    function renderTopProductsChart(products) {
        const ctx = document.getElementById('topProductsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: products.map(p => p.urun_adi),
                datasets: [{
                    label: 'Satış Adedi',
                    data: products.map(p => p.total_sold),
                    backgroundColor: 'rgba(34, 197, 94, 0.8)',
                    borderColor: 'rgba(34, 197, 94, 1)',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true } } }
        });
    }

    function renderLeastProductsChart(products) {
        const ctx = document.getElementById('leastProductsChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: products.map(p => p.urun_adi),
                datasets: [{
                    label: 'Satış Adedi',
                    data: products.map(p => p.total_sold),
                    backgroundColor: 'rgba(239, 68, 68, 0.8)',
                    borderColor: 'rgba(239, 68, 68, 1)',
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true } } }
        });
    }

    function renderTopSuppliersChart(suppliers) {
        const ctx = document.getElementById('topSuppliersChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: suppliers.map(s => s.tedarikci_adi),
                datasets: [{
                    data: suppliers.map(s => s.transaction_count),
                    backgroundColor: ['rgba(59, 130, 246, 0.8)', 'rgba(168, 85, 247, 0.8)', 'rgba(34, 197, 94, 0.8)', 'rgba(249, 115, 22, 0.8)', 'rgba(236, 72, 153, 0.8)']
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });
    }

    function renderTopCustomersChart(customers) {
        const ctx = document.getElementById('topCustomersChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: customers.map(c => c.musteri_adi),
                datasets: [{
                    data: customers.map(c => c.transaction_count),
                    backgroundColor: ['rgba(59, 130, 246, 0.8)', 'rgba(168, 85, 247, 0.8)', 'rgba(34, 197, 94, 0.8)', 'rgba(249, 115, 22, 0.8)', 'rgba(236, 72, 153, 0.8)']
                }]
            },
            options: { responsive: true, maintainAspectRatio: true }
        });
    }

    function renderProfitChart(profitData) {
        const ctx = document.getElementById('profitChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Alış Maliyeti', 'Satış Geliri', 'Net Kar'],
                datasets: [{
                    label: 'Tutar (₺)',
                    data: [profitData.total_cost, profitData.total_revenue, profitData.profit],
                    backgroundColor: ['rgba(239, 68, 68, 0.8)', 'rgba(59, 130, 246, 0.8)', 'rgba(34, 197, 94, 0.8)']
                }]
            },
            options: { responsive: true, maintainAspectRatio: true, scales: { y: { beginAtZero: true } } }
        });
    }

    function renderForecast(salesTrend) {
        if (salesTrend.length === 0) {
            document.getElementById('forecastValue').textContent = 'Veri yok';
            return;
        }

        const values = salesTrend.map(s => parseInt(s.total_sold));
        let totalWeight = 0, weightedSum = 0;

        values.forEach((value, index) => {
            const weight = index + 1;
            weightedSum += value * weight;
            totalWeight += weight;
        });

        const forecast = totalWeight > 0 ? Math.round(weightedSum / totalWeight) : 0;
        const recentAvg = values.slice(0, 7).reduce((a, b) => a + b, 0) / Math.min(7, values.length);
        const olderAvg = values.slice(7, 14).reduce((a, b) => a + b, 0) / Math.max(1, values.slice(7, 14).length);
        const trend = recentAvg > olderAvg ? 'Yükseliş' : recentAvg < olderAvg ? 'Düşüş' : 'Sabit';

        document.getElementById('forecastValue').textContent = forecast.toLocaleString('tr-TR');
        document.getElementById('trendDirection').textContent = trend;
        document.getElementById('confidenceLevel').textContent = 'Orta';
    }

    loadDashboard();
</script>

<?php include __DIR__ . '/../../../views/layouts/footer.php'; ?>
