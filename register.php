<?php
require_once "config/database.php";

$username = $email = $password = $confirm_password = "";
$err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Basic validation
    if (empty(trim($_POST["username"])) || empty(trim($_POST["email"])) || empty(trim($_POST["password"]))) {
        $err = "Please fill all fields.";
    } else {
        $username = trim($_POST["username"]);
        $email = trim($_POST["email"]);
        $password = trim($_POST["password"]);
        
        // Check if username exists
        $sql = "SELECT id FROM users WHERE username = :username OR email = :email";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":username", $username, PDO::PARAM_STR);
            $stmt->bindParam(":email", $email, PDO::PARAM_STR);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                $err = "This username or email is already taken.";
            } else {
                // Generate 6-digit OTP
                $otp = strval(rand(100000, 999999));
                
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert user
                $sql = "INSERT INTO users (username, email, password, otp, status) VALUES (:username, :email, :password, :otp, 'pending')";
                if ($stmt = $pdo->prepare($sql)) {
                    $stmt->bindParam(":username", $username, PDO::PARAM_STR);
                    $stmt->bindParam(":email", $email, PDO::PARAM_STR);
                    $stmt->bindParam(":password", $hashed_password, PDO::PARAM_STR);
                    $stmt->bindParam(":otp", $otp, PDO::PARAM_STR);
                    
                    if ($stmt->execute()) {
                        // Send Email OTP
                        require_once "config/mail.php";
                        $subject = "Verification Code - St. Aloysius Lost & Found";
                        $message = "
                            <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee;'>
                                <h2 style='color: #003366;'>Welcome to St. Aloysius Lost & Found</h2>
                                <p>Hello <strong>$username</strong>,</p>
                                <p>Your verification code for registration is:</p>
                                <div style='font-size: 24px; font-weight: bold; color: #003366; padding: 10px; background: #f4f4f4; text-align: center; border-radius: 5px; letter-spacing: 5px;'>
                                    $otp
                                </div>
                                <p>This code will expire shortly. Please do not share it with anyone.</p>
                                <hr style='border: 0; border-top: 1px solid #eee;'>
                                <p style='font-size: 12px; color: #777;'>St. Aloysius College Lost and Found Portal</p>
                            </div>
                        ";
                        
                        sendEmail($email, $subject, $message);

                        session_start();
                        $_SESSION["temp_user"] = $username;
                        header("location: verify_otp.php");
                        exit;
                    } else {
                        $err = "Something went wrong. Please try again later.";
                    }
                }
            }
        }
    }
}
?>

<?php include "includes/header.php"; ?>

<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card p-4 shadow-lg border-0">
                <div class="text-center mb-4">
                    <h2 class="fw-bold" style="color: var(--college-blue)">Create Account</h2>
                    <p class="text-muted">Join St. Aloysius Lost & Found Community</p>
                </div>
                
                <?php if(!empty($err)): ?>
                    <div class="alert alert-danger"><?php echo $err; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-user"></i></span>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-college py-2 fw-bold">Register Now</button>
                    </div>
                    <p class="text-center mt-3">Already have an account? <a href="login.php" class="text-decoration-none fw-bold">Login here</a></p>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
