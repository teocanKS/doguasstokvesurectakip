<!-- Top Bar -->
<div class="bg-white border-b border-slate-200 p-4 flex items-center justify-between">
    <div>
        <h2 class="text-2xl font-bold text-gray-900"><?= $pageTitle ?? 'Dashboard' ?></h2>
        <p class="text-sm text-gray-600"><?= $pageDescription ?? '' ?></p>
    </div>

    <div class="flex items-center space-x-4">
        <!-- Live Clock -->
        <div class="text-right">
            <div id="liveClock" class="text-lg font-semibold text-gray-900"></div>
            <div id="liveDate" class="text-xs text-gray-600"></div>
        </div>
    </div>
</div>

<script>
    // Live clock
    function updateClock() {
        const now = new Date();

        // Saat
        const hours = String(now.getHours()).padStart(2, '0');
        const minutes = String(now.getMinutes()).padStart(2, '0');
        const seconds = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('liveClock').textContent = `${hours}:${minutes}:${seconds}`;

        // Tarih
        const days = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
        const months = ['Ocak', 'Şubat', 'Mart', 'Nisan', 'Mayıs', 'Haziran', 'Temmuz', 'Ağustos', 'Eylül', 'Ekim', 'Kasım', 'Aralık'];

        const dayName = days[now.getDay()];
        const day = now.getDate();
        const month = months[now.getMonth()];
        const year = now.getFullYear();

        document.getElementById('liveDate').textContent = `${dayName}, ${day} ${month} ${year}`;
    }

    // İlk güncelleme
    updateClock();

    // Her saniye güncelle
    setInterval(updateClock, 1000);
</script>
