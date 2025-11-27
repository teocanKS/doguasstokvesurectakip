<?php
/**
 * Root index - Redirect to appropriate page
 */

require_once __DIR__ . '/../src/helpers/helpers.php';

// Giriş yapmışsa dashboard'a yönlendir
if (isLoggedIn()) {
    $redirect = isAdmin() ? '/admin/dashboard/index.php' : '/personnel/dashboard/index.php';
    header("Location: $redirect");
} else {
    // Giriş yapmamışsa login sayfasına yönlendir
    header('Location: /auth/login.php');
}
exit;
