<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class Hint_Found extends DBObject
{
    public int $ParticipantID;
    public int $HintID;

}