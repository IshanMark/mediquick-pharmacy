<?php
require_once __DIR__ . '/includes/bootstrap.php';

$slug = $_GET['article'] ?? null;

if ($slug) {
    $stmt = $pdo->prepare('SELECT a.*, u.full_name FROM articles a JOIN users u ON u.id = a.author_id WHERE a.slug = ? AND a.published_at IS NOT NULL');
    $stmt->execute([$slug]);
    $article = $stmt->fetch();
    if (!$article) {
        http_response_code(404);
        require __DIR__ . '/404.php';
        exit;
    }

    $pageTitle = $article['title'];
    require __DIR__ . '/includes/header.php';
    ?>
    <div class="container py-4" style="max-width:720px">
      <p class="small"><a href="<?= url('health-hub.php') ?>">&larr; Health Hub</a></p>
      <h1 class="h3 fw-bold"><?= e($article['title']) ?></h1>
      <p class="text-muted small">By <?= e($article['full_name']) ?> · <?= date('d M Y', strtotime($article['published_at'])) ?></p>
      <div class="mt-4"><?= nl2br(e($article['body'])) ?></div>
    </div>
    <?php
    require __DIR__ . '/includes/footer.php';
    exit;
}

$articles = $pdo->query(
    "SELECT a.*, u.full_name FROM articles a JOIN users u ON u.id = a.author_id
     WHERE a.published_at IS NOT NULL ORDER BY a.published_at DESC"
)->fetchAll();

$pageTitle = 'Health Hub';
require __DIR__ . '/includes/header.php';
?>
<div class="container py-4">
  <h1 class="h3 fw-bold mb-1">Health Hub &amp; knowledge center</h1>
  <p class="text-muted mb-4">Tips and guidance from our pharmacists.</p>

  <?php if (!$articles): ?>
    <div class="alert alert-light border">No articles published yet. Check back soon.</div>
  <?php endif; ?>

  <div class="row g-3">
    <?php foreach ($articles as $a): ?>
      <div class="col-md-4">
        <div class="kpi-tile h-100">
          <h2 class="h6 fw-bold"><a class="text-decoration-none" href="<?= url('health-hub.php?article=' . e($a['slug'])) ?>"><?= e($a['title']) ?></a></h2>
          <p class="small text-muted"><?= e(mb_strimwidth(strip_tags($a['body']), 0, 140, '…')) ?></p>
          <p class="small text-muted mb-0">By <?= e($a['full_name']) ?> · <?= date('d M Y', strtotime($a['published_at'])) ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
