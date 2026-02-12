<?php
require_once __DIR__ . '/obj/User.php';

use assets\obj\User;

function checksForLogin() {
    if (!isLoggedIn()) {
        header('Location: /accounts/login');
        exit;
    }
}
function checksForAdmin() {
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Cache-Control: post-check=0, pre-check=0", false);
    header("Pragma: no-cache");
    header("Expires: 0");
    if (!isAdmin()) {
        header('Location: /');
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

function clearFailedSignUps() {
    $users = User::getAllWhere("NOT Verified AND NOT Enabled");
    foreach ($users as $u) $u->Delete();
}