<?php
session_start();
require_once "config/database.php";

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    if ($_SESSION["role"] === 'admin') {
        header("location: admin/dashboard.php");
    } else {
        header("location: user/dashboard.php");
    }
    exit;
}

$err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    $sql = "SELECT id, username, password, role, status FROM users WHERE username = :username";
    if ($stmt = $pdo->prepare($sql)) {
        $stmt->bindParam(":username", $username, PDO::PARAM_STR);
        if ($stmt->execute()) {
            if ($stmt->rowCount() == 1) {
                if ($row = $stmt->fetch()) {
                    if ($row['status'] !== 'active') {
                        $err = "Your account is not activated. Please verify your OTP.";
                    } elseif (password_verify($password, $row['password'])) {
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $row['id'];
                        $_SESSION["username"] = $row['username'];
                        $_SESSION["role"] = $row['role'];

                        if ($row['role'] === 'admin') {
                            header("location: admin/dashboard.php");
                        } else {
                            header("location: user/dashboard.php");
                        }
                    } else {
                        $err = "Invalid username or password.";
                    }
                }
            } else {
                $err = "Invalid username or password.";
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
                    <h2 class="fw-bold" style="color: var(--college-blue)">Student/Staff Login</h2>
                    <p class="text-muted">Access St. Aloysius Lost & Found Portal</p>
                </div>
                
                <?php if(isset($_GET['registered'])): ?>
                    <div class="alert alert-success">Registration successful! Please login.</div>
                <?php endif; ?>

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
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-college py-2 fw-bold">Login</button>
                    </div>
                    <p class="text-center mt-3">Don't have an account? <a href="register.php" class="text-decoration-none fw-bold">Register here</a></p>
                </form>
            </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
