<?php
// public/api/qrcode.php
declare(strict_types=1);

$raw = isset($_GET["code"]) ? trim((string)$_GET["code"]) : "";
if ($raw === "") {
    http_response_code(400);
    header("Content-Type: text/plain; charset=utf-8");
    echo "Missing code";
    exit;
}

// Convert relative paths like /map?place=1 into absolute URL (best for phone scanners)
$code = $raw;
if (!preg_match('#^https?://#i', $code)) {
    $scheme = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
    $host   = $_SERVER["HTTP_HOST"] ?? "localhost";
    // ensure it starts with /
    if ($code[0] !== "/") $code = "/" . $code;
    $code = $scheme . "://" . $host . $code;
}

// Autoload (your composer install is in /qr_vendor)
$autoload = __DIR__ . "/../../qr_vendor/vendor/autoload.php";
if (!file_exists($autoload)) {
    http_response_code(500);
    header("Content-Type: text/plain; charset=utf-8");
    echo "Autoload not found: " . $autoload;
    exit;
}
require_once $autoload;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

try {
    $qrCode = new QrCode($code);

    // Good scanning defaults
    $qrCode->setSize(360);
    $qrCode->setMargin(18);

    // ✅ Endroid v4.x error correction uses classes like ErrorCorrectionLevelHigh
    // Make it robust in case of minor version differences.
    if (class_exists(\Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh::class)) {
        $qrCode->setErrorCorrectionLevel(new \Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh());
    } elseif (class_exists(\Endroid\QrCode\ErrorCorrectionLevel::class)) {
        // fallback for older versions (if ever used)
        $qrCode->setErrorCorrectionLevel(\Endroid\QrCode\ErrorCorrectionLevel::HIGH);
    }
    // else: no ECC setter available; still generates QR

    $writer = new PngWriter();
    $result = $writer->write($qrCode);

    header("Content-Type: image/png");
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    echo $result->getString();
    exit;

} catch (Throwable $e) {
    http_response_code(500);
    header("Content-Type: text/plain; charset=utf-8");
    echo "QR generation error: " . $e->getMessage();
    exit;
}
