<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

$user_id = $_SESSION["id"];

// Handle Delete Action
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $item_id = $_GET['delete'];
    // Verify user owns the item before deleting
    $check_sql = "DELETE FROM items WHERE id = :id AND user_id = :uid";
    $check_stmt = $pdo->prepare($check_sql);
    if ($check_stmt->execute(['id' => $item_id, 'uid' => $user_id])) {
        $_SESSION['message'] = "Item successfully deleted.";
        header("location: dashboard.php");
        exit;
    }
}

// Fetch user's posts
$sql = "SELECT * FROM items WHERE user_id = :user_id ORDER BY created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
$stmt->execute();
$my_items = $stmt->fetchAll();

// Statistics
$total_posts = count($my_items);
$pending_apr = 0;
$resolved_count = 0;
foreach($my_items as $item) {
    if($item['status'] == 'pending') $pending_apr++;
    if(in_array($item['status'], ['claimed', 'returned'])) $resolved_count++;
}

include "../includes/header.php";
?>

<div class="container py-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold">Welcome back, <?php echo htmlspecialchars($_SESSION["username"]); ?>!</h2>
            <p class="text-muted">Manage your reported items and tracking claims.</p>
        </div>
        <div class="col-md-4 text-md-end">
            <a href="post_item.php" class="btn btn-college px-4 shadow-sm"><i class="fas fa-plus me-2"></i> Report New Item</a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row g-3 mb-5">
        <div class="col-md-4">
            <div class="card p-3 bg-white border-start border-primary border-5 h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fas fa-file-alt text-primary fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Total Submissions</h6>
                        <h3 class="fw-bold mb-0"><?php echo $total_posts; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 bg-white border-start border-warning border-5 h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fas fa-clock text-warning fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Pending Approval</h6>
                        <h3 class="fw-bold mb-0"><?php echo $pending_apr; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card p-3 bg-white border-start border-success border-5 h-100">
                <div class="d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                        <i class="fas fa-check-circle text-success fa-lg"></i>
                    </div>
                    <div>
                        <h6 class="text-muted mb-0">Resolved Items</h6>
                        <h3 class="fw-bold mb-0"><?php echo $resolved_count; ?></h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <div class="card-header bg-white py-3 border-0">
            <h5 class="fw-bold mb-0">Your Submissions</h5>
        </div>
        <?php if(isset($_SESSION['message'])): ?>
            <div class="alert alert-success mx-3"><?php echo $_SESSION['message']; unset($_SESSION['message']); ?></div>
        <?php endif; ?>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3">Item</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Date Reported</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($my_items) > 0): ?>
                            <?php foreach ($my_items as $item): ?>
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            <?php if ($item['image_path']): ?>
                                                <img src="../assets/uploads/<?php echo $item['image_path']; ?>" class="rounded me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <?php else: ?>
                                                <div class="bg-light rounded d-flex align-items-center justify-content-center me-2" style="width: 40px; height: 40px;">
                                                    <i class="fas fa-image text-muted"></i>
                                                </div>
                                            <?php endif; ?>
                                            <span class="fw-bold"><?php echo htmlspecialchars($item['title']); ?></span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $item['type'] == 'lost' ? 'bg-danger' : 'bg-success'; ?>">
                                            <?php echo ucfirst($item['type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($item['category']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($item['created_at'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $item['status']; ?>">
                                            <?php echo ucfirst($item['status']); ?>
                                        </span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <a href="../index.php" class="btn btn-outline-secondary btn-sm" title="View on Site"><i class="fas fa-eye"></i></a>
                                        <a href="?delete=<?php echo $item['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Are you sure you want to delete this report?')" title="Delete"><i class="fas fa-trash"></i></a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <p class="text-muted mb-0">You haven't reported any items yet.</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
