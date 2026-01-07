<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";
require_once __DIR__ . "/Place_Image.php";

class Place extends DBObject
{
    public string $Name;
    public string $Description;
    public float $Latitude = 0;
    public float $Longitude = 0;
    public string $QRCode;
    public string $Category;
    public int $ThumbnailID;

    public function getImages() {
        return Place_Image::selectAllWhere("ID", "PlaceID = ?", $this->ID);
    }
    public static function getLocalPlaces() {
        return Place::getAllWhere("Category = ?", "Local");
    }
    public static function getWorldPlaces()  {
        return Place::getAllWhere("Category = ?", "World");
    }

    public static function getByQRCode(string $code) {
        return Place::getWhere("QRCode = ?", $code);
    }
}