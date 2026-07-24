<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    header("location: ../login.php");
    exit;
}

// Handle Actions (Approve/Reject/Resolve/Delete)
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $action = $_GET['action'];
    
    if ($action == 'delete') {
        $sql = "DELETE FROM items WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        if ($stmt->execute(['id' => $id])) {
            $_SESSION['message'] = "Item successfully deleted.";
        }
    } else {
        $status = 'pending';
        if ($action == 'approve') $status = 'approved';
        if ($action == 'reject') $status = 'rejected';
        if ($action == 'resolve') $status = 'returned';
        
        $sql = "UPDATE items SET status = :status WHERE id = :id";
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":status", $status, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id, PDO::PARAM_INT);
            if ($stmt->execute()) {
                // Trigger Notification for User
                $item_sql = "SELECT i.user_id, i.title, u.email FROM items i JOIN users u ON i.user_id = u.id WHERE i.id = :id";
                $item_stmt = $pdo->prepare($item_sql);
                $item_stmt->bindParam(":id", $id);
                $item_stmt->execute();
                $item = $item_stmt->fetch();
                
                if($item) {
                    // In-app notification
                    $msg = "Your post for '" . $item['title'] . "' has been " . $status . " by the administrator.";
                    $notif_sql = "INSERT INTO notifications (user_id, message) VALUES (:uid, :msg)";
                    $notif_stmt = $pdo->prepare($notif_sql);
                    $notif_stmt->execute(['uid' => $item['user_id'], 'msg' => $msg]);

                    // Email notification
                    require_once "../config/mail.php";
                    $subject = "Update: Your post '" . $item['title'] . "' has been " . $status;
                    $email_body = "
                        <div style='font-family: Arial, sans-serif; padding: 20px; border: 1px solid #eee;'>
                            <h2 style='color: #003366;'>Post Update</h2>
                            <p>Hello,</p>
                            <p>Your post for the item <strong>" . $item['title'] . "</strong> has been <strong>" . $status . "</strong> by the college administrator.</p>
                            " . ($status == 'approved' ? "<p>It is now visible to all students on the platform.</p>" : "<p>Status updated to: <strong>" . $status . "</strong></p>") . "
                            <hr style='border: 0; border-top: 1px solid #eee;'>
                            <p style='font-size: 12px; color: #777;'>St. Aloysius College Lost and Found Portal</p>
                        </div>
                    ";
                    sendEmail($item['email'], $subject, $email_body);
                }
                
                $_SESSION['message'] = "Item successfully " . $status;
            }
        }
    }
    header("location: manage_items.php");
    exit;
}

// Fetch All Items
$sql = "SELECT i.*, u.username FROM items i JOIN users u ON i.user_id = u.id ORDER BY i.created_at DESC";
$items = $pdo->query($sql)->fetchAll();

include "../includes/header.php";
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Manage Reported Items</h2>
        <a href="dashboard.php" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i> Dashboard</a>
    </div>

    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3 text-uppercase small fw-bold">ID</th>
                            <th class="text-uppercase small fw-bold">Requester</th>
                            <th class="text-uppercase small fw-bold">Item Details</th>
                            <th class="text-uppercase small fw-bold">Type</th>
                            <th class="text-uppercase small fw-bold">Status</th>
                            <th class="text-uppercase small fw-bold">Date</th>
                            <th class="text-end pe-3 text-uppercase small fw-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($items as $item): ?>
                            <tr>
                                <td class="ps-3"><?php echo $item['id']; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($item['username']); ?></div>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($item['category']); ?> | <?php echo htmlspecialchars($item['location']); ?></div>
                                </td>
                                <td>
                                    <span class="badge <?php echo $item['type'] == 'lost' ? 'bg-danger' : 'bg-success'; ?>">
                                        <?php echo ucfirst($item['type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-<?php echo $item['status']; ?>">
                                        <?php echo ucfirst($item['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                <td class="text-end pe-3">
                                    <?php if($item['status'] == 'pending'): ?>
                                        <a href="?action=approve&id=<?php echo $item['id']; ?>" class="btn btn-success btn-sm" title="Approve"><i class="fas fa-check"></i></a>
                                        <a href="?action=reject&id=<?php echo $item['id']; ?>" class="btn btn-danger btn-sm" title="Reject"><i class="fas fa-times"></i></a>
                                    <?php elseif($item['status'] == 'approved'): ?>
                                        <a href="?action=resolve&id=<?php echo $item['id']; ?>" class="btn btn-outline-success btn-sm" title="Mark as Resolved/Returned"><i class="fas fa-handshake"></i> Resolve</a>
                                    <?php endif; ?>
                                    
                                    <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#viewModal<?php echo $item['id']; ?>" title="View Details"><i class="fas fa-eye"></i></button>
                                    <a href="?action=delete&id=<?php echo $item['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Permanently delete this item?')" title="Delete"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                            
                            <!-- Modal -->
                            <div class="modal fade" id="viewModal<?php echo $item['id']; ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Item Details #<?php echo $item['id']; ?></h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <?php if($item['image_path']): ?>
                                                <img src="../assets/uploads/<?php echo $item['image_path']; ?>" class="img-fluid rounded mb-3">
                                            <?php endif; ?>
                                            <p><strong>Description:</strong><br><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                                            <p><strong>Contact Email:</strong> user_email_placeholder@staloysius.edu.in</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
