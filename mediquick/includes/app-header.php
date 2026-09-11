<?php
/**
 * Shared shell for the staff and admin panels (sidebar + topbar).
 * Calling page sets $area ('staff'|'admin'), $active (nav key) and $pageTitle before including.
 */
$area = $area ?? 'staff';
$active = $active ?? '';
$pageTitle = $pageTitle ?? ucfirst($area);
$user = current_user();

$navByArea = [
    'staff' => [
        'dashboard' => ['dashboard.php', 'fa-gauge-high', 'Dashboard'],
        'rx-queue'  => ['rx-queue.php', 'fa-file-prescription', 'Prescription queue'],
        'orders'    => ['orders.php', 'fa-box', 'Orders'],
        'products'  => ['products.php', 'fa-pills', 'Products & stock'],
        'inquiries' => ['inquiries.php', 'fa-comments', 'Inquiries'],
    ],
    'admin' => [
        'dashboard'    => ['dashboard.php', 'fa-gauge-high', 'Dashboard'],
        'staff'        => ['staff.php', 'fa-users', 'Staff accounts'],
        'reports'      => ['reports.php', 'fa-chart-line', 'Reports'],
        'categories'   => ['categories.php', 'fa-tags', 'Categories'],
        'audit-log'    => ['audit-log.php', 'fa-shield-halved', 'Audit log'],
    ],
];
$nav = $navByArea[$area];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?> · MediQuick <?= ucfirst($area) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<div class="app-shell">
  <aside class="app-sidebar">
    <div class="brand">
      <span class="brand-mark brand-mark-sm"><i class="fa-solid fa-heart-pulse"></i></span>
      <span>MediQuick <span class="opacity-75 fw-normal">· <?= ucfirst($area) ?></span></span>
    </div>
    <nav class="nav flex-column py-2">
      <?php foreach ($nav as $key => [$href, $icon, $label]): ?>
        <a class="nav-link <?= $active === $key ? 'active' : '' ?>" href="<?= url($area . '/' . $href) ?>">
          <i class="fa-solid <?= $icon ?> me-2"></i><?= e($label) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="mt-auto px-3 py-3 small">
      <a class="nav-link px-0" href="<?= url('index.php') ?>"><i class="fa-solid fa-arrow-left me-2"></i>Public site</a>
      <a class="nav-link px-0" href="<?= url('logout.php') ?>"><i class="fa-solid fa-right-from-bracket me-2"></i>Log out</a>
    </div>
  </aside>
  <div class="app-main">
    <div class="app-topbar d-flex justify-content-between align-items-center">
      <h1 class="h5 fw-bold mb-0"><?= e($pageTitle) ?></h1>
      <span class="small text-muted"><?= e($user['name']) ?> · <?= ucfirst($user['role']) ?></span>
    </div>
    <div class="app-content">
      <?php if ($msg = flash('success')): ?><div class="alert alert-success"><?= e($msg) ?></div><?php endif; ?>
      <?php if ($msg = flash('error')): ?><div class="alert alert-danger"><?= e($msg) ?></div><?php endif; ?>
