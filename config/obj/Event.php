<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";
require_once __DIR__ . "/Hint.php";
require_once __DIR__ . "/Event_Image.php";
require_once __DIR__ . "/Event_Participant.php";

use assets\obj\Hint;
use assets\obj\Event_Image;
use assets\obj\Event_Participant;

class Event extends DBObject
{
    public string $Name;
    public string $Description;
    public string $CreatedAt;
    public ?string $StartAt = null;
    public ?string $EndAt = null;
    public ?int $ThumbnailID = null;

    public function getImages() {
        return Event_Image::selectAllWhere("ID", "EventID = ?", $this->ID);
    }

    public function getParticipants() {
        return Event_Participant::getAllWhere("EventID = ?", $this->ID);
    }

    public function getHints() {
        return Hint::getAllWhere("EventID = ?", $this->ID);
    }

}