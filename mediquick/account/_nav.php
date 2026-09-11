<?php
/** Sidebar for the customer account area. $active is set by the calling page. */
$active = $active ?? '';
$links = [
    'dashboard'     => ['dashboard.php', 'Dashboard'],
    'orders'        => ['orders.php', 'My orders'],
    'prescriptions' => ['prescriptions.php', 'My prescriptions'],
    'notifications' => ['notifications.php', 'Notifications'],
    'profile'       => ['profile.php', 'Profile'],
];
?>
<div class="list-group mb-4">
  <?php foreach ($links as $key => [$href, $label]): ?>
    <a href="<?= url('account/' . $href) ?>" class="list-group-item list-group-item-action <?= $active === $key ? 'active' : '' ?>"
       style="<?= $active === $key ? 'background:var(--brand);border-color:var(--brand)' : '' ?>">
      <?= e($label) ?>
      <?php if ($key === 'notifications' && ($n = unread_notification_count($pdo, current_user()['id'])) > 0): ?>
        <span class="badge bg-danger rounded-pill float-end"><?= $n ?></span>
      <?php endif; ?>
    </a>
  <?php endforeach; ?>
</div>
