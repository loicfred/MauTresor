<?php
include __DIR__ . '/../config/auth.php';

require_once __DIR__ . "/../config/mailer.php";

require_once __DIR__ . '/../assets/obj/User.php';
require_once __DIR__ . '/../assets/obj/Email_Verification.php';

use assets\obj\Email_Verification;
use assets\obj\User;

?>

<!DOCTYPE html>
<html xmlns:th="http://www.thymeleaf.org">
<head>
  <title>Sign Up</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center" style="height: 100vh;">
<div class="container text-center">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow p-4">
                <h3 class="mb-3">Create an Account</h3>
                <?php
                if ($_SERVER["REQUEST_METHOD"] === "POST") {

                    if (User::getByEmail(trim($_POST["Email"]))) {
                        echo "<div class='alert alert-danger'>Email already registered.</div>";
                    }
                    else if (validatePassword($_POST["Password"]) !== true) {
                        echo "<div class='alert alert-danger'>Password should be of minimum 8 of length and contain at least 1 digit, symbol, uppercase & lowercase character.</div>";
                    }
                    else {
                        $user = new User();
                        $user->Email = trim($_POST["Email"]);
                        $user->Password = password_hash($_POST["Password"], PASSWORD_DEFAULT);
                        $user->FirstName = trim($_POST["FirstName"]);
                        $user->LastName = trim($_POST["LastName"]);
                        $user->Gender = trim($_POST["Gender"]);
                        $user->Role = 'USER';
                        $user->DateOfBirth = trim($_POST["DateOfBirth"]);
                        $user->CreatedAt = date("Y-m-d H:i:s");
                        $user->UpdatedAt = date("Y-m-d H:i:s");

                        // Insert user
                        if ($user->Write()->ID === 0) {
                            echo "<div class='alert alert-danger'>An error occurred.</div>";
                        } else {
                            $email_verification = new Email_Verification();
                            $email_verification->UserID = $user->ID;
                            $email_verification->Token = bin2hex(random_bytes(32));
                            $email_verification->Type = 'REGISTRATION';
                            $email_verification->ExpiryDate = date('Y-m-d H:i:s', strtotime('+10 minutes'));

                            if ($email_verification->Write()->ID !== 0) {
                                $verifyLink = "http://localhost:63342/LocalGuideTreasureHunt/accounts/verification?token=$email_verification->Token";
                                sendVerificationEmail($user->Email, $verifyLink);
                                header("Location: login?checkEmail");
                            }
                        }
                    }
                }
                ?>

                <form method="post" action="signup">
                    <div class="mb-3">
                        <input type="email" name="Email" maxlength="64" class="form-control" placeholder="Email" required>
                    </div>

                    <div class="mb-3">
                        <input type="password" name="Password" maxlength="16" class="form-control" placeholder="Password" required>
                    </div>

                    <div class="mb-3">
                        <input type="text" name="FirstName" maxlength="64" class="form-control" placeholder="First Name" required>
                    </div>

                    <div class="mb-3">
                        <input type="text" name="LastName" maxlength="64" class="form-control" placeholder="Last Name" required>
                    </div>

                    <div class="mb-3">
                        <select name="Gender" class="form-select" required>
                            <option value="" disabled selected>Select Gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <input type="date" name="DateOfBirth" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-danger w-100">Sign Up</button>
                </form>
                <p class="mt-3">Already have an account? <a href="/login">Log in</a></p>
            </div>
        </div>
    </div>
</div>
</body>
</html>
<?php
function validatePassword($password): bool {
    return !(strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[\W_]/', $password));
}
?>
