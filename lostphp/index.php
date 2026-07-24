<?php
require_once "config/database.php";
include "includes/header.php";

// Fetch approved items
$sql = "SELECT i.*, u.username FROM items i JOIN users u ON i.user_id = u.id WHERE i.status = 'approved' ORDER BY i.created_at DESC";
$stmt = $pdo->query($sql);
$items = $stmt->fetchAll();
?>

<div class="welcome-section text-center">
    <div class="container">
        <h1 class="display-4 fw-bold">Lost Something? Found Something?</h1>
        <p class="lead mb-4">The official St Aloysius College Lost and Found portal to help you recover your belongings.</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="user/post_item.php?type=lost" class="btn btn-light btn-lg px-4 shadow-sm fw-bold">I Lost Something</a>
            <a href="user/post_item.php?type=found" class="btn btn-outline-light btn-lg px-4 fw-bold">I Found Something</a>
        </div>
    </div>
</div>

<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Recent Items</h2>
        <div class="btn-group shadow-sm">
            <button class="btn btn-white active border">All</button>
            <button class="btn btn-white border">Lost</button>
            <button class="btn btn-white border">Found</button>
        </div>
    </div>

    <div class="row g-4">
        <?php if (count($items) > 0): ?>
            <?php foreach ($items as $item): ?>
                <div class="col-md-4 col-lg-3">
                    <div class="card h-100">
                        <?php if ($item['image_path']): ?>
                            <img src="assets/uploads/<?php echo htmlspecialchars($item['image_path']); ?>" class="card-img-top" alt="Item Image" style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <i class="fas fa-image fa-3x text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge <?php echo $item['type'] == 'lost' ? 'bg-danger' : 'bg-success'; ?> text-uppercase">
                                    <?php echo $item['type']; ?>
                                </span>
                                <small class="text-muted"><i class="fas fa-calendar-alt me-1"></i> <?php echo date('M d, Y', strtotime($item['date_lost_found'])); ?></small>
                            </div>
                            <h5 class="card-title fw-bold text-truncate"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <p class="card-text text-muted small"><i class="fas fa-map-marker-alt me-1"></i> <?php echo htmlspecialchars($item['location']); ?></p>
                            <div class="d-grid mt-3">
                                <?php if($item['type'] == 'found'): ?>
                                    <a href="user/claim_item.php?id=<?php echo $item['id']; ?>" class="btn btn-college btn-sm">Claim Item</a>
                                <?php else: ?>
                                    <a href="user/contact_owner.php?id=<?php echo $item['id']; ?>" class="btn btn-outline-primary btn-sm">I Found This</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-0 py-3">
                            <small class="text-muted">Posted by: <strong><?php echo htmlspecialchars($item['username']); ?></strong></small>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center py-5">
                <i class="fas fa-search fa-4x text-muted mb-3"></i>
                <h4 class="text-muted">No items found matching your criteria.</h4>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="bg-white py-5">
    <div class="container text-center">
        <h2 class="fw-bold mb-4">How it Works</h2>
        <div class="row">
            <div class="col-md-4 px-4">
                <div class="mb-3 text-primary"><i class="fas fa-edit fa-3x"></i></div>
                <h4>1. Report</h4>
                <p class="text-muted">Fill out a simple form to report a lost or found item with details and location.</p>
            </div>
            <div class="col-md-4 px-4 border-start border-end">
                <div class="mb-3 text-primary"><i class="fas fa-check-circle fa-3x"></i></div>
                <h4>2. Verify</h4>
                <p class="text-muted">Our administrators review and approve the post to ensure authenticity and safety.</p>
            </div>
            <div class="col-md-4 px-4">
                <div class="mb-3 text-primary"><i class="fas fa-hand-holding-heart fa-3x"></i></div>
                <h4>3. Recover</h4>
                <p class="text-muted">The rightful owner can claim their item after providing proof of ownership.</p>
            </div>
        </div>
    </div>
</div>

<?php include "includes/footer.php"; ?>
