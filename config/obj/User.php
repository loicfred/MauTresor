<?php
namespace assets\obj;

require_once __DIR__ . "/DBObject.php";

class User extends DBObject {
    public string $Email;
    public string $Password;
    public string $FirstName;
    public string $LastName;
    public string $Role;
    public string $Gender;
    public ?string $AccountProvider = null;
    public string $DateOfBirth;
    public string $CreatedAt;
    public string $UpdatedAt;
    public bool $Enabled = false;
    public bool $Verified = false;
    public ?string $Image = null;
    public ?string $MimeType = null;

    public static function getByEmail(string $email) {
        return self::getWhere("Email = ?", $email);
    }
    public static function getByAuthentication(string $email, string $password) {
        $user = self::getByEmail($email);
        if($user == null) return null;
        if (password_verify($password, $user->Password)) return $user;
        return null;
    }



    public static function validatePassword($password): bool {
        return !(strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[\W_]/', $password));
    }
}
