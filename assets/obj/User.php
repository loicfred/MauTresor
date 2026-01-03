<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class User extends DBObject {
    public int $ID;
    public string $Email;
    public string $Password;
    public string $FirstName;
    public string $LastName;
    public string $Role;
    public string $Gender;
    public ?string $AccountProvider;
    public string $DateOfBirth;
    public string $CreatedAt;
    public string $UpdatedAt;
    public ?string $Image;
    public bool $Enabled;
    public bool $Verified;
}
