<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Page not found';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-5 text-center">
  <h1 class="display-6 fw-bold">404 — Page not found</h1>
  <p class="text-muted">That page doesn't exist, or has moved.</p>
  <a href="<?= url('index.php') ?>" class="btn" style="background:var(--brand);color:#fff">Back to home</a>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
