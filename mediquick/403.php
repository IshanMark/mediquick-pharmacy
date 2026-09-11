<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Access denied';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-5 text-center">
  <h1 class="display-6 fw-bold">403 — Access denied</h1>
  <p class="text-muted">You don't have permission to view this page.</p>
  <a href="<?= url('index.php') ?>" class="btn btn-brand" style="background:var(--brand);color:#fff">Back to home</a>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
