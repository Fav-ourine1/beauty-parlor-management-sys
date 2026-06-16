<?php
$pageTitle    = 'Reports';
$pageSubtitle = 'Analytics & insights';

$totalRevenue = array_sum(array_column($revenue, 'total_revenue'));
$totalPaid    = array_sum(array_column($revenue, 'appointments_paid'));
$cashRevenue  = array_sum(array_column($revenue, 'cash_revenue'));
$mpesaRevenue = array_sum(array_column($revenue, 'mpesa_revenue'));
?>
<?php include BASE_PATH . '/app/views/layouts/head.php'; ?>
<?php include BASE_PATH . '/app/views/layouts/nav.php'; ?>

<!-- Summary stats -->
<div class="stats-grid" style="margin-bottom:28px">
  <div class="stat-card">
    <span class="stat-icon">💰</span>
    <div class="stat-label">Total Revenue</div>
    <div class="stat-value" style="font-size:1.3rem"><?= kes($totalRevenue) ?></div>
    <div class="stat-sub">All time</div>
  </div>
  <div class="stat-card success">
    <span class="stat-icon">📱</span>
    <div class="stat-label">M-Pesa</div>
    <div class="stat-value" style="font-size:1.3rem"><?= kes($mpesaRevenue) ?></div>
    <div class="stat-sub"><?= $totalRevenue > 0 ? round($mpesaRevenue / $totalRevenue * 100) : 0 ?>% of total</div>
  </div>
  <div class="stat-card info">
    <span class="stat-icon">💵</span>
    <div class="stat-label">Cash</div>
    <div class="stat-value" style="font-size:1.3rem"><?= kes($cashRevenue) ?></div>
    <div class="stat-sub"><?= $totalRevenue > 0 ? round($cashRevenue / $totalRevenue * 100) : 0 ?>% of total</div>
  </div>
  <div class="stat-card">
    <span class="stat-icon">🧾</span>
    <div class="stat-label">Paid Appointments</div>
    <div class="stat-value"><?= (int)$totalPaid ?></div>
    <div class="stat-sub">All time</div>
  </div>
</div>

<div class="dashboard-grid">

  <!-- Revenue table -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">💰 Daily Revenue</span>
    </div>
    <div class="card-body" style="padding:0">
      <?php if (empty($revenue)): ?>
        <div class="empty-state"><div class="empty-icon">📊</div><p>No revenue recorded yet.</p></div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Date</th><th>Appointments</th><th>Cash</th><th>M-Pesa</th><th>Total</th></tr>
            </thead>
            <tbody>
            <?php foreach ($revenue as $r): ?>
              <tr>
                <td><?= e(date('d M Y', strtotime($r['revenue_date']))) ?></td>
                <td><?= (int)$r['appointments_paid'] ?></td>
                <td><?= kes((float)$r['cash_revenue']) ?></td>
                <td><?= kes((float)$r['mpesa_revenue']) ?></td>
                <td><strong><?= kes((float)$r['total_revenue']) ?></strong></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Today's appointment summary -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">📅 Today's Summary</span>
      <span style="font-size:.8rem;color:var(--text-muted)"><?= date('d M Y') ?></span>
    </div>
    <div class="card-body">
      <div style="display:flex;flex-direction:column;gap:12px">
        <?php foreach ($todaySummary as $status => $count): ?>
          <div style="display:flex;align-items:center;justify-content:space-between">
            <span class="badge badge-<?= e($status) ?>" style="font-size:.82rem"><?= ucwords(str_replace('_',' ',$status)) ?></span>
            <strong style="font-size:1.2rem"><?= (int)$count ?></strong>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Staff attendance -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">👩‍💼 Staff Attendance — <?= date('F Y', mktime(0,0,0,$month,1,$year)) ?></span>
    </div>
    <div class="card-body" style="padding:0">
      <?php if (empty($attendance)): ?>
        <div class="empty-state"><div class="empty-icon">📋</div><p>No attendance data yet.</p></div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Staff</th><th>Title</th><th>Present</th><th>Late</th><th>Absent</th><th>Total Shifts</th></tr>
            </thead>
            <tbody>
            <?php foreach ($attendance as $a): ?>
              <tr>
                <td><?= e($a['full_name']) ?></td>
                <td><?= e($a['job_title']) ?></td>
                <td><span class="badge badge-completed"><?= (int)$a['present'] ?></span></td>
                <td><span class="badge badge-late"><?= (int)$a['late'] ?></span></td>
                <td><span class="badge badge-absent"><?= (int)$a['absent'] ?></span></td>
                <td><?= (int)$a['total'] ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Low stock -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">⚠️ Low Stock</span>
      <a href="<?= APP_URL ?>/admin/inventory" class="btn btn-secondary btn-sm">Manage</a>
    </div>
    <div class="card-body" style="padding:0">
      <?php if (empty($lowStock)): ?>
        <div class="empty-state"><div class="empty-icon">✅</div><p>All stock levels are healthy.</p></div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead><tr><th>Product</th><th>Stock</th><th>Min</th><th>Reorder Qty</th></tr></thead>
            <tbody>
            <?php foreach ($lowStock as $p): ?>
              <tr>
                <td><?= e($p['name']) ?></td>
                <td><span class="badge badge-low"><?= (int)$p['current_stock'] ?> <?= e($p['unit']) ?></span></td>
                <td><?= (int)$p['low_stock_threshold'] ?></td>
                <td><?= (int)$p['reorder_quantity'] ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php include BASE_PATH . '/app/views/layouts/scripts.php'; ?>
