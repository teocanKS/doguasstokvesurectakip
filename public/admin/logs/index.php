<?php
/**
 * Logs Viewer
 * Etkinlik loglarını görüntüleme (Sadece admin)
 */

require_once __DIR__ . '/../../../src/helpers/helpers.php';
requireAdmin();

$pageTitle = 'Etkinlikler (Loglar)';
$pageDescription = 'Sistem etkinlik kayıtları';

include __DIR__ . '/../../../views/layouts/header.php';
?>

<?php include __DIR__ . '/../../../views/layouts/admin_sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen">
    <?php include __DIR__ . '/../../../views/layouts/topbar.php'; ?>

    <div class="p-6">
        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Filtreler</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tablo</label>
                    <select id="filterTable" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">Tümü</option>
                        <option value="musteri">musteri</option>
                        <option value="tedarikci">tedarikci</option>
                        <option value="urun">urun</option>
                        <option value="urun_stok">urun_stok</option>
                        <option value="urun_musteri_islem">urun_musteri_islem</option>
                        <option value="urun_tedarikci_alis">urun_tedarikci_alis</option>
                        <option value="users">users</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">İşlem Türü</label>
                    <select id="filterOperation" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                        <option value="">Tümü</option>
                        <option value="INSERT">INSERT</option>
                        <option value="UPDATE">UPDATE</option>
                        <option value="DELETE">DELETE</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Başlangıç Tarihi</label>
                    <input type="date" id="filterDateFrom" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Bitiş Tarihi</label>
                    <input type="date" id="filterDateTo" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                </div>
            </div>

            <div class="mt-4 flex justify-between">
                <button onclick="applyFilters()" class="bg-orange-600 text-white px-6 py-2 rounded-lg hover:bg-orange-700">
                    Filtrele
                </button>
                <button onclick="exportCSV()" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700">
                    CSV İndir
                </button>
            </div>
        </div>

        <!-- Logs Table -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Log Kayıtları</h3>

            <div id="logsTableContainer">
                <div class="animate-pulse space-y-4">
                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                    <div class="h-4 bg-gray-200 rounded w-full"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentFilters = {};

    async function loadLogs() {
        try {
            const params = new URLSearchParams(currentFilters);
            const response = await fetch('/api/admin/logs.php?' + params.toString());
            const data = await response.json();

            if (data.success) {
                renderLogsTable(data.data.logs);
            }
        } catch (error) {
            console.error('Error loading logs:', error);
        }
    }

    function renderLogsTable(logs) {
        const container = document.getElementById('logsTableContainer');

        if (logs.length === 0) {
            container.innerHTML = '<p class="text-gray-600">Log kaydı bulunamadı.</p>';
            return;
        }

        let html = '<div class="overflow-x-auto"><table class="w-full text-sm">';
        html += '<thead class="bg-slate-800 text-white"><tr>';
        html += '<th class="px-4 py-3 text-left">Log ID</th>';
        html += '<th class="px-4 py-3 text-left">Tablo</th>';
        html += '<th class="px-4 py-3 text-left">İşlem</th>';
        html += '<th class="px-4 py-3 text-left">Kayıt ID</th>';
        html += '<th class="px-4 py-3 text-left">Tarih</th>';
        html += '<th class="px-4 py-3 text-left">İşlem</th>';
        html += '</tr></thead><tbody>';

        logs.forEach(log => {
            const date = new Date(log.islem_zamani).toLocaleString('tr-TR');
            const operationColor = log.islem_turu === 'INSERT' ? 'text-green-600' : log.islem_turu === 'UPDATE' ? 'text-blue-600' : 'text-red-600';

            html += '<tr class="border-t border-gray-200 hover:bg-gray-50">';
            html += `<td class="px-4 py-3">${log.log_id}</td>`;
            html += `<td class="px-4 py-3 font-medium">${log.tablo_adi}</td>`;
            html += `<td class="px-4 py-3 ${operationColor} font-semibold">${log.islem_turu}</td>`;
            html += `<td class="px-4 py-3">${log.kayit_id}</td>`;
            html += `<td class="px-4 py-3">${date}</td>`;
            html += `<td class="px-4 py-3"><button onclick="showLogDetail(${log.log_id}, '${escapeHtml(log.eski_deger)}', '${escapeHtml(log.yeni_deger)}')" class="text-orange-600 hover:underline">Detay</button></td>`;
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    function applyFilters() {
        currentFilters = {
            table: document.getElementById('filterTable').value,
            operation: document.getElementById('filterOperation').value,
            date_from: document.getElementById('filterDateFrom').value,
            date_to: document.getElementById('filterDateTo').value
        };

        loadLogs();
    }

    function exportCSV() {
        const params = new URLSearchParams(currentFilters);
        window.location.href = '/api/admin/logs_export.php?' + params.toString();
    }

    function showLogDetail(logId, oldValue, newValue) {
        alert(`Log ID: ${logId}\n\nEski Değer:\n${oldValue.substring(0, 200)}...\n\nYeni Değer:\n${newValue.substring(0, 200)}...`);
    }

    function escapeHtml(text) {
        if (!text) return '';
        return text.replace(/'/g, "\\'").replace(/"/g, '&quot;');
    }

    loadLogs();
</script>

<?php include __DIR__ . '/../../../views/layouts/footer.php'; ?>
