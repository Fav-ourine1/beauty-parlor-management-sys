<?php
$pageTitle    = 'Appointments';
$pageSubtitle = 'Manage all bookings';
$pageAction   = '<button class="btn btn-primary" onclick="document.getElementById(\'filter-bar\').classList.toggle(\'hidden\')">🔍 Filter</button>';
?>
<?php include BASE_PATH . '/app/views/layouts/head.php'; ?>
<?php include BASE_PATH . '/app/views/layouts/nav.php'; ?>

<!-- Filter bar -->
<form method="GET" id="filter-bar" style="background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px 20px;margin-bottom:22px;display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end">
  <div class="form-group" style="margin:0;flex:1;min-width:150px">
    <label class="form-label">Date</label>
    <input class="form-control" type="date" name="date" value="<?= e($_GET['date'] ?? '') ?>">
  </div>
  <div class="form-group" style="margin:0;flex:1;min-width:150px">
    <label class="form-label">Status</label>
    <select class="form-control" name="status">
      <option value="">All statuses</option>
      <?php foreach (['pending','confirmed','in_progress','completed','cancelled','no_show'] as $s): ?>
        <option value="<?= $s ?>" <?= ($_GET['status'] ?? '') === $s ? 'selected' : '' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <button class="btn btn-primary" type="submit">Apply</button>
  <a href="<?= APP_URL ?>/admin/appointments" class="btn btn-secondary">Clear</a>
</form>

<div class="card">
  <div class="card-header">
    <span class="card-title">All Appointments <span style="font-weight:400;color:var(--text-muted);font-size:.85rem">(<?= count($appointments) ?>)</span></span>
  </div>
  <div class="card-body" style="padding:0">
    <?php if (empty($appointments)): ?>
      <div class="empty-state"><div class="empty-icon">📭</div><p>No appointments found.</p></div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>#</th><th>Date</th><th>Time</th><th>Client</th><th>Staff</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
          </thead>
          <tbody>
          <?php foreach ($appointments as $a): ?>
            <tr>
              <td><?= (int)$a['id'] ?></td>
              <td><?= e(date('d M Y', strtotime($a['appointment_date']))) ?></td>
              <td><?= e(substr($a['start_time'],0,5)) ?> – <?= e(substr($a['end_time'],0,5)) ?></td>
              <td>
                <strong><?= e($a['client_name'] ?? '—') ?></strong>
                <?php if (!empty($a['client_phone'])): ?>
                  <br><small style="color:var(--text-muted)"><?= e($a['client_phone']) ?></small>
                <?php endif; ?>
              </td>
              <td><?= e($a['staff_name'] ?? 'Unassigned') ?></td>
              <td><?= kes((float)$a['total_amount']) ?></td>
              <td><span class="badge badge-<?= e($a['status']) ?>"><?= e(str_replace('_',' ',$a['status'])) ?></span></td>
              <td style="white-space:nowrap">
                <?php if ($a['status'] === 'pending'): ?>
                  <button class="btn btn-success btn-sm" onclick="updateAppointmentStatus(<?= $a['id'] ?>,'confirmed',this)">Confirm</button>
                <?php elseif ($a['status'] === 'confirmed'): ?>
                  <button class="btn btn-warning btn-sm" onclick="updateAppointmentStatus(<?= $a['id'] ?>,'in_progress',this)">Start</button>
                <?php elseif ($a['status'] === 'in_progress'): ?>
                  <button class="btn btn-success btn-sm" onclick="updateAppointmentStatus(<?= $a['id'] ?>,'completed',this)">Complete</button>
                <?php endif; ?>
                <?php if (!in_array($a['status'], ['completed','cancelled','no_show'])): ?>
                  <button class="btn btn-danger btn-sm" onclick="cancelAppt(<?= $a['id'] ?>,this)">Cancel</button>
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

<?php
$pageScript = <<<JS
async function cancelAppt(id, btn) {
  if (!confirm('Cancel this appointment?')) return;
  btn.disabled = true;
  try {
    await API.delete('/api/appointments/' + id, { reason: 'Cancelled by admin' });
    toast('Appointment cancelled', 'success');
    setTimeout(() => location.reload(), 800);
  } catch(e) {
    toast(e.message, 'error');
    btn.disabled = false;
  }
}
JS;
?>
<?php include BASE_PATH . '/app/views/layouts/scripts.php'; ?>
