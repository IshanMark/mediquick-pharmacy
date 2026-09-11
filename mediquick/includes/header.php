<?php
/**
 * Public/customer site chrome. Include after includes/bootstrap.php.
 * Optional $pageTitle string set by the calling page before this include.
 */
$pageTitle = $pageTitle ?? 'MediQuick Pharmacy';
$user = current_user();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · MediQuick Pharmacy</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="announce-bar">
  <div class="container d-flex justify-content-between align-items-center flex-wrap gap-1">
    <span><i class="fa-solid fa-truck-fast me-1"></i> Island-wide delivery from our Kurunegala branch</span>
    <span class="d-flex gap-3">
      <a href="tel:+94372224455"><i class="fa-solid fa-phone me-1"></i> +94 37 222 4455</a>
      <?php if ($user && $user['role'] === 'customer'): ?>
        <a href="<?= url('account/orders.php') ?>"><i class="fa-solid fa-location-dot me-1"></i> Track order</a>
      <?php endif; ?>
    </span>
  </div>
</div>
<nav class="navbar navbar-expand-lg site-navbar sticky-top">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('index.php') ?>">
      <span class="brand-mark"><i class="fa-solid fa-heart-pulse"></i></span>
      <span class="d-flex flex-column leading-tight">
        <span class="brand-name">MediQuick</span>
        <span class="brand-sub">Pharmacy &amp; Wellness</span>
      </span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-label="Toggle menu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMain">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="<?= url('shop.php') ?>">Shop</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('prescription-upload.php') ?>">Prescription <span class="badge-rx-inline">Rx</span></a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('health-hub.php') ?>">Health Hub</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= url('contact.php') ?>">Contact</a></li>
      </ul>
      <ul class="navbar-nav align-items-lg-center gap-2">
        <li class="nav-item">
          <a class="icon-btn position-relative" href="<?= url('cart.php') ?>" aria-label="Cart">
            <i class="fa-solid fa-bag-shopping"></i>
            <?php if (cart_count() > 0): ?>
              <span class="cart-badge"><?= cart_count() ?></span>
            <?php endif; ?>
          </a>
        </li>
        <?php if ($user): ?>
          <?php if ($user['role'] === 'customer'): ?>
            <li class="nav-item"><a class="icon-btn" href="<?= url('account/dashboard.php') ?>" aria-label="My account"><i class="fa-regular fa-user"></i></a></li>
          <?php elseif ($user['role'] === 'staff'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= url('staff/dashboard.php') ?>">Staff Panel</a></li>
          <?php elseif ($user['role'] === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= url('admin/dashboard.php') ?>">Admin Panel</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="btn btn-outline-secondary btn-sm" href="<?= url('logout.php') ?>">Log out</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="btn btn-brand btn-sm" href="<?= url('login.php') ?>">Sign in</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
<?php if ($msg = flash('success')): ?>
  <div class="container mt-3"><div class="alert alert-success mb-0"><?= e($msg) ?></div></div>
<?php endif; ?>
<?php if ($msg = flash('error')): ?>
  <div class="container mt-3"><div class="alert alert-danger mb-0"><?= e($msg) ?></div></div>
<?php endif; ?>
<main>
