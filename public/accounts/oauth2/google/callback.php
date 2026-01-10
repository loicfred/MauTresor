<?php
require 'vendor/autoload.php';

require_once __DIR__ . '/../../../../config/obj/User.php';
use assets\obj\User;

$client = new Google\Client();
$client->setClientId('YOUR_CLIENT_ID');
$client->setClientSecret('YOUR_CLIENT_SECRET');
$client->setRedirectUri('http://mautresor.mu/accounts/oauth2/google/callback.php');

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
$client->setAccessToken($token);

$payload = $client->verifyIdToken();

if ($payload === null) {
    header('Location: /accounts/login');
    exit;
}


$user = User::getByEmail($payload['email']);
if (!$user) {
    $user = new User();
    $user->AccountProvider = "Google";
    $user->Email = $payload['email'];
    $user->Password = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
    $user->FirstName = $payload['given_name'];
    $user->LastName = $payload['family_name'];
    $user->Verified = true;
    $user->Enabled = true;
    $user->Gender = 'Other';
    $user->CreatedAt = date("Y-m-d H:i:s");
    $user->UpdatedAt = date("Y-m-d H:i:s");
    $user->Role = 'USER';
    $user->Write();
}

// save whatever you need
$_SESSION['user'] = [
    'email' => $payload['email'],
    'name'  => $payload['name'],
];

// 🔁 REDIRECT TO HOME PAGE
header('Location: home.php');
exit;