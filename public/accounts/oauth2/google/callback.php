<?php
require __DIR__ . '/../../../../vendor/autoload.php';

require_once __DIR__ . '/../../../../config/obj/User.php';
use assets\obj\User;

$client = new Google\Client();
$client->setClientId('181292867676-ie3qguqaf718hkbop5qkf44m79nq97i2.apps.googleusercontent.com');
$client->setClientSecret('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');
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

$_SESSION['user'] = [
    'email' => $payload['email'],
    'name'  => $payload['name'],
];

header('Location: /');
exit;