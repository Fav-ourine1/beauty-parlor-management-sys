  </div><!-- /page-body -->

  <?php
    $currentRole = $_SESSION['user']['role'] ?? null;
    if ($currentRole === 'client'):
  ?>
  <?php include BASE_PATH . '/app/views/layouts/footer.php'; ?>
  <?php endif; ?>

</div><!-- /main-content -->
</div><!-- /app-shell -->
<div id="toast-container"></div>
<script src="<?= APP_URL ?>/js/app.js"></script>
<?php if (!empty($pageScript)): ?>
<script><?= $pageScript ?></script>
<?php endif; ?>
</body>
</html>
