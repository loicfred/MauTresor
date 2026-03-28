<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class Place_Image extends DBObject
{
    public int $PlaceID;
    public string $Name;
    public string $Image;
    public string $MimeType;
}