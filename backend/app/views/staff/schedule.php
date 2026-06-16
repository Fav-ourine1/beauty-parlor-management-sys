<?php
$pageTitle    = 'My Schedule';
$pageSubtitle = 'All upcoming shifts';
?>
<?php include BASE_PATH . '/app/views/layouts/head.php'; ?>
<?php include BASE_PATH . '/app/views/layouts/nav.php'; ?>

<div class="card">
  <div class="card-header">
    <span class="card-title">📅 Upcoming Shifts</span>
  </div>
  <div class="card-body">
    <?php if (empty($shifts)): ?>
      <div class="empty-state">
        <div class="empty-icon">📭</div>
        <p>No shifts scheduled yet. Your manager will add them here.</p>
      </div>
    <?php else: ?>
      <div class="shift-list">
        <?php foreach ($shifts as $s):
          $dt      = new DateTimeImmutable($s['shift_date']);
          $isToday = $s['shift_date'] === date('Y-m-d');
          $isPast  = $s['shift_date'] < date('Y-m-d');
        ?>
          <div class="shift-card" style="<?= $isToday ? 'border-color:var(--primary);background:var(--primary-light)' : ($isPast ? 'opacity:.6' : '') ?>">
            <div class="shift-date-box">
              <div class="day"><?= $dt->format('d') ?></div>
              <div class="month"><?= $dt->format('M') ?></div>
              <div style="font-size:.68rem;color:var(--text-muted)"><?= $dt->format('D') ?></div>
            </div>
            <div class="shift-info" style="flex:1">
              <div class="shift-time">
                <?= e(substr($s['start_time'],0,5)) ?> – <?= e(substr($s['end_time'],0,5)) ?>
                <?php if ($isToday): ?><span class="badge badge-confirmed" style="margin-left:8px">Today</span><?php endif; ?>
                <?php if ($isPast):  ?><span class="badge badge-no_show"   style="margin-left:8px">Past</span><?php endif; ?>
              </div>
              <?php if (!empty($s['notes'])): ?>
                <div class="shift-notes"><?= e($s['notes']) ?></div>
              <?php endif; ?>
            </div>
            <div style="font-size:.8rem;color:var(--text-muted)">
              <?php
                $start = new DateTimeImmutable($s['shift_date'] . ' ' . $s['start_time']);
                $end   = new DateTimeImmutable($s['shift_date'] . ' ' . $s['end_time']);
                $hours = $start->diff($end)->h + ($start->diff($end)->i / 60);
                echo number_format($hours, 1) . ' hrs';
              ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<?php include BASE_PATH . '/app/views/layouts/scripts.php'; ?>
