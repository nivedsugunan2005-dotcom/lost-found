<?php
session_start();
require_once "../config/database.php";

// Admin check
if (!isset($_SESSION["loggedin"]) || $_SESSION["role"] !== 'admin') {
    header("location: ../login.php");
    exit;
}

// Fetch Stats
$total_items = $pdo->query("SELECT COUNT(*) FROM items")->fetchColumn();
$pending_items = $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'pending'")->fetchColumn();
$lost_count = $pdo->query("SELECT COUNT(*) FROM items WHERE type = 'lost'")->fetchColumn();
$found_count = $pdo->query("SELECT COUNT(*) FROM items WHERE type = 'found'")->fetchColumn();
$verified_users = $pdo->query("SELECT COUNT(*) FROM users WHERE status = 'active'")->fetchColumn();
$returned_count = $pdo->query("SELECT COUNT(*) FROM items WHERE status = 'returned'")->fetchColumn();

// Fetch Pending Approvals
$sql_pending = "SELECT i.*, u.username FROM items i JOIN users u ON i.user_id = u.id WHERE i.status = 'pending' ORDER BY i.created_at ASC";
$items_pending = $pdo->query($sql_pending)->fetchAll();

include "../includes/header.php";
?>

<div class="container-fluid py-4 px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold"><i class="fas fa-chart-pie me-2 text-primary"></i>Admin Analytics Dashboard</h2>
        <div>
            <button class="btn btn-outline-primary btn-sm me-2"><i class="fas fa-download me-1"></i> Export Report</button>
            <span class="text-muted">Last update: <?php echo date('H:i'); ?></span>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-between py-2">
                    <div>
                        <h6 class="text-muted mb-1">Total Items</h6>
                        <h3 class="fw-bold mb-0"><?php echo $total_items; ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 p-3 rounded"><i class="fas fa-box text-primary"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-between py-2">
                    <div>
                        <h6 class="text-muted mb-1">Pending Approval</h6>
                        <h3 class="fw-bold mb-0 text-warning"><?php echo $pending_items; ?></h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 p-3 rounded"><i class="fas fa-hourglass-half text-warning"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-between py-2">
                    <div>
                        <h6 class="text-muted mb-1">Items Found</h6>
                        <h3 class="fw-bold mb-0 text-success"><?php echo $found_count; ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 p-3 rounded"><i class="fas fa-search text-success"></i></div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex justify-content-between py-2">
                    <div>
                        <h6 class="text-muted mb-1">Active Users</h6>
                        <h3 class="fw-bold mb-0 text-info"><?php echo $verified_users; ?></h3>
                    </div>
                    <div class="bg-info bg-opacity-10 p-3 rounded"><i class="fas fa-users text-info"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Analytics Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h5 class="fw-bold mb-0">Overview: Lost vs Found</h5>
                </div>
                <div class="card-body">
                    <canvas id="itemsChart" style="max-height: 400px;"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Pending Items -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0">Pending Approvals</h5>
                    <a href="manage_items.php" class="small text-decoration-none">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php if(count($items_pending) > 0): ?>
                            <?php foreach(array_slice($items_pending, 0, 5) as $item): ?>
                                <div class="list-group-item py-3">
                                    <div class="d-flex justify-content-between">
                                        <h6 class="fw-bold mb-1"><?php echo htmlspecialchars($item['title']); ?></h6>
                                        <span class="badge <?php echo $item['type'] == 'lost' ? 'bg-danger' : 'bg-success'; ?>"><?php echo ucfirst($item['type']); ?></span>
                                    </div>
                                    <p class="text-muted small mb-2"><i class="fas fa-user me-1"></i> <?php echo $item['username']; ?></p>
                                    <div class="btn-group btn-group-sm w-100 mt-2">
                                        <a href="manage_items.php?action=approve&id=<?php echo $item['id']; ?>" class="btn btn-outline-success">Approve</a>
                                        <a href="manage_items.php?action=reject&id=<?php echo $item['id']; ?>" class="btn btn-outline-danger">Reject</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="p-5 text-center text-muted">No pending items.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('itemsChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Lost Items', 'Found Items', 'Returned Items', 'Total Reports'],
            datasets: [{
                label: 'Statistics',
                data: [<?php echo $lost_count; ?>, <?php echo $found_count; ?>, <?php echo $returned_count; ?>, <?php echo $total_items; ?>],
                backgroundColor: [
                    'rgba(220, 53, 69, 0.7)',
                    'rgba(25, 135, 84, 0.7)',
                    'rgba(13, 110, 253, 0.7)',
                    'rgba(0, 51, 102, 0.7)'
                ],
                borderColor: [
                    '#dc3545',
                    '#198754',
                    '#0d6efd',
                    '#003366'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
});
</script>

<?php include "../includes/footer.php"; ?>
