<?php
$pageTitle    = 'Staff';
$pageSubtitle = 'Manage team members';
$pageAction   = '<button class="btn btn-primary" onclick="openModal(\'add-staff-modal\')">+ Add Staff</button>';
?>
<?php include BASE_PATH . '/app/views/layouts/head.php'; ?>
<?php include BASE_PATH . '/app/views/layouts/nav.php'; ?>

<div class="card">
  <div class="card-header">
    <span class="card-title">Team Members <span style="font-weight:400;color:var(--text-muted);font-size:.85rem">(<?= count($staffList) ?>)</span></span>
  </div>
  <div class="card-body" style="padding:0">
    <?php if (empty($staffList)): ?>
      <div class="empty-state"><div class="empty-icon">👩‍💼</div><p>No staff members yet. Add one to get started.</p></div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Name</th><th>Job Title</th><th>Phone</th><th>Email</th><th>Hire Date</th><th>Status</th><th></th></tr>
          </thead>
          <tbody>
          <?php foreach ($staffList as $s): ?>
            <tr>
              <td>
                <div style="display:flex;align-items:center;gap:10px">
                  <div class="user-avatar" style="width:32px;height:32px;font-size:.75rem;background:var(--accent)">
                    <?= strtoupper(substr($s['full_name'],0,1)) ?>
                  </div>
                  <strong><?= e($s['full_name']) ?></strong>
                </div>
              </td>
              <td><?= e($s['job_title']) ?></td>
              <td><?= e($s['phone'] ?? '—') ?></td>
              <td><?= e($s['email'] ?? '—') ?></td>
              <td><?= !empty($s['hire_date']) ? e(date('d M Y', strtotime($s['hire_date']))) : '—' ?></td>
              <td>
                <span class="badge <?= $s['is_active'] ? 'badge-completed' : 'badge-cancelled' ?>">
                  <?= $s['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td>
                <button class="btn btn-secondary btn-sm"
                        onclick="openShiftModal(<?= $s['id'] ?>, '<?= e($s['full_name']) ?>')">
                  Schedule Shift
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add Staff Modal -->
<div id="add-staff-modal" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Add Staff Member</span>
      <button class="modal-close" onclick="closeModal('add-staff-modal')">✕</button>
    </div>
    <div class="modal-body">
      <div id="staff-error" class="alert alert-error" style="display:none"></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input class="form-control" type="text" id="s-name" placeholder="Jane Njeri">
        </div>
        <div class="form-group">
          <label class="form-label">Job Title</label>
          <input class="form-control" type="text" id="s-title" placeholder="Senior Stylist">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Email</label>
          <input class="form-control" type="email" id="s-email" placeholder="jane@email.com">
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input class="form-control" type="tel" id="s-phone" placeholder="07XXXXXXXX">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Password</label>
          <input class="form-control" type="password" id="s-password" placeholder="••••••••">
        </div>
        <div class="form-group">
          <label class="form-label">Hire Date</label>
          <input class="form-control" type="date" id="s-hire-date">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Specialisations <small style="font-weight:400;color:var(--text-muted)">(optional)</small></label>
        <input class="form-control" type="text" id="s-specs" placeholder="Hair Colouring, Nail Care">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('add-staff-modal')">Cancel</button>
      <button class="btn btn-primary" id="save-staff-btn" onclick="saveStaff()">Add Staff Member</button>
    </div>
  </div>
</div>

<!-- Schedule Shift Modal -->
<div id="shift-modal" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="shift-modal-title">Schedule Shift</span>
      <button class="modal-close" onclick="closeModal('shift-modal')">✕</button>
    </div>
    <div class="modal-body">
      <div id="shift-error" class="alert alert-error" style="display:none"></div>
      <input type="hidden" id="shift-staff-id">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Date</label>
          <input class="form-control" type="date" id="shift-date" min="<?= date('Y-m-d') ?>">
        </div>
        <div class="form-group">
          <label class="form-label">Start Time</label>
          <input class="form-control" type="time" id="shift-start" value="08:00">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">End Time</label>
        <input class="form-control" type="time" id="shift-end" value="17:00">
      </div>
      <div class="form-group">
        <label class="form-label">Notes <small style="font-weight:400;color:var(--text-muted)">(optional)</small></label>
        <input class="form-control" type="text" id="shift-notes" placeholder="e.g. Half day">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('shift-modal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveShift()">Save Shift</button>
    </div>
  </div>
</div>

<?php
$pageScript = <<<JS
function openModal(id)  { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function openShiftModal(staffId, name) {
  document.getElementById('shift-staff-id').value = staffId;
  document.getElementById('shift-modal-title').textContent = 'Schedule Shift — ' + name;
  openModal('shift-modal');
}

async function saveStaff() {
  const btn = document.getElementById('save-staff-btn');
  const err = document.getElementById('staff-error');
  err.style.display = 'none';
  btn.disabled = true;
  try {
    await API.post('/api/staff', {
      full_name:       document.getElementById('s-name').value,
      job_title:       document.getElementById('s-title').value,
      email:           document.getElementById('s-email').value,
      phone:           document.getElementById('s-phone').value,
      password:        document.getElementById('s-password').value,
      hire_date:       document.getElementById('s-hire-date').value || null,
      specialisations: document.getElementById('s-specs').value || null,
    });
    toast('Staff member added!', 'success');
    setTimeout(() => location.reload(), 800);
  } catch(e) {
    err.textContent = e.message; err.style.display = 'block';
    btn.disabled = false;
  }
}

async function saveShift() {
  const err = document.getElementById('shift-error');
  err.style.display = 'none';
  try {
    await API.post('/api/shifts', {
      staff_id:   parseInt(document.getElementById('shift-staff-id').value),
      shift_date: document.getElementById('shift-date').value,
      start_time: document.getElementById('shift-start').value,
      end_time:   document.getElementById('shift-end').value,
      notes:      document.getElementById('shift-notes').value || null,
    });
    toast('Shift scheduled!', 'success');
    closeModal('shift-modal');
  } catch(e) {
    err.textContent = e.message; err.style.display = 'block';
  }
}
JS;
?>
<?php include BASE_PATH . '/app/views/layouts/scripts.php'; ?>
