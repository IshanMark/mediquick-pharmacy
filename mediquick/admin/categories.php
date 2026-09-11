<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$admin = current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    if (isset($_POST['add_category'])) {
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        if ($name === '') {
            $errors[] = 'Category name is required.';
        } else {
            $slug = strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $name), '-'));
            $pdo->prepare('INSERT INTO categories (name, slug, description) VALUES (?, ?, ?)')
                ->execute([$name, $slug, $description ?: null]);
            log_action($pdo, $admin['id'], 'category.create', 'category', (int) $pdo->lastInsertId());
            flash('success', 'Category added.');
            redirect('admin/categories.php');
        }
    }

    if (isset($_POST['delete_category'])) {
        $id = (int) $_POST['delete_category'];
        $inUse = $pdo->prepare('SELECT COUNT(*) FROM products WHERE category_id = ?');
        $inUse->execute([$id]);
        if ((int) $inUse->fetchColumn() > 0) {
            flash('error', "Can't delete a category that still has products in it.");
        } else {
            $pdo->prepare('DELETE FROM categories WHERE id = ?')->execute([$id]);
            log_action($pdo, $admin['id'], 'category.delete', 'category', $id);
            flash('success', 'Category deleted.');
        }
        redirect('admin/categories.php');
    }
}

$categories = $pdo->query(
    'SELECT c.*, COUNT(p.id) product_count FROM categories c LEFT JOIN products p ON p.category_id = c.id
     GROUP BY c.id ORDER BY c.name'
)->fetchAll();

$testimonials = $pdo->query(
    "SELECT t.*, u.full_name FROM testimonials t JOIN users u ON u.id = t.customer_id
     WHERE t.is_approved = 0 ORDER BY t.created_at DESC"
)->fetchAll();

if (isset($_GET['approve_testimonial'])) {
    $pdo->prepare('UPDATE testimonials SET is_approved = 1 WHERE id = ?')->execute([(int) $_GET['approve_testimonial']]);
    redirect('admin/categories.php');
}

$area = 'admin'; $active = 'categories'; $pageTitle = 'Categories & testimonials';
require __DIR__ . '/../includes/app-header.php';
?>
<div class="row g-4">
  <div class="col-md-6">
    <div class="kpi-tile mb-4">
      <h2 class="h6 fw-bold">Add category</h2>
      <?php foreach ($errors as $err): ?><div class="alert alert-danger small"><?= e($err) ?></div><?php endforeach; ?>
      <form method="post" class="d-flex gap-2">
        <?= csrf_field() ?>
        <input name="name" class="form-control" placeholder="Category name" required>
        <button type="submit" name="add_category" value="1" class="btn" style="background:var(--brand);color:#fff">Add</button>
      </form>
    </div>
    <div class="kpi-tile">
      <h2 class="h6 fw-bold">Categories</h2>
      <table class="table table-sm-tight mb-0">
        <thead><tr><th>Name</th><th>Products</th><th></th></tr></thead>
        <tbody>
        <?php foreach ($categories as $cat): ?>
          <tr>
            <td><?= e($cat['name']) ?></td>
            <td><?= (int) $cat['product_count'] ?></td>
            <td>
              <form method="post" onsubmit="return confirm('Delete this category?')">
                <?= csrf_field() ?>
                <button type="submit" name="delete_category" value="<?= $cat['id'] ?>" class="btn btn-link btn-sm p-0 small text-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="col-md-6">
    <div class="kpi-tile">
      <h2 class="h6 fw-bold">Testimonials awaiting approval</h2>
      <?php if (!$testimonials): ?><p class="small text-muted mb-0">Nothing waiting.</p><?php endif; ?>
      <?php foreach ($testimonials as $t): ?>
        <div class="border-bottom py-2">
          <div class="text-warning small"><?= str_repeat('★', (int) $t['rating']) ?></div>
          <p class="small mb-1">"<?= e($t['message']) ?>" — <?= e($t['full_name']) ?></p>
          <a href="?approve_testimonial=<?= $t['id'] ?>" class="small">Approve</a>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php require __DIR__ . '/../includes/app-footer.php'; ?>
