<?php
require_once __DIR__ . '/../../../config/obj/User.php';
use assets\obj\User;

// 1) Validate state (CSRF protection)
if (
    !isset($_GET['state']) ||
    !isset($_SESSION['google_oauth_state']) ||
    !hash_equals($_SESSION['google_oauth_state'], $_GET['state'])
) {
    echo $_GET['state'];
    echo $_SESSION['google_oauth_state'];
    header('Location: /accounts/login?error=invalid_state');
    exit;
}
unset($_SESSION['google_oauth_state']);

// 2) Must have code
if (!isset($_GET['code'])) {
    header('Location: /accounts/login?error=no_code');
    exit;
}
$code = $_GET['code'];

// 3) OAuth credentials
$client_id = '181292867676-ie3qguqaf718hkbop5qkf44m79nq97i2.apps.googleusercontent.com';
$client_secret = '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';
$redirect_uri = getOrigin() . '/accounts/oauth2_google/callback';

// 4) Exchange code for token
$token_url = 'https://oauth2.googleapis.com/token';
$post = [
    'code' => $code,
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'grant_type' => 'authorization_code',
];

$ch = curl_init($token_url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query($post),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],

    // ✅ WAMP/Windows SSL workaround (remove later in production)
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,

    // timeouts
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 30,
]);

$resp = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$errno = curl_errno($ch);
$error = curl_error($ch);
curl_close($ch);

// If curl itself failed (network/SSL/etc.)
if ($resp === false) {
    // Send useful debug
    echo "<h3>cURL failed</h3>";
    echo "<pre>errno: " . htmlspecialchars((string)$errno) . "</pre>";
    echo "<pre>error: " . htmlspecialchars((string)$error) . "</pre>";
    exit;
}

// If Google returned an error (non-200)
if ($http !== 200) {
    echo "<h3>Token exchange failed</h3>";
    echo "<pre>HTTP: " . htmlspecialchars((string)$http) . "</pre>";
    echo "<pre>Response: " . htmlspecialchars((string)$resp) . "</pre>";
    exit;
}

$tok = json_decode($resp, true);
$access_token = $tok['access_token'] ?? null;

if (!$access_token) {
    echo "<h3>Missing access_token</h3>";
    echo "<pre>Response: " . htmlspecialchars((string)$resp) . "</pre>";
    exit;
}

// 5) Fetch Google user info
$userinfo_url = 'https://www.googleapis.com/oauth2/v3/userinfo';
$ch = curl_init($userinfo_url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Authorization: Bearer ' . $access_token],

    // ✅ same SSL workaround
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,

    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_TIMEOUT => 30,
]);

$u = curl_exec($ch);
$http2 = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($http2 !== 200) {
    echo "<h3>Userinfo failed</h3>";
    echo "<pre>HTTP: " . htmlspecialchars((string)$http2) . "</pre>";
    echo "<pre>Response: " . htmlspecialchars((string)$u) . "</pre>";
    exit;
}

$payload = json_decode($u, true);

$email  = $payload['email'] ?? null;
$given  = $payload['given_name'] ?? '';
$family = $payload['family_name'] ?? '';
$name   = $payload['name'] ?? trim($given . ' ' . $family);

if (!$email) {
    header('Location: /accounts/login?error=no_email');
    exit;
}

// 6) Find or create user in DB
$user = User::getByEmail($email);

if (!$user) {
    $user = new User();
    $user->AccountProvider = "Google";
    $user->Email = $email;
    $user->Password = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
    $user->FirstName = $given ?: $name;
    $user->LastName = $family ?: '';
    $user->Verified = true;
    $user->Enabled = true;
    $user->Gender = 'Other';
    $user->CreatedAt = date("Y-m-d H:i:s");
    $user->UpdatedAt = date("Y-m-d H:i:s");
    $user->Role = 'USER';
    $user->Write();
}

// ✅ Log in using your normal system
$_SESSION['user_id'] = $user->ID;

// 7) Redirect home
header('Location: /');
exit;

function getOrigin(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $scheme . '://' . $host;
}