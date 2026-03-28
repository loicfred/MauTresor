<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class Hint extends DBObject
{
    public int $EventID;
    public int $PlaceID;
    public string $Name;
    public string $Description;
    public ?string $Thumbnail;

    public function getParticipantsWhoFound() {
        $hintFound = Hint_Found::getWhere("HintID = ?", $this->ID);
        $participants = [];
        foreach ($hintFound as $hint) $participants[] = $hint->ParticipantID;
        return $participants;
    }

    public static function getByPlace(string $placeId) {
        return Hint::getWhere("PlaceID = ?", $placeId);
    }

}