<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class Event_Participant extends DBObject
{
    public int $UserID;
    public int $EventID;

    public static function getByUserAndEvent(string $userId, string $eventId) {
        return Event_Participant::getWhere("UserID = ? AND EventID = ?", $userId, $eventId);
    }

}