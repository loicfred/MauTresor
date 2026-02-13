<?php
require_once __DIR__ . '/../../config/mailer.php';
require_once __DIR__ . '/../../config/obj/User.php';
require_once __DIR__ . '/../../config/obj/Email_Verification.php';

use assets\obj\User;
use assets\obj\Email_Verification;
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reset Password</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#957304">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon"  href="/assets/img/logo_transparent.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        html {
            background: url('/assets/img/bg2.png') no-repeat;
            background-size: cover;
        }
        body {
            background-color: #00000088;
        }
    </style>
</head>
<body class="d-flex align-items-center" style="height: 100vh;">
<main class="container text-center">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-4">
                <h3 class="mb-3">Reset Password</h3>
                <?php
                try {
                    if (isset($_GET["expired"])) {
                        echo "<div class='alert alert-danger'>The reset code has expired.</div>";
                    }
                    if (isset($_GET["successEmail"])) {
                        echo "<div class='alert alert-success'>An email has been sent to your email address with further instructions.</div>";
                    }
                    if (isset($_GET["successPhone"])) {
                        echo "<div class='alert alert-success'>An SMS has been sent to your phone number with further instructions.</div>";
                    }

                    if ($_SERVER["REQUEST_METHOD"] === "POST") {
                        $user = null;
                        if (isset($_GET["type"])) {
                            if ($_GET["type"] === "email") {
                                $user = User::getByEmail(trim($_POST["Email"]));
                                if (!$user) {
                                    echo "<div class='alert alert-danger'>Invalid email.</div>";
                                } else {
                                    $email = new Email_Verification();
                                    $email->UserID = $user->ID;
                                    $email->Token = $token = bin2hex(random_bytes(32));
                                    $email->Type = 'PASSWORD_RESET';
                                    $email->ExpiryDate = date('Y-m-d H:i:s', strtotime('+10 minutes'));
                                    $email->Write();
                                    sendNewPassword($user->Email, $email->Token);
                                    header("Location: /accounts/resetpassword?successEmail");
                                }
                            } else if ($_GET["type"] === "phone") {
                                $user = User::getByPhone(trim($_POST["Phone"]));
                                if (!$user) {
                                    echo "<div class='alert alert-danger'>Invalid phone.</div>";
                                } else {
                                    $token = bin2hex(random_bytes(32));

                                    // Phone number send sms logic

                                    header("Location: /accounts/resetpassword?successPhone");
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    echo "<div class='alert alert-danger'>An error occurred: " . $e->getMessage() . "</div>";
                }
                ?>

                <ul class="nav nav-tabs mb-3" id="resetTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="email-tab" data-bs-toggle="tab" data-bs-target="#email" type="button" role="tab">Email</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="phone-tab" data-bs-toggle="tab" data-bs-target="#phone" type="button" role="tab">Phone</button>
                    </li>
                </ul>

                <div class="tab-content" id="resetTabContent">
                    <div class="tab-pane fade show active" id="email" role="tabpanel">
                        <form action="/accounts/resetpassword?type=email" method="post">
                            <div class="mb-3">
                                <label for="Email">Email Address:</label>
                                <input type="email" name="Email" class="form-control" placeholder="Email" required>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">Send Email</button>
                        </form>
                    </div>
                    <div class="tab-pane fade" id="phone" role="tabpanel">
                        <form action="/accounts/resetpassword?type=phone" method="post">
                            <div class="mb-3">
                                <label for="Phone">Phone Number:</label>
                                <input name="Phone" class="form-control" placeholder="Phone" required>
                            </div>
                            <button type="submit" class="btn btn-danger w-100">Send SMS</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script src="/assets/js/app.js"></script>

</body>
</html>
