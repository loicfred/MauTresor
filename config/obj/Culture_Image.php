<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class Culture_Image extends DBObject
{
    public int $CultureID;
    public string $Name;
    public string $Image;
    public string $MimeType;
}