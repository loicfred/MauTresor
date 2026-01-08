<?php
include __DIR__ . '/../../config/auth.php';

require_once __DIR__ . '/../../config/obj/Email_Verification.php';
require_once __DIR__ . '/../../config/obj/Notification.php';
require_once __DIR__ . '/../../config/obj/User.php';

use assets\obj\Email_Verification;
use assets\obj\Notification;
use assets\obj\User;

?>

<!DOCTYPE html>
<html xmlns:th="http://www.thymeleaf.org">
<head>
    <title>Account Verification</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#822BD9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon"  href="/assets/img/logo_transparent.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
<div class="container text-center">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow p-4">
                <?php
                if (isset($_GET["token"])) {
                    $emailVerif = Email_Verification::getByToken($_GET["token"]);
                    if ($emailVerif == null) {
                        echo "<h3 class='mb-3'>There is no verification here.</h3>";
                    }
                    else if ($emailVerif->isExpired()) {
                        echo "<h3 class='mb-3'>This verification code has expired. Try again.</h3>";
                        echo "<a href='/signup' class='btn btn-secondary mt-3'>Try Again</a>";
                        $emailVerif->Delete();
                    } else {
                        $user = User::getByID($emailVerif->UserID);
                        $user->Verified = true;
                        $user->Enabled = true;
                        $user->Update();
                        $emailVerif->Delete();
                        echo "<h3 class='mb-3'>Success !</h3>";
                        echo "<a href='/login?successVerif' class='btn btn-success mt-3'>Go to Login</a>";
                        $notif = new Notification();
                        $notif->UserID = $user->ID;
                        $notif->CreatedAt = date('Y-m-d H:i:s');
                        $notif->Title = "Account Verification";
                        $notif->Message = "Your account has been verified successfully.";
                        $notif->Write();
                    }
                }
                ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>