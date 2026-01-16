<?php
require __DIR__ . '/../../../../vendor/autoload.php';

use Google\Client;
if ($_SERVER["REQUEST_METHOD"] === "GET") {
    $client = new Client();
    $client->setClientId('181292867676-ie3qguqaf718hkbop5qkf44m79nq97i2.apps.googleusercontent.com');
    $client->setClientSecret('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');
    $client->setRedirectUri('http://mautresor.mu/accounts/oauth2/google/callback.php');
    $client->setScopes(['email', 'profile']);
    header('Location: ' . $client->createAuthUrl());
}
?>