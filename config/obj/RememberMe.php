<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class RememberMe extends DBObject {
    public string $Token;
    public string $ExpiryDate;

    public static function getByToken(string $token) {
        return self::getWhere("Token = ?", $token);
    }

    public function isExpired(): bool {
        return strtotime($this->ExpiryDate) < time();
    }
}
