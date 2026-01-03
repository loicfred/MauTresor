<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['Email'] ?? '';
    $message = $_POST['Message'] ?? '';
    $gender = $_POST['Gender'] ?? '';
    // Process / validate / update database
}