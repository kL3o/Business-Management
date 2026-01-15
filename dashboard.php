<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();
$user = current_user($mysqli);
$brand = get_branding($mysqli, (int)$user['id']);
$success = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$year = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$summary = get_monthly_summary($mysqli, (int)$user['id'], $year, $month);
$recent = get_transactions_in_month($mysqli, (int)$user['id'], $year, $month, 100);

$page_title = __t('Dashboard');
require_once __DIR__ . '/includes/header.php';
?>
  <div class="row g-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <form class="row gy-2 align-items-end" method="get">
            <div class="col-6 col-md-3">
              <label class="form-label"><?php _e('Month'); ?></label>
              <select class="form-select" name="month">
                <?php for($m=1;$m<=12;$m++): ?>
                  <?php $mn = date('F', mktime(0,0,0,$m,1)); ?>
                  <option value="<?= $m; ?>" <?= $m===$month?'selected':''; ?>><?= htmlspecialchars(__t($mn)); ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col-6 col-md-3">
              <label class="form-label"><?php _e('Year'); ?></label>
              <select class="form-select" name="year">
                <?php $cy=(int)date('Y'); for($y=$cy-5;$y<=$cy+5;$y++): ?>
                  <option value="<?= $y; ?>" <?= $y===$year?'selected':''; ?>><?= $y; ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="col-12 col-md-3">
              <button class="btn btn-primary" type="submit"><?php _e('Apply'); ?></button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><?php _e('Quick Stats'); ?></h5>
          <p class="card-text"><?php _e('Summary for'); ?> <?= htmlspecialchars(__t(date('F', mktime(0,0,0,$month,1)))); ?> <?= $year; ?>:</p>
          <ul class="mb-0">
            <li><?php _e('Total Income'); ?>: <div class="h4 mb-0"><?= format_currency($summary['income'] ?? 0); ?></div></li>
            <li><?php _e('Total Expenses'); ?>: <div class="h4 mb-0"><?= format_currency($summary['expense'] ?? 0); ?></div></li>
            <li><?php _e('Net Cash Flow'); ?>: <div class="h4 mb-0"><?= format_currency($summary['net'] ?? 0); ?></div></li>
          </ul>
        </div>
      </div>
    </div>
    <div class="col-12 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><?php _e('Add Transaction'); ?></h5>
          <form class="row gy-2" method="post" action="<?= base_url('save_transaction.php'); ?>">
            <div class="col-12 col-md-4">
              <label class="form-label"><?php _e('Type'); ?></label>
              <select class="form-select" name="type" required>
                <option value="income"><?php _e('Income'); ?></option>
                <option value="expense"><?php _e('Expense'); ?></option>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label"><?php _e('Category'); ?></label>
              <input type="text" class="form-control" name="category" placeholder="<?= htmlspecialchars(__t('Sales, Rent, etc.')); ?>" required>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label"><?php _e('Amount'); ?></label>
              <input type="number" step="0.01" min="0" class="form-control" name="amount" placeholder="0.00" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label"><?php _e('Date'); ?></label>
              <input type="date" class="form-control" name="occurred_on" value="<?= date('Y-m-d'); ?>" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label"><?php _e('Description'); ?></label>
              <input type="text" class="form-control" name="description" placeholder="<?= htmlspecialchars(__t('Optional details')); ?>">
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit"><?php _e('Save Transaction'); ?></button>
            </div>
          </form>
        </div>
      </div>
    </div>
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><?php _e('Transactions for'); ?> <?= htmlspecialchars(__t(date('F', mktime(0,0,0,$month,1)))); ?> <?= $year; ?></h5>
          <div class="table-responsive">
            <table class="table table-striped mb-0">
              <thead>
                <tr>
                  <th><?php _e('Date'); ?></th>
                  <th><?php _e('Type'); ?></th>
                  <th><?php _e('Category'); ?></th>
                  <th class="text-end"><?php _e('Amount'); ?></th>
                  <th><?php _e('Description'); ?></th>
                  <th class="text-end"><?php _e('Actions'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$recent): ?>
                  <tr><td colspan="6" class="text-center text-muted"><?= htmlspecialchars(__t('No transactions yet')); ?></td></tr>
                <?php else: foreach ($recent as $t): ?>
                  <tr>
                      <td style="min-width:130px;"><?= htmlspecialchars($t['occurred_on']); ?></td>
                      <td style="min-width:120px;"><span class="badge bg-<?= $t['type']==='income'?'success':'danger'; ?>"><?= htmlspecialchars(__t($t['type']==='income'?'Income':'Expense')); ?></span></td>
                      <td style="min-width:150px;"><?= htmlspecialchars($t['category']); ?></td>
                      <td class="text-end" style="min-width:140px;" data-currency-amount=<?= (float)$t['amount']; ?>><?= format_currency((float)$t['amount']); ?></td>
                      <td style="min-width:200px;"><?= htmlspecialchars($t['description'] ?? ''); ?></td>
                      <td class="text-end" style="min-width:160px;">
                        <a href="<?= base_url('edit_transaction.php?id=' . (int)$t['id']); ?>" class="btn btn-sm btn-secondary"><?php _e('Edit'); ?></a>
                        <form method="post" action="<?= base_url('delete_transaction.php'); ?>" class="d-inline" onsubmit="return confirm('<?= htmlspecialchars(__t('Delete this transaction?')); ?>');">
                          <input type="hidden" name="id" value="<?= (int)$t['id']; ?>">
                          <button type="submit" class="btn btn-sm btn-danger"><?php _e('Delete'); ?></button>
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
<?php require_once __DIR__ . '/includes/footer.php'; ?>
