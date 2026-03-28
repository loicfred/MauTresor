<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/obj/User.php';

use Kreait\Firebase\Factory;
use assets\obj\User;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$data = json_decode(file_get_contents("php://input"), true);
if (!$data || empty($data['token'])) {
    http_response_code(400);
    exit('Missing token');
}

$tokenString = $data['token'];

try {
    $factory = (new Factory)->withServiceAccount(__DIR__ . '/../../config/firebase_service_account.json');
    $auth = $factory->createAuth();

    $verifiedToken = $auth->verifyIdToken($tokenString);

    $claims = $verifiedToken->claims();
    $email = $claims->get('email');

    if (!$email) {
        throw new Exception('Email not found in token');
    }

    // Find or create user
    $user = User::getByEmail($email);

    if (!$user) {
        $user = new User();
        $user->Email = $email;
        $user->Password = password_hash(random_bytes(12), PASSWORD_DEFAULT);
        $user->AccountProvider = 'Firebase';
        $user->Verified = true;
        $user->Enabled = true;
        $user->Role = 'USER';
        $user->CreatedAt = date('Y-m-d H:i:s');
        $user->UpdatedAt = date('Y-m-d H:i:s');
        $user->Write();
    }
    if ($claims->get('picture')) {
        $url = $claims->get('picture');
        $imageBytes = file_get_contents($url);
        if ($imageBytes !== false) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->buffer($imageBytes);

            $user->Image = base64_encode($imageBytes);
            $user->MimeType = $mimeType;
            $user->Update();
        }
    }

    $_SESSION['user_id'] = $user->ID;

    http_response_code(200);
    echo json_encode(['ok' => true]);
    exit;

} catch (Throwable $e) {
    http_response_code(401);
    header('Content-Type: text/plain');
    echo $e->getMessage();
    exit;
}
