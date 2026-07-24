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

$item_id = $_GET['id'];
$user_id = $_SESSION["id"];
$err = "";
$success = "";

// Fetch item details
$stmt = $pdo->prepare("SELECT * FROM items WHERE id = :id AND status = 'approved'");
$stmt->execute(['id' => $item_id]);
$item = $stmt->fetch();

if (!$item) {
    die("Item not found or not available for claim.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $proof = trim($_POST["proof"]);
    
    // Check if already claimed
    $check_stmt = $pdo->prepare("SELECT id FROM claims WHERE item_id = :iid AND user_id = :uid");
    $check_stmt->execute(['iid' => $item_id, 'uid' => $user_id]);
    
    if ($check_stmt->rowCount() > 0) {
        $err = "You have already submitted a claim for this item.";
    } else {
        $sql = "INSERT INTO claims (item_id, user_id, proof_description) VALUES (:iid, :uid, :proof)";
        if ($stmt_claim = $pdo->prepare($sql)) {
            if ($stmt_claim->execute(['iid' => $item_id, 'uid' => $user_id, 'proof' => $proof])) {
                $success = "Your claim has been submitted. Please wait for administrator verification.";
            } else {
                $err = "Could not submit claim. Try again.";
            }
        }
    }
}

include "../includes/header.php";
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <h4 class="fw-bold mb-0">Claim Found Item</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <?php if ($item['image_path']): ?>
                                <img src="../assets/uploads/<?php echo $item['image_path']; ?>" class="img-fluid rounded shadow-sm">
                            <?php else: ?>
                                <div class="bg-light p-4 text-center rounded"><i class="fas fa-image fa-3x text-muted"></i></div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <h5 class="fw-bold text-primary"><?php echo htmlspecialchars($item['title']); ?></h5>
                            <p class="mb-1"><strong>Location:</strong> <?php echo htmlspecialchars($item['location']); ?></p>
                            <p class="mb-1"><strong>Date Found:</strong> <?php echo date('M d, Y', strtotime($item['date_lost_found'])); ?></p>
                            <p class="text-muted small"><?php echo nl2br(htmlspecialchars($item['description'])); ?></p>
                        </div>
                    </div>

                    <?php if($success): ?>
                        <div class="alert alert-success"><?php echo $success; ?></div>
                        <a href="dashboard.php" class="btn btn-college">Go to My Claims</a>
                    <?php else: ?>
                        <?php if($err): ?> <div class="alert alert-danger"><?php echo $err; ?></div> <?php endif; ?>
                        
                        <form action="" method="post">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Proof of Ownership</label>
                                <textarea name="proof" class="form-control" rows="5" placeholder="Please describe identifying marks, contents, or any details that only the owner would know." required></textarea>
                            </div>
                            <div class="d-grid shadow-sm">
                                <button type="submit" class="btn btn-college py-3 fw-bold">Submit Claim Request</button>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
