<?php
require_once "config/database.php";

echo "<h2>Admin Account Reset Tool</h2>";

try {
    // 1. Check if user exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();

    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);

    if ($admin) {
        // 2. Update existing admin
        $sql = "UPDATE users SET password = :pass, role = 'admin', status = 'active' WHERE username = 'admin'";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['pass' => $hashed_password]);
        echo "<p style='color: green;'>✅ Success: Admin password has been reset to <strong>admin123</strong></p>";
    } else {
        // 3. Create new admin
        $sql = "INSERT INTO users (username, email, password, role, status) VALUES ('admin', 'admin@staloysius.edu.in', :pass, 'admin', 'active')";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['pass' => $hashed_password]);
        echo "<p style='color: green;'>✅ Success: Admin account created with password <strong>admin123</strong></p>";
    }

    echo "<p><a href='login.php'>Go to Login Page</a></p>";
    
    // Optionally delete this file after use
    // unlink(__FILE__); 

} catch (PDOException $e) {
    echo "<p style='color: red;'>❌ Error: " . $e->getMessage() . "</p>";
}
?>
