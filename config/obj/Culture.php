<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";
require_once __DIR__ . "/Culture_Image.php";

class Culture extends DBObject
{
    public string $Name;
    public string $Description;
    public int $ThumbnailID;

    public function getImages() {
        return Culture_Image::selectAllWhere("ID", "CultureID = ?", $this->ID);
    }
}