<?php
$pageTitle    = 'My Bookings';
$pageSubtitle = 'Welcome, ' . ($user['full_name'] ?? '');
$pageAction   = '<a href="' . APP_URL . '/client/book" class="btn btn-primary"><span>+</span> Book Appointment</a>';
?>
<?php include BASE_PATH . '/app/views/layouts/head.php'; ?>
<?php include BASE_PATH . '/app/views/layouts/nav.php'; ?>

<!-- Upcoming -->
<div class="card" style="margin-bottom:24px">
  <div class="card-header">
    <span class="card-title">📅 Upcoming Appointments</span>
  </div>
  <div class="card-body" style="padding:0">
    <?php
      $upcoming = array_filter($appointments ?? [], fn($a) => in_array($a['status'], ['pending','confirmed','in_progress']));
      if (empty($upcoming)):
    ?>
      <div class="empty-state">
        <div class="empty-icon">🌸</div>
        <p>No upcoming appointments.<br>
           <a href="<?= APP_URL ?>/client/book" style="color:var(--primary)">Book your first appointment →</a></p>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Date</th><th>Time</th><th>Stylist</th><th>Amount</th><th>Status</th><th></th></tr>
          </thead>
          <tbody>
          <?php foreach ($upcoming as $a): ?>
            <tr>
              <td><?= e(date('d M Y', strtotime($a['appointment_date']))) ?></td>
              <td><?= e(substr($a['start_time'],0,5)) ?></td>
              <td><?= e($a['staff_name'] ?? 'To be assigned') ?></td>
              <td><?= kes((float)$a['total_amount']) ?></td>
              <td><span class="badge badge-<?= e($a['status']) ?>"><?= e(str_replace('_',' ',$a['status'])) ?></span></td>
              <td>
                <?php if (in_array($a['status'], ['pending','confirmed'])): ?>
                  <button class="btn btn-danger btn-sm"
                          onclick="cancelAppointment(<?= $a['id'] ?>, this)">Cancel</button>
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

<!-- History -->
<div class="card">
  <div class="card-header">
    <span class="card-title">🕓 Past Appointments</span>
  </div>
  <div class="card-body" style="padding:0">
    <?php
      $past = array_filter($appointments ?? [], fn($a) => in_array($a['status'], ['completed','cancelled','no_show']));
      if (empty($past)):
    ?>
      <div class="empty-state" style="padding:24px">
        <p style="color:var(--text-muted)">No past appointments yet.</p>
      </div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Date</th><th>Time</th><th>Amount</th><th>Status</th></tr>
          </thead>
          <tbody>
          <?php foreach ($past as $a): ?>
            <tr>
              <td><?= e(date('d M Y', strtotime($a['appointment_date']))) ?></td>
              <td><?= e(substr($a['start_time'],0,5)) ?></td>
              <td><?= kes((float)$a['total_amount']) ?></td>
              <td><span class="badge badge-<?= e($a['status']) ?>"><?= e(str_replace('_',' ',$a['status'])) ?></span></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php
$pageScript = <<<JS
async function cancelAppointment(id, btn) {
  if (!confirm('Are you sure you want to cancel this appointment?')) return;
  btn.disabled = true;
  try {
    await API.delete('/api/appointments/' + id, { reason: 'Cancelled by client' });
    toast('Appointment cancelled.', 'success');
    setTimeout(() => location.reload(), 800);
  } catch(e) {
    toast(e.message, 'error');
    btn.disabled = false;
  }
}
JS;
?>
<?php include BASE_PATH . '/app/views/layouts/scripts.php'; ?>
