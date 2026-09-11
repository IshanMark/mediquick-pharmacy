<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$page = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 40;
$offset = ($page - 1) * $perPage;

$total = (int) $pdo->query('SELECT COUNT(*) FROM audit_logs')->fetchColumn();
$totalPages = max(1, (int) ceil($total / $perPage));

$stmt = $pdo->prepare(
    "SELECT a.*, u.full_name FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id
     ORDER BY a.created_at DESC LIMIT $perPage OFFSET $offset"
);
$stmt->execute();
$logs = $stmt->fetchAll();

$area = 'admin'; $active = 'audit-log'; $pageTitle = 'Audit log';
require __DIR__ . '/../includes/app-header.php';
?>
<div class="kpi-tile">
  <?php if (!$logs): ?>
    <p class="text-muted mb-0">No activity recorded yet.</p>
  <?php else: ?>
    <table class="table table-sm-tight">
      <thead><tr><th>When</th><th>User</th><th>Action</th><th>Entity</th><th>IP</th></tr></thead>
      <tbody>
      <?php foreach ($logs as $log): ?>
        <tr>
          <td><?= date('d M Y, g:i A', strtotime($log['created_at'])) ?></td>
          <td><?= e($log['full_name'] ?? 'System') ?></td>
          <td><code><?= e($log['action']) ?></code></td>
          <td><?= $log['entity'] ? e($log['entity']) . ' #' . $log['entity_id'] : '—' ?></td>
          <td class="small text-muted"><?= e($log['ip_address'] ?? '') ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?php if ($totalPages > 1): ?>
  <nav class="mt-3">
    <ul class="pagination">
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>"><a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a></li>
      <?php endfor; ?>
    </ul>
  </nav>
<?php endif; ?>
<?php require __DIR__ . '/../includes/app-footer.php'; ?>
