<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../../vendor/autoload.php";

function sendVerificationEmail($to, $link) {
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = "smtp.gmail.com";
        $mail->SMTPAuth = true;
        $mail->Username = "maudonate@gmail.com";
        $mail->Password = "ziga boka rjqi ssvt";
        $mail->SMTPSecure = "tls";
        $mail->Port = 587;

        $mail->setFrom("localtreasure.hunt@gmail.com", "MauTresor");
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = "Verify Your Account";
        $mail->Body = "
            Click the link below to verify your account:<br><br>
            <a href='$link'>$link</a>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
    }
}
