<?php
if(session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>St. Aloysius College - Lost & Found Management System</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --college-blue: #003366;
            --college-light-blue: #0056b3;
            --college-white: #ffffff;
            --college-gray: #f8f9fa;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--college-gray);
            color: #333;
        }
        .navbar {
            background-color: var(--college-blue);
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .navbar-brand, .nav-link {
            color: var(--college-white) !important;
        }
        .btn-college {
            background-color: var(--college-blue);
            color: white;
            border-radius: 8px;
            transition: all 0.3s;
        }
        .btn-college:hover {
            background-color: var(--college-light-blue);
            color: white;
            box-shadow: 0 4px 12px rgba(0, 51, 102, 0.3);
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .footer {
            background-color: var(--college-blue);
            color: white;
            padding: 30px 0;
            margin-top: 50px;
        }
        .badge-pending { background-color: #ffc107; color: #000; }
        .badge-approved { background-color: #198754; color: #fff; }
        .badge-claimed { background-color: #0d6efd; color: #fff; }
        .badge-returned { background-color: #6c757d; color: #fff; }
        .welcome-section {
            background: linear-gradient(135deg, var(--college-blue) 0%, var(--college-light-blue) 100%);
            padding: 60px 0;
            color: white;
            border-radius: 0 0 50px 50px;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/lostphp/index.php">
            <i class="fas fa-search-location me-2"></i>St. Aloysius L&F
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="/lostphp/index.php">Home</a></li>
                <?php if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                    <?php if($_SESSION["role"] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="/lostphp/admin/dashboard.php">Admin Panel</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="/lostphp/user/dashboard.php">My Dashboard</a></li>
                        <li class="nav-item"><a class="nav-link" href="/lostphp/user/post_item.php">Report Item</a></li>
                    <?php endif; ?>
                    <li class="nav-item dropdown ms-lg-3">
                        <a class="nav-link dropdown-toggle btn btn-sm btn-outline-light px-3" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i> <?php echo htmlspecialchars($_SESSION["username"]); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="/lostphp/logout.php">Logout</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item mx-2"><a class="nav-link" href="/lostphp/login.php">Login</a></li>
                    <li class="nav-item"><a class="btn btn-light btn-sm px-4 fw-bold" href="/lostphp/register.php">Sign Up</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<main class="min-vh-100">
