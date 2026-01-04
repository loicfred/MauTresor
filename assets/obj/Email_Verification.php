<?php
namespace assets\obj;

require_once __DIR__ . "/RememberMe.php";

class Email_Verification extends RememberMe {
    public int $UserID;
    public string $Type;
}
