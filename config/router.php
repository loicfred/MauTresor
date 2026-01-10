<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.mautresor.mu',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_name("MAUTRESOR_MU");
session_start();

set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("PHP ERROR [$errno] $errstr in $errfile:$errline");
    header('Location: error.php?msg=' . urlencode($errstr));
    exit;
});

set_exception_handler(function($exception) {
    error_log("UNCAUGHT EXCEPTION: " . $exception->getMessage());
    header('Location: error.php?msg=' . urlencode($exception->getMessage()));
    exit;
});

register_shutdown_function(function() {
    $error = error_get_last();
    if ($error !== null && in_array($error['type'], [E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE])) {
        error_log("FATAL ERROR: {$error['message']} in {$error['file']}:{$error['line']}");
        header('Location: error.php?msg=' . urlencode($error['message']));
        exit;
    }
});


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestedFile = __DIR__ . '/../public' . $uri;


if (is_file($requestedFile)) {
    return false;
}

switch ($uri) {
    case '/':
        include __DIR__ . '/../public/index.php';
        return true;
    case '/admin':
        include __DIR__ . '/../public/admin/index.php';
        return true;
    case '/api/v1/docs/':
        include __DIR__ . '/../public/api/v1/docs/index.html';
        return true;
    default:

        if (str_contains($uri, '/api/v1/admin/table/')) {
            include __DIR__ . '/../public/api/v1/admin/table.php';
        }


        else if (str_contains($uri, '/api/v1/img/')) {
            include __DIR__ . '/../public/api/v1/img.php';
        }
        else if (str_contains($uri, '/api/v1/event/')) {
            include __DIR__ . '/../public/api/v1/event.php';
        }
        else if (str_contains($uri, '/api/v1/place/')) {
            include __DIR__ . '/../public/api/v1/place.php';
        }



        else if (str_contains($uri, '/event/')) {
            include __DIR__ . '/../public/event.php';
        }
        else if (str_contains($uri, '/site/')) {
            include __DIR__ . '/../public/site.php';
        }
        else if (str_contains($uri, '/hint/')) {
            include __DIR__ . '/../public/hint.php';
        }
        else {
            include __DIR__ . '/../public' . $uri . '.php';
        }
        return true;
}




