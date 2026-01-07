<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class Event_Image extends DBObject
{
    public int $EventID;
    public string $Name;
    public string $Image;
    public string $MimeType;
}