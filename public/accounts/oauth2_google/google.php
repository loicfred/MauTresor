<?php
session_start();

$client_id = "982439855257-ve31uufongid8ajda5io96r9o5fev27a.apps.googleusercontent.com";
$redirect_uri = "https://mautresor.mu/accounts/oauth2_google/callback.php";

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
