<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

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
    $mail->setFrom("mautresor.support@gmail.com", "MauTresor");
    return $mail;
}
function sendVerificationEmail($to, $link) {
    $mail = getMailer();
    $mail->addAddress($to);
    $mail->Subject = "Verify your MauTresor account";
    $mail->Body = "
        Click the link below to verify your account:<br><br>
        <a href='https://mautresor.mu/accounts/verification?token=$link'>https://mautresor.mu/accounts/verification?token=$link</a>
    ";
    $mail->send();
}

function sendNewPassword($to, $link) {
    $mail = new PHPMailer(true);
    $mail->addAddress($to);
    $mail->Subject = "Reset your MauTresor password";
    $mail->Body = "
            Click the link below to verify your account:<br><br>
            <a href='https://mautresor.mu/accounts/newpassword?token=$link'>$link</a>
        ";

    $mail->send();
}
