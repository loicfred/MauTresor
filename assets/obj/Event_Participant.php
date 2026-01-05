<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class Event_Participant extends DBObject
{
    public int $UserID;
    public int $EventID;

}