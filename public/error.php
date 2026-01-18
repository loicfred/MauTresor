<?php
include __DIR__ . '/../config/auth.php';

$msg = isset($_GET['msg']) ? htmlspecialchars($_GET['msg']) : 'An unexpected error occurred.';
?>

<!DOCTYPE html>
<html lang="en">
<head id="master-head">
    <title>Error | MauDonate</title>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#822BD9">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="icon" href="/assets/img/logo_transparent.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="/assets/css/main.css">

    <style>
        main {
            padding: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 70vh;
        }

        .error-card {
            max-width: 90%;
            margin: auto;
            padding: 2rem 2rem;
            background: var(--primary-color-lighter);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            text-align: center;
            display: flex;
            flex-direction: column;
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dc3545;
        }

        .error-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 1rem;
        }

        .error-message {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            text-wrap: wrap;
            word-break: break-word;
        }

        .error-code {
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }
    </style>
</head>
<body>

<?php
require_once __DIR__ . '/assets/fragments/header.php';
?>

<main>
    <div class="error-card">
        <div class="error-icon">⚠️</div>
        <h1 class="error-title">Error</h1>
        <p class="error-message"><?= $msg ?></p>
        <a href="/" class="btn btn-primary">Go to home</a>
    </div>
</main>

<script src="/assets/js/app.js"></script>

</body>
</html>

