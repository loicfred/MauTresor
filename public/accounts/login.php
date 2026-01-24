<?php
include __DIR__ . '/../../config/auth.php';

require_once __DIR__ . '/../../config/obj/User.php';
require_once __DIR__ . '/../../config/obj/RememberMe.php';

use assets\obj\User;
use assets\obj\RememberMe;

if (isLoggedIn() && !isset($_GET["logout"])) header("Location: /");
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>

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
    <style>
        .google-btn {
            display: flex;
            align-items: center;
            gap: 10px;

            margin-left: 45px; margin-right: 45px;
            padding: 10px 16px;
            background-color: #ffffff;
            color: #3c4043;

            border: 1px solid #dadce0;
            border-radius: 4px;

            font-family: Arial, sans-serif;
            font-size: 14px;
            font-weight: 500;

            cursor: pointer;
            transition: background-color 0.2s, box-shadow 0.2s;
        }

        .google-btn:hover {
            background-color: #f7f8f8;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .google-btn:active {
            background-color: #eee;
        }

        .google-icon {
            width: 18px;
            height: 18px;
        }
    </style>
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
<main class="container text-center">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-4">
                <h3 class="mb-3">Welcome Back</h3>

                <?php
                if (isset($_GET["successVerif"])) {
                    echo "<div class='alert alert-success'>Your account has been verified! You can now log in.</div>";
                }
                if (isset($_GET["newpassword"])) {
                    echo "<div class='alert alert-success'>Your password has been reset successfully.</div>";
                }
                if (isLoggedIn() && isset($_GET["logout"])) {
                    session_destroy();
                    setcookie(session_name(), '', time() - 3600, '/');
                    setcookie('remember_me', '', time() - 3600);
                    $rememberMe = RememberMe::getByID($_SESSION["user_id"]);
                    if ($rememberMe) $rememberMe->Delete();
                    echo "<div class='alert alert-success'>You have been logged out successfully.</div>";
                }
                if (isset($_GET["checkEmail"])) {
                    echo "<div class='alert alert-success'>Please check your email to verify your account.</div>";
                }
                if (isset($_GET["deleted"])) {
                    echo "<div class='alert alert-success'> Your account has been deleted! You can now register once again..</div>";
                }

                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    $user = User::getByAuthentication(trim($_POST["Email"]), trim($_POST["Password"]));
                    if (!$user) {
                        echo "<div class='alert alert-danger'>Invalid email or password.</div>";
                    }
                    else if ($user->Verified === false) {
                        echo "<div class='alert alert-danger'>Verify your email first.</div>";
                    } else {
                        $_SESSION["user_id"] = $user->ID;
                        if (isset($_POST["remember-me"])) {
                            $rememberMe = new RememberMe();
                            $rememberMe->ID = $user->ID;
                            $rememberMe->Token = bin2hex(random_bytes(32));
                            $rememberMe->ExpiryDate = date('Y-m-d H:i:s', strtotime('+30 days'));
                            if ($rememberMe->Upsert()->ID === $user->ID) {
                                setcookie('remember_me', $rememberMe->Token, time() + 60*60*24*30, "/", "", true, true);
                            }
                        }
                        header("Location: /");
                    }
                }
                ?>
                <form action="/accounts/login" method="post">
                    <div class="mb-3">
                        <input type="text" name="Email" class="form-control" placeholder="Email" required>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="Password" class="form-control" placeholder="Password" required>
                    </div>
                    <div class="mb-3">
                        <input type="checkbox" class="form-check-box" name="remember-me" id="remember-me">
                        <label for="remember-me">Remember Me</label>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success w-50">Log in</button>
                        <a href="/accounts/signup" class="btn btn-primary w-50">Sign up</a>
                    </div>
                </form>
                <p class="mt-3">Forgot your password? <a href="/accounts/resetpassword">Reset</a></p>
                <a class="google-btn" href="/accounts/oauth2_google/google.php">
                <img class="google-icon" src="https://developers.google.com/identity/images/g-logo.png" alt="Google logo"/>
                    <div>Sign in with Google</div>
                </a>
            </div>
        </div>
    </div>
</main>
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.getRegistration().then(registration => {
            if (registration) {
                registration.unregister().then(success => {
                    console.log('Service worker unregistered:', success);
                });
            }
        });
    }
</script>

</body>
</html>