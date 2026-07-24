<?php
session_start();
require_once "../config/database.php";

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: ../login.php");
    exit;
}

$err = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $type = $_POST["type"];
    $title = trim($_POST["title"]);
    $category = $_POST["category"];
    $location = trim($_POST["location"]);
    $date = $_POST["date"];
    $description = trim($_POST["description"]);
    $user_id = $_SESSION["id"];
    
    // Image Upload Logic
    $image_name = null;
    if (isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
        $allowed = ["jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png"];
        $filename = $_FILES["image"]["name"];
        $filetype = $_FILES["image"]["type"];
        $filesize = $_FILES["image"]["size"];
        
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        if (!array_key_exists($ext, $allowed)) $err = "Error: Please select a valid file format.";
        
        $maxsize = 5 * 1024 * 1024;
        if ($filesize > $maxsize) $err = "Error: File size is larger than the allowed limit.";
        
        if (empty($err)) {
            $image_name = time() . "_" . $filename;
            move_uploaded_file($_FILES["image"]["tmp_name"], "../assets/uploads/" . $image_name);
        }
    }

    if (empty($err)) {
        $sql = "INSERT INTO items (user_id, type, title, category, location, date_lost_found, description, image_path, status) 
                VALUES (:user_id, :type, :title, :category, :location, :date, :description, :image_path, 'pending')";
        
        if ($stmt = $pdo->prepare($sql)) {
            $stmt->bindParam(":user_id", $user_id, PDO::PARAM_INT);
            $stmt->bindParam(":type", $type, PDO::PARAM_STR);
            $stmt->bindParam(":title", $title, PDO::PARAM_STR);
            $stmt->bindParam(":category", $category, PDO::PARAM_STR);
            $stmt->bindParam(":location", $location, PDO::PARAM_STR);
            $stmt->bindParam(":date", $date, PDO::PARAM_STR);
            $stmt->bindParam(":description", $description, PDO::PARAM_STR);
            $stmt->bindParam(":image_path", $image_name, PDO::PARAM_STR);
            
            if ($stmt->execute()) {
                $success = "Your item has been reported and is awaiting administrator approval.";
            } else {
                $err = "Something went wrong. Please try again later.";
            }
        }
    }
}

include "../includes/header.php";
?>

<div class="container py-5">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="../index.php">Home</a></li>
            <li class="breadcrumb-item active">Report Item</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-white py-3">
                    <h3 class="fw-bold mb-0 text-center" style="color: var(--college-blue)">Report Lost or Found Item</h3>
                </div>
                <div class="card-body p-4">
                    <?php if(!empty($err)): ?>
                        <div class="alert alert-danger"><?php echo $err; ?></div>
                    <?php endif; ?>
                    
                    <?php if(!empty($success)): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i> <?php echo $success; ?>
                            <div class="mt-2"><a href="dashboard.php" class="btn btn-sm btn-success">Go to Dashboard</a></div>
                        </div>
                    <?php endif; ?>

                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" enctype="multipart/form-data">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Post Type</label>
                                <select name="type" class="form-select" required>
                                    <option value="lost" <?php echo (isset($_GET['type']) && $_GET['type'] == 'lost') ? 'selected' : ''; ?>>I Lost Something</option>
                                    <option value="found" <?php echo (isset($_GET['type']) && $_GET['type'] == 'found') ? 'selected' : ''; ?>>I Found Something</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Category</label>
                                <select name="category" class="form-select" required>
                                    <option value="">Select Category</option>
                                    <option value="Electronics">Electronics</option>
                                    <option value="ID Cards/Documents">ID Cards/Documents</option>
                                    <option value="Wallets/Money">Wallets/Money</option>
                                    <option value="Books/Stationery">Books/Stationery</option>
                                    <option value="Clothing">Clothing</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Item Name / Title</label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. Blue HP Laptop Charger" required>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Location</label>
                                <input type="text" name="location" class="form-control" placeholder="e.g. Block A, Library, Canteen" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Date Lost/Found</label>
                                <input type="date" name="date" class="form-control" required value="<?php echo date('Y-m-d'); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description</label>
                            <textarea name="description" class="form-control" rows="4" placeholder="Describe the item in detail (color, brand, distinguishing marks, etc.)"></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Upload Image</label>
                            <input type="file" name="image" class="form-control">
                            <div class="form-text">Clearly visible images help in quicker identification.</div>
                        </div>

                        <div class="d-grid shadow-sm">
                            <button type="submit" class="btn btn-college py-3 fw-bold">Submit Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include "../includes/footer.php"; ?>
