<?php
require_once __DIR__ . '/../includes/bootstrap.php';
require_role('admin');

$totalOrders = (int) $pdo->query('SELECT COUNT(*) FROM orders')->fetchColumn();
$pendingRx = (int) $pdo->query("SELECT COUNT(*) FROM prescriptions WHERE status = 'pending'")->fetchColumn();
$revenueToday = (float) $pdo->query(
    "SELECT COALESCE(SUM(total),0) FROM orders WHERE payment_status = 'paid' OR payment_method = 'cod'
     AND DATE(created_at) = CURDATE()"
)->fetchColumn();
$staffCount = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role IN ('staff','admin') AND status = 'active'")->fetchColumn();

// Last 14 days of revenue for the chart, filled with zeros for days with no orders.
$rows = $pdo->query(
    "SELECT DATE(created_at) d, SUM(total) rev FROM orders
     WHERE status NOT IN ('cancelled','pending_verification') AND created_at >= DATE_SUB(CURDATE(), INTERVAL 13 DAY)
     GROUP BY DATE(created_at)"
)->fetchAll();
$byDate = array_column($rows, 'rev', 'd');
$labels = [];
$series = [];
for ($i = 13; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-$i day"));
    $labels[] = date('d M', strtotime($d));
    $series[] = round((float) ($byDate[$d] ?? 0), 2);
}

$staffAccounts = $pdo->query(
    "SELECT * FROM users WHERE role IN ('staff','admin') ORDER BY created_at DESC LIMIT 6"
)->fetchAll();

$auditLog = $pdo->query(
    "SELECT a.*, u.full_name FROM audit_logs a LEFT JOIN users u ON u.id = a.user_id ORDER BY a.created_at DESC LIMIT 6"
)->fetchAll();

$area = 'admin'; $active = 'dashboard'; $pageTitle = 'System oversight';
require __DIR__ . '/../includes/app-header.php';
?>
<div class="row g-3 mb-4">
  <div class="col-6 col-md-3"><div class="kpi-tile"><div class="kpi-value"><?= $totalOrders ?></div><div class="kpi-label">Total orders</div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-tile"><div class="kpi-value"><?= $pendingRx ?></div><div class="kpi-label">Rx pending</div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-tile"><div class="kpi-value"><?= money($revenueToday) ?></div><div class="kpi-label">Revenue today</div></div></div>
  <div class="col-6 col-md-3"><div class="kpi-tile"><div class="kpi-value"><?= $staffCount ?></div><div class="kpi-label">Active staff</div></div></div>
</div>

<div class="row g-3">
  <div class="col-md-7">
    <div class="kpi-tile">
      <h2 class="h6 fw-bold mb-2">Revenue — last 14 days</h2>
      <canvas id="revenueChart" height="180"></canvas>
    </div>
  </div>
  <div class="col-md-5">
    <div class="kpi-tile mb-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h6 fw-bold mb-0">Staff accounts</h2>
        <a href="<?= url('admin/staff.php') ?>" class="small">Manage</a>
      </div>
      <?php foreach ($staffAccounts as $s): ?>
        <div class="d-flex justify-content-between small border-bottom py-1">
          <span><?= e($s['full_name']) ?> <span class="text-muted">(<?= e($s['role']) ?>)</span></span>
          <span class="badge <?= $s['status'] === 'active' ? 'text-bg-success' : 'text-bg-secondary' ?>"><?= ucfirst($s['status']) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="kpi-tile">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h6 fw-bold mb-0">Audit trail</h2>
        <a href="<?= url('admin/audit-log.php') ?>" class="small">View all</a>
      </div>
      <?php foreach ($auditLog as $log): ?>
        <div class="small border-bottom py-1">
          <?= e($log['full_name'] ?? 'System') ?> — <?= e($log['action']) ?>
          <span class="text-muted">· <?= date('d M, g:i A', strtotime($log['created_at'])) ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('revenueChart'), {
  type: 'line',
  data: {
    labels: <?= json_encode($labels) ?>,
    datasets: [{
      label: 'Revenue (LKR)',
      data: <?= json_encode($series) ?>,
      borderColor: '#047857',
      backgroundColor: 'rgba(4,120,87,0.12)',
      fill: true,
      tension: 0.3,
      pointRadius: 3
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});
</script>
<?php require __DIR__ . '/../includes/app-footer.php'; ?>
