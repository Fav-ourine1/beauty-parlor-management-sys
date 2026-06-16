<?php
$pageTitle    = 'Inventory';
$pageSubtitle = 'Track product stock levels';
$pageAction   = '<button class="btn btn-primary" onclick="openModal(\'add-product-modal\')">+ Add Product</button>';
?>
<?php include BASE_PATH . '/app/views/layouts/head.php'; ?>
<?php include BASE_PATH . '/app/views/layouts/nav.php'; ?>

<?php if (!empty($lowStock)): ?>
  <div class="alert alert-error" style="margin-bottom:20px">
    ⚠️ <strong><?= count($lowStock) ?> product<?= count($lowStock) !== 1 ? 's' : '' ?></strong> below minimum stock level.
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header">
    <span class="card-title">Products <span style="font-weight:400;color:var(--text-muted);font-size:.85rem">(<?= count($products) ?>)</span></span>
    <button class="btn btn-secondary btn-sm" onclick="openModal('movement-modal')">📦 Record Movement</button>
  </div>
  <div class="card-body" style="padding:0">
    <?php if (empty($products)): ?>
      <div class="empty-state"><div class="empty-icon">📦</div><p>No products added yet.</p></div>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr><th>Product</th><th>Category</th><th>SKU</th><th>Stock</th><th>Min</th><th>Cost</th><th>Status</th><th></th></tr>
          </thead>
          <tbody>
          <?php foreach ($products as $p):
            $isLow = (int)$p['current_stock'] <= (int)$p['low_stock_threshold'];
          ?>
            <tr style="<?= !$p['is_active'] ? 'opacity:.5' : '' ?>">
              <td>
                <strong><?= e($p['name']) ?></strong>
                <?php if (!empty($p['brand'])): ?><br><small style="color:var(--text-muted)"><?= e($p['brand']) ?></small><?php endif; ?>
              </td>
              <td><?= e($p['category_name']) ?></td>
              <td style="font-family:monospace;font-size:.82rem"><?= e($p['sku'] ?? '—') ?></td>
              <td>
                <span class="badge <?= $isLow ? 'badge-low' : 'badge-completed' ?>">
                  <?= (int)$p['current_stock'] ?> <?= e($p['unit']) ?>
                </span>
              </td>
              <td><?= (int)$p['low_stock_threshold'] ?></td>
              <td><?= kes((float)$p['unit_cost']) ?></td>
              <td>
                <span class="badge <?= $p['is_active'] ? 'badge-completed' : 'badge-cancelled' ?>">
                  <?= $p['is_active'] ? 'Active' : 'Inactive' ?>
                </span>
              </td>
              <td>
                <button class="btn btn-secondary btn-sm"
                        onclick="editProduct(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)">Edit</button>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Add/Edit Product Modal -->
