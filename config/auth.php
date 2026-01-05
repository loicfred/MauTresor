<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.mautresor.mu',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_name("MAUTRESOR_MU");
session_start();
require_once __DIR__ . '/../assets/obj/RememberMe.php';
require_once __DIR__ . '/../assets/obj/User.php';

use assets\obj\RememberMe;
use assets\obj\User;

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    $rememberMe = RememberMe::getByToken($_COOKIE['remember_me']);
    if ($rememberMe !== null && !$rememberMe->isExpired()) {
        $_SESSION['user_id'] = $rememberMe->ID;
        $newToken = bin2hex(random_bytes(32));
        $rememberMe->Token = $newToken;
        $rememberMe->Update();
        setcookie('remember_me', $rememberMe->Token, strtotime($rememberMe->ExpiryDate), '/');
    } else {
        setcookie('remember_me', '', time() - 3600, '/');
    }
}

function checksForLogin() {
    if (!isLoggedIn()) {
        header('Location: https://mautresor.mu/accounts/login');
        exit;
    }
}
function checksForAdmin() {
    if (!isAdmin()) {
        header('Location: https://mautresor.mu');
        exit;
    }
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);

}
function isAdmin() {
    if (isLoggedIn()) {
        $user = User::getByID($_SESSION['user_id']);
        if ($user && $user->Role == 'ADMIN') return true;
    } return false;
}