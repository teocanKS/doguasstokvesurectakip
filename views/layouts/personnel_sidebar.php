<?php
// Aktif sayfa kontrolü için
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>

<!-- Sidebar -->
<aside id="sidebar" class="fixed left-0 top-0 h-full w-64 bg-gradient-to-b from-blue-600 to-blue-700 text-white shadow-xl z-40 transform transition-transform duration-300 lg:translate-x-0 -translate-x-full">
    <!-- Logo -->
    <div class="p-6 border-b border-white/10">
        <div class="flex items-center space-x-3">
            <div class="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <div>
                <h1 class="text-lg font-bold">Doğu AŞ</h1>
                <p class="text-xs text-blue-200">Personel Paneli</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="p-4">
        <ul class="space-y-2">
            <li>
                <a href="/personnel/dashboard/index.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?= $currentPage === 'index' ? 'bg-white/20 text-white' : 'text-blue-100 hover:bg-white/10 hover:text-white' ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Anasayfa
                </a>
            </li>
            <li>
                <a href="/personnel/stock/index.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?= strpos($currentPage, 'stock') !== false ? 'bg-white/20 text-white' : 'text-blue-100 hover:bg-white/10 hover:text-white' ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    Stok Durumu
                </a>
            </li>
            <li>
                <a href="/personnel/transactions/active.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?= $currentPage === 'active' ? 'bg-white/20 text-white' : 'text-blue-100 hover:bg-white/10 hover:text-white' ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    Aktif İşler
                </a>
            </li>
            <li>
                <a href="/personnel/transactions/history.php" class="flex items-center px-4 py-3 rounded-lg transition-colors <?= $currentPage === 'history' ? 'bg-white/20 text-white' : 'text-blue-100 hover:bg-white/10 hover:text-white' ?>">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Geçmiş İşler
                </a>
            </li>
        </ul>
    </nav>

    <!-- User Info & Logout -->
    <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-white/10">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                    <span class="text-sm font-bold"><?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 1)) ?></span>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium truncate"><?= htmlspecialchars($_SESSION['user_name'] ?? '') ?></p>
                    <p class="text-xs text-blue-200">Personel</p>
                </div>
            </div>
            <a href="/auth/logout.php" class="p-2 hover:bg-white/10 rounded-lg transition-colors" title="Çıkış Yap">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                </svg>
            </a>
        </div>
    </div>
</aside>

<!-- Mobile menu button -->
<button id="menuToggle" class="lg:hidden fixed top-4 left-4 z-50 p-2 bg-blue-600 text-white rounded-lg shadow-lg">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
    </svg>
</button>

<!-- Overlay -->
<div id="overlay" class="lg:hidden fixed inset-0 bg-black/50 z-30 hidden"></div>

<script>
    // Mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');

    menuToggle.addEventListener('click', () => {
        sidebar.classList.toggle('-translate-x-full');
        overlay.classList.toggle('hidden');
    });

    overlay.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        overlay.classList.add('hidden');
    });
</script>