<div id="add-product-modal" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title" id="product-modal-title">Add Product</span>
      <button class="modal-close" onclick="closeModal('add-product-modal')">✕</button>
    </div>
    <div class="modal-body">
      <div id="product-error" class="alert alert-error" style="display:none"></div>
      <input type="hidden" id="p-id">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Product Name</label>
          <input class="form-control" type="text" id="p-name" placeholder="e.g. Relaxer Kit">
        </div>
        <div class="form-group">
          <label class="form-label">Brand</label>
          <input class="form-control" type="text" id="p-brand" placeholder="e.g. Dark & Lovely">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Category</label>
          <select class="form-control" id="p-cat">
            <?php foreach ($categories as $cat): ?>
              <option value="<?= (int)$cat['id'] ?>"><?= e($cat['name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Unit</label>
          <input class="form-control" type="text" id="p-unit" placeholder="bottle / piece / ml">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">SKU</label>
          <input class="form-control" type="text" id="p-sku" placeholder="Optional">
        </div>
        <div class="form-group">
          <label class="form-label">Unit Cost (KES)</label>
          <input class="form-control" type="number" id="p-cost" min="0" step="0.01" placeholder="0.00">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Opening Stock</label>
          <input class="form-control" type="number" id="p-stock" min="0" value="0">
        </div>
        <div class="form-group">
          <label class="form-label">Min Stock Level</label>
          <input class="form-control" type="number" id="p-min" min="0" value="5">
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('add-product-modal')">Cancel</button>
      <button class="btn btn-primary" id="save-product-btn" onclick="saveProduct()">Save Product</button>
    </div>
  </div>
</div>

<!-- Record Stock Movement Modal -->
<div id="movement-modal" class="modal-overlay" style="display:none">
  <div class="modal">
    <div class="modal-header">
      <span class="modal-title">Record Stock Movement</span>
      <button class="modal-close" onclick="closeModal('movement-modal')">✕</button>
    </div>
    <div class="modal-body">
      <div id="movement-error" class="alert alert-error" style="display:none"></div>
      <div class="form-group">
        <label class="form-label">Product</label>
        <select class="form-control" id="mv-product">
          <?php foreach ($products as $p): ?>
            <option value="<?= (int)$p['id'] ?>"><?= e($p['name']) ?> (<?= (int)$p['current_stock'] ?> <?= e($p['unit']) ?>)</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">Type</label>
          <select class="form-control" id="mv-type">
            <option value="purchase">Purchase (stock in)</option>
            <option value="usage">Usage (stock out)</option>
            <option value="adjustment">Adjustment</option>
            <option value="waste">Waste</option>
            <option value="return">Return</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Quantity</label>
          <input class="form-control" type="number" id="mv-qty" placeholder="e.g. 10">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">Notes <small style="font-weight:400;color:var(--text-muted)">(optional)</small></label>
        <input class="form-control" type="text" id="mv-notes" placeholder="e.g. Monthly restock">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-secondary" onclick="closeModal('movement-modal')">Cancel</button>
      <button class="btn btn-primary" onclick="saveMovement()">Record</button>
    </div>
  </div>
</div>

<?php
$pageScript = <<<JS
function openModal(id)  { document.getElementById(id).style.display = 'flex'; }
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function editProduct(p) {
  document.getElementById('product-modal-title').textContent = 'Edit Product';
  document.getElementById('p-id').value    = p.id;
  document.getElementById('p-name').value  = p.name;
  document.getElementById('p-brand').value = p.brand || '';
  document.getElementById('p-cat').value   = p.category_id;
  document.getElementById('p-unit').value  = p.unit;
  document.getElementById('p-sku').value   = p.sku || '';
  document.getElementById('p-cost').value  = p.unit_cost;
  document.getElementById('p-min').value   = p.low_stock_threshold;
  document.getElementById('p-stock').style.display = 'none';
  document.querySelector('label[for="p-stock"]') && (document.querySelector('[id="p-stock"]').closest('.form-group').style.display='none');
  openModal('add-product-modal');
}

async function saveProduct() {
  const btn = document.getElementById('save-product-btn');
  const err = document.getElementById('product-error');
  const id  = document.getElementById('p-id').value;
  err.style.display = 'none';
  btn.disabled = true;

  const body = {
    name:                 document.getElementById('p-name').value,
    brand:                document.getElementById('p-brand').value || null,
    category_id:          parseInt(document.getElementById('p-cat').value),
    unit:                 document.getElementById('p-unit').value,
    sku:                  document.getElementById('p-sku').value || null,
    unit_cost:            parseFloat(document.getElementById('p-cost').value) || 0,
    low_stock_threshold:  parseInt(document.getElementById('p-min').value) || 5,
    current_stock:        parseInt(document.getElementById('p-stock').value) || 0,
  };

  try {
    id ? await API.put('/api/products/' + id, body)
       : await API.post('/api/products', body);
    toast(id ? 'Product updated!' : 'Product added!', 'success');
    setTimeout(() => location.reload(), 800);
  } catch(e) {
    err.textContent = e.message; err.style.display = 'block';
    btn.disabled = false;
  }
}

async function saveMovement() {
  const err = document.getElementById('movement-error');
  err.style.display = 'none';
  const type = document.getElementById('mv-type').value;
  let qty    = parseInt(document.getElementById('mv-qty').value);
  if (['usage','waste'].includes(type)) qty = -Math.abs(qty);
  else qty = Math.abs(qty);

  try {
    await API.post('/api/stock-movements', {
      product_id:      parseInt(document.getElementById('mv-product').value),
      movement_type:   type,
      quantity_change: qty,
      notes:           document.getElementById('mv-notes').value || null,
    });
    toast('Stock movement recorded!', 'success');
    setTimeout(() => location.reload(), 800);
  } catch(e) {
    err.textContent = e.message; err.style.display = 'block';
  }
}
JS;
?>
<?php include BASE_PATH . '/app/views/layouts/scripts.php'; ?>
