<?php
$pageTitle    = 'Services';
$pageSubtitle = 'Manage the services catalogue';
$pageAction   = '<button class="btn btn-primary" onclick="openModal(\'add-service-modal\')">+ Add Service</button>';

// Group services by category
$grouped = [];
foreach ($services as $svc) {
    $grouped[$svc['category_name']][] = $svc;
}
?>
<?php include BASE_PATH . '/app/views/layouts/head.php'; ?>
<?php include BASE_PATH . '/app/views/layouts/nav.php'; ?>

<?php if (empty($services)): ?>
  <div class="empty-state card" style="padding:40px">
    <div class="empty-icon">✂️</div>
    <p>No services yet. Add your first service to get started.</p>
  </div>
<?php else: ?>
  <?php foreach ($grouped as $catName => $svcs): ?>
    <div class="card" style="margin-bottom:22px">
      <div class="card-header">
        <span class="card-title"><?= e($catName) ?></span>
        <span style="font-size:.8rem;color:var(--text-muted)"><?= count($svcs) ?> service<?= count($svcs) !== 1 ? 's' : '' ?></span>
      </div>
      <div class="card-body" style="padding:0">
        <div class="table-wrap">
          <table>
            <thead>
              <tr><th>Service</th><th>Duration</th><th>Price (KES)</th><th>Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php foreach ($svcs as $svc): ?>
              <tr style="<?= !$svc['is_active'] ? 'opacity:.5' : '' ?>">
                <td>
                  <strong><?= e($svc['name']) ?></strong>
                  <?php if (!empty($svc['description'])): ?>
                    <br><small style="color:var(--text-muted)"><?= e($svc['description']) ?></small>
                  <?php endif; ?>
                </td>
                <td><?= (int)$svc['duration_mins'] ?> min</td>
                <td><?= kes((float)$svc['price']) ?></td>
                <td>
                  <span class="badge <?= $svc['is_active'] ? 'badge-completed' : 'badge-cancelled' ?>">
                    <?= $svc['is_active'] ? 'Active' : 'Inactive' ?>
                  </span>
                </td>
                <td style="white-space:nowrap">
                  <button class="btn btn-secondary btn-sm"
                          onclick="editService(<?= htmlspecialchars(json_encode($svc), ENT_QUOTES) ?>)">Edit</button>
                  <?php if ($svc['is_active']): ?>
                    <button class="btn btn-danger btn-sm"
                            onclick="toggleService(<?= $svc['id'] ?>, 0, this)">Deactivate</button>
                  <?php else: ?>
                    <button class="btn btn-success btn-sm"
                            onclick="toggleService(<?= $svc['id'] ?>, 1, this)">Activate</button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<!-- Add/Edit Service Modal -->
<div id="add-service-modal" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="service-modal-title">Add Service</span>
      <button class="modal-close" onclick="closeModal('add-service-modal')">✕</button>
    </div>
    <div class="modal-body">
      <div id="service-error" class="alert alert-error" style="display:none"></div>
      <input type="hidden" id="svc-id">
      <div class="form-group">
        <label class="form-label">Category</label>
        <select class="form-control" id="svc-cat">
          <?php foreach ($categories as $cat): ?>
            <option value="<?= (int)$cat['id'] ?>"><?= e($cat['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Service Name</label>
        <input class="form-control" type="text" id="svc-name" placeholder="e.g. Box Braids">
      </div>
      <div class="form-group">
        <label class="form-label">Description <small style="font-weight:400;color:var(--text-muted)">(optional)</small></label>
        <textarea class="form-control" id="svc-desc" rows="2" placeholder="Short description…"></textarea>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Price (KES)</label>
          <input class="form-control" type="number" id="svc-price" min="0" step="0.01" placeholder="0.00">
        </div>
        <div class="form-group">
          <label class="form-label">Duration (minutes)</label>
          <input class="form-control" type="number" id="svc-duration" min="5" step="5" placeholder="60">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('add-service-modal')">Cancel</button>
      <button class="btn btn-primary" id="save-svc-btn" onclick="saveService()">Save Service</button>
    </div>
  </div>
</div>

<?php
$pageScript = <<<JS
function openModal(id)  { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function editService(svc) {
  document.getElementById('service-modal-title').textContent = 'Edit Service';
  document.getElementById('svc-id').value       = svc.id;
  document.getElementById('svc-cat').value      = svc.category_id;
  document.getElementById('svc-name').value     = svc.name;
  document.getElementById('svc-desc').value     = svc.description || '';
  document.getElementById('svc-price').value    = svc.price;
  document.getElementById('svc-duration').value = svc.duration_mins;
  openModal('add-service-modal');
}

async function saveService() {
  const btn = document.getElementById('save-svc-btn');
  const err = document.getElementById('service-error');
  const id  = document.getElementById('svc-id').value;
  err.style.display = 'none';
  btn.disabled = true;

  const body = {
    category_id:   parseInt(document.getElementById('svc-cat').value),
    name:          document.getElementById('svc-name').value,
    description:   document.getElementById('svc-desc').value || null,
    price:         parseFloat(document.getElementById('svc-price').value),
    duration_mins: parseInt(document.getElementById('svc-duration').value),
  };

  try {
    id ? await API.put('/api/services/' + id, body)
       : await API.post('/api/services', body);
    toast(id ? 'Service updated!' : 'Service added!', 'success');
    setTimeout(() => location.reload(), 800);
  } catch(e) {
    err.textContent = e.message; err.style.display = 'block';
    btn.disabled = false;
  }
}

async function toggleService(id, active, btn) {
  btn.disabled = true;
  try {
    active ? await API.put('/api/services/' + id, { is_active: 1 })
           : await API.delete('/api/services/' + id);
    toast('Service updated!', 'success');
    setTimeout(() => location.reload(), 600);
  } catch(e) {
    toast(e.message, 'error');
    btn.disabled = false;
  }
}
JS;
?>
<?php include BASE_PATH . '/app/views/layouts/scripts.php'; ?>
