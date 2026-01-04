<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class Place extends DBObject
{
    public string $Name;
    public string $Description;
    public string $Latitude;
    public string $Longitude;
    public string $QRCode;
    public string $Category;

}