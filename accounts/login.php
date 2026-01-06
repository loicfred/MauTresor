<?php
include __DIR__ . '/../config/auth.php';

require_once __DIR__ . '/../assets/obj/User.php';
require_once __DIR__ . '/../assets/obj/RememberMe.php';

use assets\obj\RememberMe;
use assets\obj\User;

?>

<!DOCTYPE html>
<html xmlns:th="http://www.thymeleaf.org">
<head>
    <title>Login</title>
    <link rel="manifest" href="manifest.json">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
<div class="container text-center">
    <div class="row justify-content-center">
        <div class="col-md-4">
            <div class="card shadow p-4">
                <h3 class="mb-3">Welcome Back</h3>

                <?php
                if (isset($_GET["successVerif"])) {
                    echo "<div class='alert alert-success'>Your account has been verified! You can now log in.</div>";
                }
                if (isLoggedIn() && isset($_GET["logout"])) {
                    RememberMe::getByID($_SESSION["user_id"])->Delete();
                    session_destroy();
                    setcookie('remember_me', '', time() - 3600);
                    echo "<div class='alert alert-success'>You have been logged out successfully.</div>";
                }
                if (isset($_GET["checkEmail"])) {
                    echo "<div class='alert alert-success'>Please check your email to verify your account.</div>";
                }
                if (isset($_GET["deleted"])) {
                    echo "<div class='alert alert-success'> Your account has been deleted! You can now register once again..</div>";
                }

                if ($_SERVER["REQUEST_METHOD"] === "POST") {
                    $user = User::getByAuthentication($_POST["Email"], $_POST["Password"]);
                    if (!$user) {
                        echo "<div class='alert alert-danger'>Invalid email or password</div>";
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
                        header("Location: https://mautresor.mu");
                    }
                }
                ?>
                <form action="/login" method="post">
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
                    <button type="submit" class="btn btn-danger w-100">Log In</button>
                </form>
                <p class="mt-3">Don’t have an account? <a href="/signup">Sign up</a></p>
                <a href="/oauth2/authorization/google">
                    Sign in with Google
                </a>
            </div>
        </div>
    </div>
</div>
</body>
<script>
    (() => {
        'use strict';

        if (!('serviceWorker' in navigator)) {
            console.info('[PWA] Service workers not supported');
            return;
        }

        window.addEventListener('load', async () => {
            try {
                const existing = await navigator.serviceWorker.getRegistration('/');
                if (existing) {
                    console.log('[PWA] Service Worker already registered:', existing.scope);
                    return;
                }

                const registration = await navigator.serviceWorker.register(
                    '/service-worker.js',
                    { scope: '/' }
                );

                console.log('[PWA] Service Worker registered:', registration.scope);
            } catch (err) {
                console.error('[PWA] Service Worker registration failed:', err);
            }
        });
    })();
</script>
</html>