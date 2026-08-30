<!DOCTYPE html>
<html lang="en" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo isset($pageTitle) ? $pageTitle . ' - Car Service' : 'Car Service'; ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top shadow-sm">
  <div class="container">
    <a class="navbar-brand fw-bold" href="<?php echo BASE_URL; ?>index.php">
      <i class="bi bi-car-front-fill me-1"></i> Car Service
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <?php if (isLoggedIn() && !isAdmin()): ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>customer/dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>customer/my_vehicles.php">My Vehicles</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>customer/book_service.php">Book Service</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>customer/history.php">Service History</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
        <?php elseif (isAdmin()): ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/bookings.php">Bookings</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/customers.php">Customers</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/vehicles.php">Vehicles</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/services.php">Services</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/pricing.php">Pricing</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>register.php">Register</a></li>
        <?php endif; ?>
        <li class="nav-item">
          <button id="themeToggle" class="btn btn-sm btn-outline-secondary ms-lg-2" title="Toggle theme">
            <i class="bi bi-moon-stars-fill"></i>
          </button>
        </li>
      </ul>
    </div>
  </div>
</nav>

<main class="container py-4">
