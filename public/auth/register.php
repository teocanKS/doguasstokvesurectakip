<?php
/**
 * Register Page
 * Kullanıcı kayıt sayfası
 */

// Helper fonksiyonlarını yükle
require_once __DIR__ . '/../../src/helpers/helpers.php';

// Zaten giriş yapmışsa dashboard'a yönlendir
if (isLoggedIn()) {
    $redirect = isAdmin() ? '/admin/dashboard/index.php' : '/personnel/dashboard/index.php';
    header("Location: $redirect");
    exit;
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kayıt Ol - Doğu AŞ</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo ve Başlık -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-20 h-20 bg-orange-500 rounded-full mb-4">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-900">Doğu AŞ</h1>
            <p class="text-gray-600 mt-2">Stok ve Süreç Takip Sistemi</p>
        </div>

        <!-- Register Card -->
        <div class="bg-white/95 backdrop-blur-sm rounded-3xl shadow-xl p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Kayıt Ol</h2>

            <!-- Alert -->
            <div id="alert" class="hidden mb-4 p-4 rounded-lg"></div>

            <!-- Register Form -->
            <form id="registerForm" class="space-y-4">
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Ad *
                    </label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
                        placeholder="Adınız"
                    >
                </div>

                <!-- Surname -->
                <div>
                    <label for="surname" class="block text-sm font-medium text-gray-700 mb-2">
                        Soyad
                    </label>
                    <input
                        type="text"
                        id="surname"
                        name="surname"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
                        placeholder="Soyadınız"
                    >
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email *
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
                        placeholder="email@example.com"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Şifre *
                    </label>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        required
                        minlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
                        placeholder="En az 6 karakter"
                    >
                </div>

                <!-- Password Confirm -->
                <div>
                    <label for="password_confirm" class="block text-sm font-medium text-gray-700 mb-2">
                        Şifre Tekrar *
                    </label>
                    <input
                        type="password"
                        id="password_confirm"
                        name="password_confirm"
                        required
                        minlength="6"
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition"
                        placeholder="Şifrenizi tekrar girin"
                    >
                </div>

                <!-- Info -->
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                    <p class="text-sm text-blue-800">
                        <strong>Not:</strong> Kayıt sonrası hesabınız yönetici onayından sonra aktif olacaktır.
                    </p>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white py-3 rounded-lg font-medium hover:from-orange-600 hover:to-orange-700 transition-all duration-200 shadow-md hover:shadow-lg"
                >
                    Kayıt Ol
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center">
                <p class="text-sm text-gray-600">
                    Zaten hesabınız var mı?
                    <a href="/auth/login.php" class="text-orange-600 hover:text-orange-700 font-medium">
                        Giriş Yap
                    </a>
                </p>
            </div>
        </div>
    </div>

    <script>
        // Register form submission
        document.getElementById('registerForm').addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitBtn = document.getElementById('submitBtn');
            const alert = document.getElementById('alert');

            // Get form data
            const formData = {
                name: document.getElementById('name').value,
                surname: document.getElementById('surname').value,
                email: document.getElementById('email').value,
                password: document.getElementById('password').value,
                password_confirm: document.getElementById('password_confirm').value
            };

            // Disable button
            submitBtn.disabled = true;
            submitBtn.textContent = 'Kaydediliyor...';

            try {
                const response = await fetch('/api/auth/register.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();

                if (data.success) {
                    // Success
                    showAlert('success', data.message);
                    document.getElementById('registerForm').reset();
                    setTimeout(() => {
                        window.location.href = '/auth/login.php';
                    }, 3000);
                } else {
                    // Error
                    showAlert('error', data.error);
                    submitBtn.disabled = false;
                    submitBtn.textContent = 'Kayıt Ol';
                }
            } catch (error) {
                showAlert('error', 'Bir hata oluştu. Lütfen tekrar deneyin.');
                submitBtn.disabled = false;
                submitBtn.textContent = 'Kayıt Ol';
            }
        });

        function showAlert(type, message) {
            const alert = document.getElementById('alert');
            alert.className = 'mb-4 p-4 rounded-lg ' + (type === 'success' ? 'bg-green-50 text-green-800 border border-green-200' : 'bg-red-50 text-red-800 border border-red-200');
            alert.textContent = message;
            alert.classList.remove('hidden');

            if (type === 'success') {
                setTimeout(() => {
                    alert.classList.add('hidden');
                }, 5000);
            }
        }
    </script>

</body>
</html>
