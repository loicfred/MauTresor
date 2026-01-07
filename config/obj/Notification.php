<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class Notification extends DBObject {
    public int $UserID;
    public string $Title;
    public string $Message;
    public string $CreatedAt;
    public bool $isRead = false;

    public static function getOfUser(string $userid) {
        return self::getAllWhere("UserID = ?", $userid);
    }
}
