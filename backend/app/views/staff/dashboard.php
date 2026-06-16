<?php
$pageTitle    = 'My Dashboard';
$pageSubtitle = 'Welcome back, ' . ($user['full_name'] ?? '');
?>
<?php include BASE_PATH . '/app/views/layouts/head.php'; ?>
<?php include BASE_PATH . '/app/views/layouts/nav.php'; ?>

<div class="dashboard-grid">

  <!-- Upcoming Shifts -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">📅 Upcoming Shifts</span>
    </div>
    <div class="card-body">
      <?php if (empty($upcomingShifts)): ?>
        <div class="empty-state">
          <div class="empty-icon">📭</div>
          <p>No upcoming shifts scheduled.</p>
        </div>
      <?php else: ?>
        <div class="shift-list">
          <?php foreach ($upcomingShifts as $s): ?>
            <?php
              $dt    = new DateTimeImmutable($s['shift_date']);
              $isToday = $s['shift_date'] === date('Y-m-d');
            ?>
            <div class="shift-card" style="<?= $isToday ? 'border-color:var(--primary);background:var(--primary-light)' : '' ?>">
              <div class="shift-date-box">
                <div class="day"><?= $dt->format('d') ?></div>
                <div class="month"><?= $dt->format('M') ?></div>
              </div>
              <div class="shift-info">
                <div class="shift-time">
                  <?= e(substr($s['start_time'],0,5)) ?> – <?= e(substr($s['end_time'],0,5)) ?>
                  <?php if ($isToday): ?>
                    <span class="badge badge-confirmed" style="margin-left:6px">Today</span>
                  <?php endif; ?>
                </div>
                <div class="shift-notes"><?= e($s['notes'] ?? 'Regular shift') ?></div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Today's Appointments -->
  <div class="card">
    <div class="card-header">
      <span class="card-title">✨ Today's Clients</span>
      <span style="font-size:.8rem;color:var(--text-muted)"><?= date('d M Y') ?></span>
    </div>
    <div class="card-body" style="padding:0">
      <?php
        $todayAppts = $todayAppointments ?? [];
        if (empty($todayAppts)):
      ?>
        <div class="empty-state">
          <div class="empty-icon">🌸</div>
          <p>No clients scheduled for today.</p>
        </div>
      <?php else: ?>
        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Time</th>
                <th>Client</th>
                <th>Services</th>
                <th>Status</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
            <?php foreach ($todayAppts as $a): ?>
              <tr>
                <td><?= e(substr($a['start_time'],0,5)) ?></td>
                <td><?= e($a['client_name'] ?? '—') ?></td>
                <td><?= e($a['service_names'] ?? '—') ?></td>
                <td><span class="badge badge-<?= e($a['status']) ?>"><?= e(str_replace('_',' ',$a['status'])) ?></span></td>
                <td>
                  <?php if ($a['status'] === 'confirmed'): ?>
                    <button class="btn btn-warning btn-sm"
                            onclick="updateAppointmentStatus(<?= $a['id'] ?>,'in_progress',this)">Start</button>
                  <?php elseif ($a['status'] === 'in_progress'): ?>
                    <button class="btn btn-success btn-sm"
                            onclick="updateAppointmentStatus(<?= $a['id'] ?>,'completed',this)">Done</button>
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

</div>

<?php include BASE_PATH . '/app/views/layouts/scripts.php'; ?>
