<?php
/**
 * User Approvals Page
 * Kullanıcı onaylama sayfası (Sadece admin)
 */

require_once __DIR__ . '/../../../src/helpers/helpers.php';
requireAdmin();

$pageTitle = 'Kullanıcı Onayları';
$pageDescription = 'Onay bekleyen kullanıcıları yönetin';

include __DIR__ . '/../../../views/layouts/header.php';
?>

<?php include __DIR__ . '/../../../views/layouts/admin_sidebar.php'; ?>

<div class="lg:ml-64 min-h-screen">
    <?php include __DIR__ . '/../../../views/layouts/topbar.php'; ?>

    <div class="p-6">
        <!-- Alert -->
        <div id="alert" class="hidden mb-4 p-4 rounded-lg"></div>

        <!-- Pending Users Card -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Onay Bekleyen Kullanıcılar</h3>

            <!-- Table -->
            <div id="usersTableContainer">
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
    let pendingUsers = [];

    async function loadPendingUsers() {
        try {
            const response = await fetch('/api/admin/users.php');
            const data = await response.json();

            if (data.success) {
                pendingUsers = data.data.users;
                renderUsersTable();
            }
        } catch (error) {
            console.error('Error loading users:', error);
            showAlert('error', 'Kullanıcılar yüklenirken hata oluştu');
        }
    }

    function renderUsersTable() {
        const container = document.getElementById('usersTableContainer');

        if (pendingUsers.length === 0) {
            container.innerHTML = '<p class="text-gray-600">Onay bekleyen kullanıcı yok.</p>';
            return;
        }

        let html = '<div class="overflow-x-auto"><table class="w-full">';
        html += '<thead class="bg-slate-100"><tr>';
        html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Ad Soyad</th>';
        html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Email</th>';
        html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">Kayıt Tarihi</th>';
        html += '<th class="px-6 py-3 text-left text-xs font-medium text-gray-700 uppercase">İşlemler</th>';
        html += '</tr></thead><tbody>';

        pendingUsers.forEach(user => {
            const fullName = `${user.name} ${user.surname || ''}`.trim();
            const createdAt = new Date(user.created_at).toLocaleDateString('tr-TR');

            html += '<tr class="border-t border-gray-200 hover:bg-gray-50">';
            html += `<td class="px-6 py-4 text-sm text-gray-900">${fullName}</td>`;
            html += `<td class="px-6 py-4 text-sm text-gray-900">${user.email}</td>`;
            html += `<td class="px-6 py-4 text-sm text-gray-600">${createdAt}</td>`;
            html += '<td class="px-6 py-4 text-sm">';
            html += `<button onclick="approveUser(${user.users_id})" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 mr-2">Onayla</button>`;
            html += `<button onclick="rejectUser(${user.users_id})" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700">Reddet</button>`;
            html += '</td>';
            html += '</tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    async function approveUser(userId) {
        if (!confirm('Bu kullanıcıyı onaylamak istediğinize emin misiniz?')) return;

        try {
            const response = await fetch('/api/admin/users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, action: 'approve' })
            });

            const data = await response.json();

            if (data.success) {
                showAlert('success', data.message);
                loadPendingUsers();
            } else {
                showAlert('error', data.error);
            }
        } catch (error) {
            showAlert('error', 'İşlem sırasında hata oluştu');
        }
    }

    async function rejectUser(userId) {
        if (!confirm('Bu kullanıcıyı reddetmek istediğinize emin misiniz? Bu işlem geri alınamaz.')) return;

        try {
            const response = await fetch('/api/admin/users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId, action: 'reject' })
            });

            const data = await response.json();

            if (data.success) {
                showAlert('success', data.message);
                loadPendingUsers();
            } else {
                showAlert('error', data.error);
            }
        } catch (error) {
            showAlert('error', 'İşlem sırasında hata oluştu');
        }
    }

    function showAlert(type, message) {
        const alert = document.getElementById('alert');
        alert.className = 'mb-4 p-4 rounded-lg ' + (type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200');
        alert.textContent = message;
        alert.classList.remove('hidden');

        setTimeout(() => alert.classList.add('hidden'), 5000);
    }

    loadPendingUsers();
</script>

<?php include __DIR__ . '/../../../views/layouts/footer.php'; ?>
