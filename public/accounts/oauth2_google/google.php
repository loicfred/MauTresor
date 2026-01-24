<?php

$client_id = "181292867676-ie3qguqaf718hkbop5qkf44m79nq97i2.apps.googleusercontent.com";
$redirect_uri = getOrigin() . "/accounts/oauth2_google/callback";

$state = bin2hex(random_bytes(16));
$_SESSION["google_oauth_state"] = $state;

$params = [
    "client_id" => $client_id,
    "redirect_uri" => $redirect_uri,
    "response_type" => "code",
    "scope" => "openid email profile",
    "state" => $state,
    "prompt" => "select_account"
];

header("Location: https://accounts.google.com/o/oauth2/v2/auth?" . http_build_query($params));
exit;

function getOrigin(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $scheme . '://' . $host;
}