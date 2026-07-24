<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    header("location: ../login.php");
    exit;
}

// Handle Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $claim_id = $_GET['id'];
    $action = $_GET['action'];
    $status = $action == 'approve' ? 'approved' : 'rejected';
    
    // Update Claim
    $sql = "UPDATE claims SET status = :status WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute(['status' => $status, 'id' => $claim_id])) {
        
        // If approved, update item status to 'claimed' or 'returned'
        if ($status == 'approved') {
            $claim_data = $pdo->prepare("SELECT c.item_id, c.user_id, u.email, i.title FROM claims c JOIN users u ON c.user_id = u.id JOIN items i ON c.item_id = i.id WHERE c.id = :id");
            $claim_data->execute(['id' => $claim_id]);
            $row = $claim_data->fetch();
            
            if ($row) {
                // Mark item as claimed
                $pdo->prepare("UPDATE items SET status = 'claimed' WHERE id = :iid")->execute(['iid' => $row['item_id']]);
                
                // In-app Notification
                $msg = "Your claim request for '" . $row['title'] . "' has been APPROVED! You can now collect your item from the college office.";
                $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")->execute([$row['user_id'], $msg]);

                // Email Notification
                require_once "../config/mail.php";
                $subject = "Claim Approved: Please collect your " . $row['title'];
                $email_body = "
                    <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee;'>
                        <h2 style='color: #003366;'>Claim Approved!</h2>
                        <p>Hello,</p>
                        <p>Good news! Your claim request for the item <strong>" . $row['title'] . "</strong> has been approved by the college administrator.</p>
                        <div style='background: #eef7ee; padding: 15px; border: 1px solid #c3e6cb; border-radius: 5px; margin: 20px 0;'>
                            <strong>Next Steps:</strong><br>
                            Please visit the college main office during working hours with your ID card to collect your item.
                        </div>
                        <hr style='border: 0; border-top: 1px solid #eee;'>
                        <p style='font-size: 12px; color: #777;'>St Aloysius College Lost and Found Portal</p>
                    </div>
                ";
                sendEmail($row['email'], $subject, $email_body);
            }
        }
        
        $_SESSION['message'] = "Claim has been " . $status;
    }
}

// Fetch All Claims
$sql = "SELECT c.*, u.username, i.title, i.image_path FROM claims c 
        JOIN users u ON c.user_id = u.id 
        JOIN items i ON c.item_id = i.id 
        ORDER BY c.created_at DESC";
$claims = $pdo->query($sql)->fetchAll();

include "../includes/header.php";
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Manage Claim Requests</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
    </div>

    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-4">
        <?php if(count($claims) > 0): ?>
            <?php foreach($claims as $claim): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <span class="fw-bold text-primary">Claim #<?php echo $claim['id']; ?></span>
                            <span class="badge badge-<?php echo $claim['status']; ?>"><?php echo ucfirst($claim['status']); ?></span>
                        </div>
                        <div class="card-body">
                            <h6 class="fw-bold mb-1">Claimant: <?php echo htmlspecialchars($claim['username']); ?></h6>
                            <p class="text-muted small">Target Item: <strong><?php echo htmlspecialchars($claim['title']); ?></strong></p>
                            
                            <hr>
                            <p class="mb-0 small fw-bold text-muted text-uppercase">Proof Summary:</p>
                            <p class="small"><?php echo nl2br(htmlspecialchars($claim['proof_description'])); ?></p>
                            
                            <?php if($claim['status'] == 'pending'): ?>
                                <div class="d-grid gap-2 mt-4">
                                    <a href="?action=approve&id=<?php echo $claim['id']; ?>" class="btn btn-success btn-sm"><i class="fas fa-check me-1"></i> Approve Claim</a>
                                    <a href="?action=reject&id=<?php echo $claim['id']; ?>" class="btn btn-outline-danger btn-sm"><i class="fas fa-times me-1"></i> Reject Claim</a>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer bg-light py-2 text-center">
                            <small class="text-muted">Submitted on: <?php echo date('M d, H:i', strtotime($claim['created_at'])); ?></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-hand-holding fa-3x text-muted mb-3"></i>
                <p class="text-muted">No claim requests found.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
