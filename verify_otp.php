<?php
session_start();
require_once "config/database.php";

if (!isset($_SESSION["temp_user"])) {
    header("location: register.php");
    exit;
}

$err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $otp_input = trim($_POST["otp"]);
    $username = $_SESSION["temp_user"];
    
    $sql = "SELECT id, otp FROM users WHERE username = :username";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch();
        
        if ($user && $user['otp'] == $otp_input) {
            // Update status to active
            $update_sql = "UPDATE users SET status = 'active', otp = NULL WHERE id = :id";
            if ($update_stmt = $pdo->prepare($update_sql)) {
                $update_stmt->bindParam(":id", $user['id'], PDO::PARAM_INT);
                if ($update_stmt->execute()) {
                    unset($_SESSION["temp_user"]);
                    header("location: login.php?registered=success");
                    exit;
                }
            }
        } else {
            $err = "Invalid OTP. Please try again.";
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
                    <h2 class="fw-bold" style="color: var(--college-blue)">Verify OTP</h2>
                    <p class="text-muted">Enter the 6-digit code sent to your email.</p>
                </div>

                <div class="alert alert-info">
                    We've sent a 6-digit verification code to your registered email address. Please check your inbox and spam folder.
                </div>
                
                <?php if(!empty($err)): ?>
                    <div class="alert alert-danger"><?php echo $err; ?></div>
                <?php endif; ?>

                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                    <div class="mb-3 text-center">
                        <input type="text" name="otp" class="form-control form-control-lg text-center fw-bold letter-spacing-2" placeholder="000000" maxlength="6" required>
                    </div>
                    <div class="d-grid gap-2 mt-4">
                        <button type="submit" class="btn btn-college py-2 fw-bold">Verify & Activate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
.letter-spacing-2 {
    letter-spacing: 10px;
    font-size: 2rem;
}
</style>

<?php include "includes/footer.php"; ?>
