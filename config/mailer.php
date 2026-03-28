<?php
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . "/../vendor/autoload.php";

function getMailer() {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $_ENV['SMTP_HOST'];
    $mail->SMTPAuth = $_ENV['SMTP_AUTH'];
    $mail->Username = $_ENV['SMTP_USER'];
    $mail->Password = $_ENV['SMTP_PASS'];;
    $mail->SMTPSecure = $_ENV['SMTP_SECURE'];
    $mail->Port = $_ENV['SMTP_PORT'];
    $mail->isHTML();
    $mail->setFrom("mautresor.support@gmail.com", "MauTresor");
    return $mail;
}
function sendVerificationEmail($to, $link) {
    $link = getOrigin() . "/accounts/verification?token=$link";
    $mail = getMailer();
    $mail->addAddress($to);
    $mail->Subject = "Verify your MauTresor account";
    $mail->Body = "
        Click the link below to verify your account:<br><br>
            <a href='$link'>$link</a>
    ";
    $mail->send();
}

function sendNewPassword($to, $link) {
    $link = getOrigin() . "/accounts/newpassword?token=$link";
    $mail = getMailer();
    $mail->addAddress($to);
    $mail->Subject = "Reset your MauTresor password";
    $mail->Body = "
            Click the link below to reset your password:<br><br>
            <a href='$link'>$link</a>
            If you didn't request to edit your password, please ignore this email.
        ";
    $mail->send();
}

function sendEmail($to, $subject, $content) {
    $mail = getMailer();
    $mail->addAddress($to);
    $mail->Subject = $subject;
    $mail->Body = $content;
    $mail->send();
}

function getOrigin(): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    return $scheme . '://' . $host;
}