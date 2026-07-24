<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["loggedin"])) {
    header("location: ../login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("location: ../index.php");
    exit;
}

$id = $_GET['id'];
$user_id = $_SESSION["id"];

// Fetch item and owner info
$stmt = $pdo->prepare("SELECT i.*, u.username, u.email FROM items i JOIN users u ON i.user_id = u.id WHERE i.id = :id");
$stmt->execute(['id' => $id]);
$item = $stmt->fetch();

if (!$item) die("Item not found.");

$success = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $msg_text = trim($_POST["message"]);
    
    // Create notification for owner
    $notif_msg = "User '" . $_SESSION['username'] . "' found your item: '" . $item['title'] . "'. Message: " . $msg_text;
    $notif_sql = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
    $pdo->prepare($notif_sql)->execute([$item['user_id'], $notif_msg]);
    
    // Send Email to owner
    require_once "../config/mail.php";
    $subject = "Great News! Someone found your " . $item['title'];
    $email_body = "
        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee;'>
            <h2 style='color: #003366;'>Good News!</h2>
            <p>Hello,</p>
            <p>A user named <strong>" . $_SESSION['username'] . "</strong> has found the item you reported as lost: <strong>" . $item['title'] . "</strong>.</p>
            <div style='background: #f9f9f9; padding: 15px; border-left: 4px solid #003366; margin: 20px 0;'>
                <strong>Message from Finder:</strong><br>
                " . nl2br(htmlspecialchars($msg_text)) . "
            </div>
            <p>Please log in to the St. Aloysius Lost & Found portal to coordinate with the finder.</p>
            <hr style='border: 0; border-top: 1px solid #eee;'>
            <p style='font-size: 12px; color: #777;'>St. Aloysius College Lost and Found Portal</p>
        </div>
    ";
    
    sendEmail($item['email'], $subject, $email_body);

    $success = "Your message has been sent to the owner via the portal and email!";
}

include "../includes/header.php";
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <h4 class="fw-bold mb-0">Contact Owner</h4>
                </div>
                <div class="card-body">
                    <div class="alert alert-info py-2">
                        You are sending a message to the owner of: <br>
                        <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                    </div>

                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <div class="text-center"><a href="../index.php" class="btn btn-college">Back to Home</a></div>
                    <?php else: ?>
                        <form action="" method="post">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Your Message</label>
                                <textarea name="message" class="form-control" rows="5" placeholder="Let the owner know where you found it or where they can collect it..." required></textarea>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-college py-3 fw-bold">Send Message</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
