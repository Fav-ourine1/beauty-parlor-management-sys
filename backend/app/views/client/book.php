<?php
$pageTitle  = 'Book an Appointment';
$pageSubtitle = 'Select your services and preferred time';
?>
<?php include BASE_PATH . '/app/views/layouts/head.php'; ?>
<?php include BASE_PATH . '/app/views/layouts/nav.php'; ?>

<div style="max-width:720px">

  <div id="booking-error" class="alert alert-error" style="display:none"></div>

  <form id="booking-form" novalidate>

    <!-- Step 1: Services -->
    <div class="card" style="margin-bottom:22px">
      <div class="card-header">
        <span class="card-title">✂️ Choose Services</span>
      </div>
      <div class="card-body">
        <?php if (empty($services)): ?>
          <p style="color:var(--text-muted)">No services available at the moment.</p>
        <?php else: ?>
          <?php
            // Group by category
            $grouped = [];
            foreach ($services as $svc) {
              $grouped[$svc['category_name']][] = $svc;
            }
          ?>
          <?php foreach ($grouped as $catName => $svcs): ?>
            <p style="font-weight:700;font-size:.82rem;color:var(--text-muted);text-transform:uppercase;letter-spacing:.06em;margin:14px 0 8px"><?= e($catName) ?></p>
            <div class="service-list">
              <?php foreach ($svcs as $svc): ?>
                <div class="service-item"
                     data-id="<?= (int)$svc['id'] ?>"
                     data-price="<?= (float)$svc['price'] ?>">
                  <input type="checkbox" name="service_ids[]" value="<?= (int)$svc['id'] ?>">
                  <div class="service-info">
                    <div class="service-name"><?= e($svc['name']) ?></div>
                    <div class="service-meta"><?= (int)$svc['duration_mins'] ?> min
                      <?php if (!empty($svc['description'])): ?> · <?= e($svc['description']) ?><?php endif; ?>
                    </div>
                  </div>
                  <div class="service-price"><?= kes((float)$svc['price']) ?></div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endforeach; ?>

          <!-- Booking summary -->
          <div id="booking-summary" class="booking-summary" style="display:none">
            <div class="total-label">Estimated Total</div>
            <div class="total-amount" id="booking-total">KES 0.00</div>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Step 2: Date & Time -->
    <div class="card" style="margin-bottom:22px">
      <div class="card-header">
        <span class="card-title">🕐 Choose Date & Time</span>
      </div>
      <div class="card-body">
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" for="appointment_date">Date</label>
            <input class="form-control" type="date" id="appointment_date" name="appointment_date"
                   min="<?= date('Y-m-d') ?>" required>
          </div>
          <div class="form-group">
            <label class="form-label" for="start_time">Start time</label>
            <input class="form-control" type="time" id="start_time" name="start_time"
                   min="08:00" max="18:00" required>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" for="end_time">End time</label>
          <input class="form-control" type="time" id="end_time" name="end_time"
                 min="08:00" max="20:00" required>
        </div>
        <div class="form-group">
          <label class="form-label" for="notes">Special requests <span style="font-weight:400;color:var(--text-muted)">(optional)</span></label>
          <textarea class="form-control" id="notes" name="notes"
                    placeholder="Any allergies, preferences, or special requests…"></textarea>
        </div>
      </div>
    </div>

    <button class="btn btn-primary btn-lg btn-block" type="submit">
      Book Appointment
    </button>
  </form>

</div>

<?php include BASE_PATH . '/app/views/layouts/scripts.php'; ?>
