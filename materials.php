<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();
$user = current_user($mysqli);
$brand = get_branding($mysqli, (int)$user['id']);
$success = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$materials = get_material_purchases($mysqli, (int)$user['id'], 200);
$totals = get_material_totals($mysqli, (int)$user['id']);

$page_title = __t('Materials');
require_once __DIR__ . '/includes/header.php';
?>
<div class="no-print">
  <?php if ($success): ?><div class="alert alert-success"><?= htmlspecialchars($success); ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error); ?></div><?php endif; ?>
</div>

  <div class="row g-4">
    <div class="col-12">
      <div class="card shadow-sm no-print">
        <div class="card-body">
          <h5 class="card-title"><?php _e('Add Material Purchase'); ?></h5>
          <form class="row gy-2" method="post" action="<?= base_url('save_material.php'); ?>">
            <div class="col-12 col-md-4">
              <label class="form-label"><?php _e('Material'); ?></label>
              <input type="text" class="form-control" name="material_name" placeholder="<?= htmlspecialchars(__t('e.g., Steel Rods')); ?>" required>
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label"><?php _e('Unit'); ?></label>
              <input type="text" class="form-control" name="unit" placeholder="<?= htmlspecialchars(__t('pcs, kg, m')); ?>" value="pcs" required>
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label"><?php _e('Quantity'); ?></label>
              <input type="number" step="0.001" min="0" class="form-control" name="quantity" placeholder="0.000" required>
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label"><?php _e('Unit Price'); ?></label>
              <input type="number" step="0.0001" min="0" class="form-control" name="unit_price" placeholder="0.0000" required>
            </div>
            <div class="col-6 col-md-2">
              <label class="form-label"><?php _e('Date'); ?></label>
              <input type="date" class="form-control" name="purchased_on" value="<?= date('Y-m-d'); ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label"><?php _e('Description'); ?></label>
              <input type="text" class="form-control" name="description" placeholder="<?= htmlspecialchars(__t('Optional details')); ?>">
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit"><?php _e('Save Material'); ?></button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3"><?php _e('Materials Purchases'); ?></h5>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th><?php _e('Date'); ?></th>
                  <th><?php _e('Material'); ?></th>
                  <th><?php _e('Unit'); ?></th>
                  <th class="text-end"><?php _e('Quantity'); ?></th>
                  <th class="text-end"><?php _e('Unit Price'); ?></th>
                  <th class="text-end"><?php _e('Total'); ?></th>
                  <th><?php _e('Description'); ?></th>
                  <th class="text-end"><?php _e('Actions'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$materials): ?>
                  <tr><td colspan="8" class="text-center text-muted"><?= htmlspecialchars(__t('No materials recorded')); ?></td></tr>
                <?php else: foreach ($materials as $m): ?>
                  <tr>
                    <td><?= htmlspecialchars($m['purchased_on']); ?></td>
                    <td><?= htmlspecialchars($m['material_name']); ?></td>
                    <td><?= htmlspecialchars($m['unit']); ?></td>
                    <td class="text-end"><?= number_format((float)$m['quantity'], 3); ?></td>
                    <td class="text-end" data-currency-amount="<?= (float)$m['unit_price']; ?>"><?= format_currency((float)$m['unit_price']); ?></td>
                    <td class="text-end" data-currency-amount="<?= (float)$m['total']; ?>"><?= format_currency((float)$m['total']); ?></td>
                    <td><?= htmlspecialchars($m['description'] ?? ''); ?></td>
                    <td class="text-end text-nowrap">
                      <form method="post" action="<?= base_url('delete_material.php'); ?>" class="d-inline" onsubmit="return confirm('<?= htmlspecialchars(__t('Delete this material purchase? This will also remove the corresponding expense transaction.')); ?>');">
                        <input type="hidden" name="id" value="<?= (int)$m['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"><?php _e('Delete'); ?></button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title mb-3"><?php _e('Totals by Material'); ?></h5>
          <div class="table-responsive">
            <table class="table table-sm table-bordered">
              <thead>
                <tr>
                  <th><?php _e('Material'); ?></th>
                  <th><?php _e('Unit'); ?></th>
                  <th class="text-end"><?php _e('Total Quantity'); ?></th>
                  <th class="text-end"><?php _e('Total Cost'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$totals): ?>
                  <tr><td colspan="4" class="text-center text-muted"><?= htmlspecialchars(__t('No totals yet')); ?></td></tr>
                <?php else: foreach ($totals as $t): ?>
                  <tr>
                    <td><?= htmlspecialchars($t['material_name']); ?></td>
                    <td><?= htmlspecialchars($t['unit']); ?></td>
                    <td class="text-end"><?= number_format((float)$t['total_qty'], 3); ?></td>
                    <td class="text-end" data-currency-amount="<?= (float)$t['total_cost']; ?>"><?= format_currency((float)$t['total_cost']); ?></td>
                  </tr>
                <?php endforeach; endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
</div>

<script>
// Add print button functionality
const printButton = document.getElementById('printButton');
if (printButton) {
    printButton.addEventListener('click', () => window.print());
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
