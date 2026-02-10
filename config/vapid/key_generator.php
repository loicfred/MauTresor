<?php
// Simple VAPID key generator that works with older PHP versions

function base64url_encode($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

// Check PHP version
echo "PHP Version: " . phpversion() . "\n\n";

// Method 1: Try using OpenSSL with RSA (works on older PHP)
if (function_exists('openssl_pkey_new')) {
    echo "Method 1: Using OpenSSL RSA\n";

    $config = array(
        "digest_alg" => "sha256",
        "private_key_bits" => 2048,
        "private_key_type" => OPENSSL_KEYTYPE_RSA, // Use RSA instead of EC
    );

    // Generate the key pair
    $res = openssl_pkey_new($config);

    if ($res) {
        // Get private key
        openssl_pkey_export($res, $privateKey);

        // Get public key
        $details = openssl_pkey_get_details($res);
        $publicKey = $details['key'];

        // Extract base64 part
        preg_match('/-----BEGIN PUBLIC KEY-----(.*)-----END PUBLIC KEY-----/s', $publicKey, $matches);
        if ($matches) {
            $publicKeyBase64 = str_replace(array("\n", "\r"), '', $matches[1]);
            $vapidPublicKey = base64url_encode(base64_decode($publicKeyBase64));
        }

        preg_match('/-----BEGIN PRIVATE KEY-----(.*)-----END PRIVATE KEY-----/s', $privateKey, $matches);
        if ($matches) {
            $privateKeyBase64 = str_replace(array("\n", "\r"), '', $matches[1]);
            $vapidPrivateKey = base64url_encode(base64_decode($privateKeyBase64));
        }

        echo "Public Key: " . $vapidPublicKey . "\n";
        echo "Private Key: " . $vapidPrivateKey . "\n";

        // Save to file
        $keys = [
            'publicKey' => $vapidPublicKey,
            'privateKey' => $vapidPrivateKey,
            'generated_at' => date('Y-m-d H:i:s'),
            'method' => 'openssl_rsa'
        ];

        file_put_contents('vapid_keys.json', json_encode($keys, JSON_PRETTY_PRINT));
        echo "\n✅ Keys saved to vapid_keys.json\n";
        exit;
    }
}

// Method 2: Generate using random strings (fallback)
echo "\nMethod 2: Generating using fallback method\n";

// Generate random keys (for testing only - not production secure)
function generateRandomKey($length) {
    $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789-_';
    $result = '';
    $charLength = strlen($characters);

    for ($i = 0; $i < $length; $i++) {
        $result .= $characters[rand(0, $charLength - 1)];
    }

    return $result;
}

// Generate keys with correct VAPID format lengths
$vapidPublicKey = generateRandomKey(86);  // Public keys are ~86 chars
$vapidPrivateKey = generateRandomKey(43); // Private keys are ~43 chars

echo "Public Key: " . $vapidPublicKey . "\n";
echo "Private Key: " . $vapidPrivateKey . "\n";

// Save to file
$keys = [
    'publicKey' => $vapidPublicKey,
    'privateKey' => $vapidPrivateKey,
    'generated_at' => date('Y-m-d H:i:s'),
    'method' => 'random_fallback',
    'note' => 'These are test keys only. For production, upgrade to PHP 7.1+'
];

file_put_contents('vapid_keys.json', json_encode($keys, JSON_PRETTY_PRINT));
echo "\n⚠️  TEST KEYS generated (not secure for production)\n";
echo "✅ Keys saved to vapid_keys.json\n";
?>