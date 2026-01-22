<?php
use PHPMailer\PHPMailer\PHPMailer;

require_once __DIR__ . "/../vendor/autoload.php";

function getMailer() {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = "smtp.gmail.com";
    $mail->SMTPAuth = true;
    $mail->Username = "maudonate@gmail.com";
    $mail->Password = "ziga boka rjqi ssvt";
    $mail->SMTPSecure = "tls";
    $mail->Port = 587;
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


function getOrigin() {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    echo $scheme . '://' . $host;
}