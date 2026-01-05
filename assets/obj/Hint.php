<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class Hint extends DBObject
{
    public int $EventID;
    public string $Name;
    public string $Description;
    public string $QRCodeValue;
    public ?string $Thumbnail;


    public function getParticipantsWhoFound() {
        $hintFound = Hint_Found::getWhere("HintID = ?", $this->ID);
        $participants = [];
        foreach ($hintFound as $hint) $participants[] = $hint->ParticipantID;
        return $participants;
    }

}