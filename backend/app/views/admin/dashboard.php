<?php
$pageTitle    = 'Dashboard';
$pageSubtitle = 'Today — ' . date('l, d F Y');
?>
<?php include BASE_PATH . '/app/views/layouts/head.php'; ?>
<?php include BASE_PATH . '/app/views/layouts/nav.php'; ?>

<!-- Stat Cards -->
<div class="stats-grid">
  <div class="stat-card">
    <span class="stat-icon">📅</span>
    <div class="stat-label">Pending</div>
    <div class="stat-value"><?= (int)($todaySummary['pending'] ?? 0) ?></div>
    <div class="stat-sub">Awaiting confirmation</div>
  </div>
  <div class="stat-card info">
    <span class="stat-icon">✅</span>
    <div class="stat-label">Confirmed</div>
    <div class="stat-value"><?= (int)($todaySummary['confirmed'] ?? 0) ?></div>
    <div class="stat-sub">Booked today</div>
  </div>
  <div class="stat-card warning">
    <span class="stat-icon">⏳</span>
    <div class="stat-label">In Progress</div>
    <div class="stat-value"><?= (int)($todaySummary['in_progress'] ?? 0) ?></div>
    <div class="stat-sub">Currently serving</div>
  </div>
  <div class="stat-card success">
    <span class="stat-icon">🎉</span>
    <div class="stat-label">Completed</div>
    <div class="stat-value"><?= (int)($todaySummary['completed'] ?? 0) ?></div>
    <div class="stat-sub">Done today</div>
  </div>
  <div class="stat-card danger">
    <span class="stat-icon">❌</span>
    <div class="stat-label">Cancelled</div>
    <div class="stat-value"><?= (int)($todaySummary['cancelled'] ?? 0) ?></div>
    <div class="stat-sub">Today</div>
  </div>
  <?php
    $revenueToday = 0;
    foreach ($todayRevenue as $row) {
      if (!empty($row['revenue_date']) && $row['revenue_date'] === date('Y-m-d')) {
        $revenueToday = (float)$row['total_revenue'];
      }
    }
  ?>
  <div class="stat-card">
    <span class="stat-icon">💰</span>
    <div class="stat-label">Revenue Today</div>
    <div class="stat-value" style="font-size:1.3rem"><?= kes($revenueToday) ?></div>
    <div class="stat-sub">Cash + M-Pesa</div>
  </div>
</div>

<!-- Dashboard body grid -->
<div class="dashboard-grid">

  <!-- Today's Appointments -->
  <div class="card full-width">
    <div class="card-header">
      <span class="card-title">📅 Today's Appointments</span>
      <a href="<?= APP_URL ?>/admin/appointments" class="btn btn-secondary btn-sm">View All</a>
    </div>
    <div class="card-body" style="padding:0">
      <?php
        $appts = $appointments ?? [];
        if (empty($appts)):
      ?>
        <div class="empty-state">
          <div class="empty-icon">📭</div>
          <p>No appointments scheduled for today.</p>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>#</th>
                <th>Client</th>
                <th>Staff</th>
                <th>Time</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($appts as $a): ?>
              <tr>
                <td><?= (int)$a['id'] ?></td>
                <td><?= e($a['client_name'] ?? '—') ?></td>
                <td><?= e($a['staff_name']  ?? 'Unassigned') ?></td>
                <td><?= e(substr($a['start_time'], 0, 5)) ?> – <?= e(substr($a['end_time'], 0, 5)) ?></td>
                <td><?= kes((float)$a['total_amount']) ?></td>
                <td><span class="badge badge-<?= e($a['status']) ?>"><?= e(str_replace('_',' ',$a['status'])) ?></span></td>
                <td>
                  <?php if ($a['status'] === 'pending'): ?>
                    <button class="btn btn-success btn-sm"
                            onclick="updateAppointmentStatus(<?= $a['id'] ?>,'confirmed',this)">Confirm</button>
                  <?php elseif ($a['status'] === 'confirmed'): ?>
                    <button class="btn btn-warning btn-sm"
                            onclick="updateAppointmentStatus(<?= $a['id'] ?>,'in_progress',this)">Start</button>
                  <?php elseif ($a['status'] === 'in_progress'): ?>
                    <button class="btn btn-success btn-sm"
                            onclick="updateAppointmentStatus(<?= $a['id'] ?>,'completed',this)">Complete</button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Low Stock Alerts -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">⚠️ Low Stock Alerts</span>
      <a href="<?= APP_URL ?>/admin/inventory" class="btn btn-secondary btn-sm">Inventory</a>
    </div>
    <div class="card-body" style="padding:0">
      <?php if (empty($lowStock)): ?>
        <div class="empty-state">
          <div class="empty-icon">✅</div>
          <p>All stock levels are healthy.</p>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Product</th><th>Category</th><th>Stock</th><th>Min</th></tr>
            </thead>
            <tbody>
            <?php foreach ($lowStock as $p): ?>
              <tr>
                <td><?= e($p['name']) ?></td>
                <td><?= e($p['category']) ?></td>
                <td><span class="badge badge-low"><?= (int)$p['current_stock'] ?> <?= e($p['unit']) ?></span></td>
                <td><?= (int)$p['low_stock_threshold'] ?></td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Revenue (last 7 days) -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">💰 Recent Revenue</span>
      <a href="<?= APP_URL ?>/admin/reports" class="btn btn-secondary btn-sm">Full Report</a>
    </div>
    <div class="card-body" style="padding:0">
      <?php if (empty($todayRevenue)): ?>
        <div class="empty-state">
          <div class="empty-icon">📊</div>
          <p>No revenue data yet.</p>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Date</th><th>Appointments</th><th>Cash</th><th>M-Pesa</th><th>Total</th></tr>
            </thead>
            <tbody>
            <?php foreach (array_slice($todayRevenue, 0, 7) as $r): ?>
              <tr>
                <td><?= e(date('d M', strtotime($r['revenue_date']))) ?></td>
                <td><?= (int)$r['appointments_paid'] ?></td>
                <td><?= kes((float)$r['cash_revenue'])  ?></td>
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

</div><!-- /dashboard-grid -->

<?php include BASE_PATH . '/app/views/layouts/scripts.php'; ?>
