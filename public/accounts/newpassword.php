<?php
require_once __DIR__ . '/../../config/obj/User.php';
require_once __DIR__ . '/../../config/obj/Notification.php';
require_once __DIR__ . '/../../config/obj/Email_Verification.php';

use assets\obj\User;
use assets\obj\Notification;
use assets\obj\Email_Verification;

$emailVerif = Email_Verification::getByToken($_GET["token"]);
if ($emailVerif == null) {
    header("Location: /accounts/resetpassword?expired");
} else if ($emailVerif->isExpired()) {
    $emailVerif->Delete();
    header("Location: /accounts/resetpassword?expired");
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>New Password</title>

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
<main class="container text-center">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-4">
                <h3 class="mb-3">Enter your new password</h3>

                <?php
                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    $pwrd1 = trim($_POST["Password1"]);
                    $pwrd2 = trim($_POST["Password2"]);
                    if ($pwrd1 !== $pwrd2) {
                        echo "<div class='alert alert-danger'>Passwords do not match.</div>";
                    } else if (User::validatePassword($pwrd1) !== true) {
                        echo "<div class='alert alert-danger'>Password should be of minimum 8 of length and contain at least 1 digit, symbol, uppercase & lowercase character.</div>";
                    } else {
                        $user = User::getByID($emailVerif->UserID);
                        $user->Password = password_hash($_POST["Password"], PASSWORD_DEFAULT);;
                        $user->Update();
                        $emailVerif->Delete();
                        $notif = new Notification();
                        $notif->UserID = $user->ID;
                        $notif->isRead = false;
                        $notif->CreatedAt = date('Y-m-d H:i:s');
                        $notif->Title = "Password has been changed!";
                        $notif->Message = "Your password has been reset successfully.";
                        $notif->Write();
                        header("Location: /accounts/login?newpassword");
                    }
                }
                ?>
                <form action="/accounts/newpassword?token=<?= $_GET('token') ?>" method="post">
                    <div class="mb-3">
                        <input type="password" name="Password1" class="form-control" placeholder="New Password" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="Password2" class="form-control" placeholder="Retype your password" required>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Reset Password</button>
                </form>
            </div>
        </div>
    </div>
</main>
<script src="/assets/js/app.js"></script>

</body>
</html>
