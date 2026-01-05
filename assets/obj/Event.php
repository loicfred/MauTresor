<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class Event extends DBObject
{
    public string $Name;
    public string $Description;
    public string $CreatedAt;
    public ?string $StartAt;
    public ?string $EndAt;
    public ?int $ThumbnailID;

    public function getImages() {
        return Event_Image::selectAllWhere("ID", "EventID = ?", $this->ID);
    }

    public function getParticipants() {
        return Event_Participant::getWhere("EventID = ?", $this->ID);
    }

    public function getHints() {
        return Hint::getWhere("EventID = ?", $this->ID);
    }

}