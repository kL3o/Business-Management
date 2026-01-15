<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();
$user = current_user($mysqli);
$brand = get_branding($mysqli, (int)$user['id']);

// Ensure schema exists and fetch workers
ensure_workers_schema($mysqli);
$monthly_workers = get_workers($mysqli, (int)$user['id'], 'monthly', 500);
$daily_workers = get_workers($mysqli, (int)$user['id'], 'daily', 500);

// Recent logs
$recent_absences = get_recent_worker_absences($mysqli, (int)$user['id'], 50);
$recent_payments = get_recent_worker_payments($mysqli, (int)$user['id'], 50);

$success = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$page_title = 'Workers';
require_once __DIR__ . '/includes/header.php';

// Display flash messages
if ($success || $error) {
    echo '<div class="row"><div class="col-12">';
    if ($success) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
    if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
    echo '</div></div>';
}
?>

  <div class="row g-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><?php _e('Register Worker'); ?></h5>
          <form class="row gy-2" method="post" action="<?= base_url('save_worker.php'); ?>">
            <div class="col-12 col-md-4">
              <label class="form-label"><?php echo htmlspecialchars(__t('Full Name') ?: 'Full Name'); ?></label>
              <input type="text" class="form-control" name="name" required>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label"><?php _e('Worker Type'); ?></label>
              <select class="form-select" name="worker_type" id="worker_type" required>
                <option value="monthly"><?php _e('Monthly'); ?></option>
                <option value="daily"><?php _e('Daily'); ?></option>
              </select>
            </div>
            <div class="col-12 col-md-4" id="monthly_salary_group">
              <label class="form-label"><?php _e('Monthly Salary'); ?></label>
              <input type="number" step="0.01" min="0" class="form-control" name="monthly_salary" placeholder="0.00">
            </div>
            <div class="col-12 col-md-4" id="daily_rate_group">
              <label class="form-label"><?php _e('Daily Rate'); ?></label>
              <input type="number" step="0.01" min="0" class="form-control" name="daily_rate" placeholder="0.00">
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit"><?php _e('Add Worker'); ?></button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><?php _e('Monthly Workers'); ?></h5>
          <div class="table-responsive">
            <table class="table table-striped mb-0">
              <thead>
                <tr>
                  <th><?php echo htmlspecialchars(__t('Full Name') ?: 'Full Name'); ?></th>
                  <th class="text-end"><?php _e('Monthly Salary'); ?></th>
                  <th class="text-end"><?php echo htmlspecialchars(__t('Actions') ?: 'Actions'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($monthly_workers)): ?>
                  <tr><td colspan="3" class="text-center text-muted"><?php echo htmlspecialchars(__t('No monthly workers yet') ?: 'No monthly workers yet'); ?></td></tr>
                <?php else: ?>
                  <?php foreach ($monthly_workers as $worker): ?>
                    <tr>
                      <td><?= htmlspecialchars($worker['name']); ?></td>
                      <td class="text-end" data-currency-amount="<?= (float)$worker['monthly_salary']; ?>"><?= format_currency((float)$worker['monthly_salary']); ?></td>
                      <td class="text-end">
                        <form method="post" action="<?= base_url('delete_worker.php'); ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this worker? This action cannot be undone.');">
                          <input type="hidden" name="worker_id" value="<?= $worker['id']; ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><?php _e('Daily Workers'); ?></h5>
          <div class="table-responsive">
            <table class="table table-striped mb-0">
              <thead>
                <tr>
                  <th><?php echo htmlspecialchars(__t('Full Name') ?: 'Full Name'); ?></th>
                  <th class="text-end"><?php _e('Daily Rate'); ?></th>
                  <th class="text-end"><?php echo htmlspecialchars(__t('Actions') ?: 'Actions'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($daily_workers)): ?>
                  <tr><td colspan="3" class="text-center text-muted"><?php echo htmlspecialchars(__t('No daily workers yet') ?: 'No daily workers yet'); ?></td></tr>
                <?php else: ?>
                  <?php foreach ($daily_workers as $worker): ?>
                    <tr>
                      <td><?= htmlspecialchars($worker['name']); ?></td>
                      <td class="text-end" data-currency-amount="<?= (float)$worker['daily_rate']; ?>"><?= format_currency((float)$worker['daily_rate']); ?></td>
                      <td class="text-end">
                        <form method="post" action="<?= base_url('delete_worker.php'); ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this worker? This action cannot be undone.');">
                          <input type="hidden" name="worker_id" value="<?= $worker['id']; ?>">
                          <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><?php _e('Record Absence'); ?></h5>
          <form class="row gy-2" method="post" action="<?= base_url('save_absence.php'); ?>">
            <div class="col-12">
              <label class="form-label"><?php echo htmlspecialchars(__t('Worker') ?: 'Worker'); ?></label>
              <select class="form-select" name="worker_id" required>
                <option value="" disabled selected><?php echo htmlspecialchars(__t('Select worker') ?: 'Select worker'); ?></option>
                <?php foreach (array_merge($monthly_workers, $daily_workers) as $w): ?>
                  <option value="<?= (int)$w['id']; ?>"><?= htmlspecialchars($w['name']); ?> (<?= htmlspecialchars($w['worker_type']); ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label"><?php echo htmlspecialchars(__t('Start Date') ?: 'Start Date'); ?></label>
              <input type="date" class="form-control" name="start_date" value="<?= date('Y-m-d'); ?>" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label"><?php echo htmlspecialchars(__t('End Date') ?: 'End Date'); ?></label>
              <input type="date" class="form-control" name="end_date" placeholder="Optional (same as start if empty)">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label"><?php echo htmlspecialchars(__t('Reason') ?: 'Reason'); ?></label>
              <select class="form-select" name="reason_type">
                <option value="holiday"><?php echo htmlspecialchars(__t('Holiday') ?: 'Holiday'); ?></option>
                <option value="health"><?php echo htmlspecialchars(__t('Health') ?: 'Health'); ?></option>
                <option value="other" selected><?php echo htmlspecialchars(__t('Other') ?: 'Other'); ?></option>
              </select>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label"><?php _e('Note'); ?></label>
              <input type="text" class="form-control" name="reason_note" placeholder="Optional details">
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit"><?php echo htmlspecialchars(__t('Save Absence') ?: 'Save Absence'); ?></button>
            </div>
          </form>
          <hr>
          <h6 class="mt-3"><?php _e('Recent Absences'); ?></h6>
          <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
              <thead>
                <tr>
                  <th><?php echo htmlspecialchars(__t('Worker') ?: 'Worker'); ?></th>
                  <th><?php echo htmlspecialchars(__t('From') ?: 'From'); ?></th>
                  <th><?php echo htmlspecialchars(__t('To') ?: 'To'); ?></th>
                  <th><?php echo htmlspecialchars(__t('Reason') ?: 'Reason'); ?></th>
                  <th><?php _e('Note'); ?></th>
                  <th><?php echo htmlspecialchars(__t('Created') ?: 'Created'); ?></th>
                  <th><?php echo htmlspecialchars(__t('Actions') ?: 'Actions'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$recent_absences): ?>
                  <tr><td colspan="7" class="text-center text-muted"><?php echo htmlspecialchars(__t('No absences yet') ?: 'No absences yet'); ?></td></tr>
                <?php else: foreach ($recent_absences as $a): ?>
                  <tr>
                    <td><?= htmlspecialchars($a['worker_name']); ?></td>
                    <td><?= htmlspecialchars($a['start_date']); ?></td>
                    <td><?= htmlspecialchars($a['end_date']); ?></td>
                    <td><?= htmlspecialchars(ucfirst($a['reason_type'])); ?></td>
                    <td><?= htmlspecialchars($a['reason_note'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($a['created_at']); ?></td>
                    <td class="text-nowrap">
                      <form method="post" action="<?= base_url('delete_absence.php'); ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this absence record? This action cannot be undone.');">
                        <input type="hidden" name="absence_id" value="<?= $a['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
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

    <div class="col-12 col-lg-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><?php _e('Record Payment'); ?></h5>
          <form class="row gy-2" method="post" action="<?= base_url('save_payment.php'); ?>">
            <div class="col-12">
              <label class="form-label">Worker</label>
              <select class="form-select" name="worker_id" required>
                <option value="" disabled selected>Select worker</option>
                <?php foreach (array_merge($monthly_workers, $daily_workers) as $w): ?>
                  <option value="<?= (int)$w['id']; ?>"><?= htmlspecialchars($w['name']); ?> (<?= htmlspecialchars($w['worker_type']); ?>)</option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label"><?php _e('Amount'); ?></label>
              <input type="number" step="0.01" min="0" class="form-control" name="amount" required>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label"><?php _e('Paid On'); ?></label>
              <input type="date" class="form-control" name="paid_on" value="<?= date('Y-m-d'); ?>" required>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label">Note</label>
              <input type="text" class="form-control" name="note" placeholder="Optional">
            </div>
            <div class="col-12">
              <small class="text-muted"><?php echo htmlspecialchars(__t('Optional period (useful for monthly salaries)') ?: 'Optional period (useful for monthly salaries)'); ?></small>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label"><?php echo htmlspecialchars(__t('Period Start') ?: 'Period Start'); ?></label>
              <input type="date" class="form-control" name="period_start">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label"><?php echo htmlspecialchars(__t('Period End') ?: 'Period End'); ?></label>
              <input type="date" class="form-control" name="period_end">
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit"><?php _e('Save Payment'); ?></button>
            </div>
          </form>
          <hr>
          <h6 class="mt-3"><?php _e('Recent Payments'); ?></h6>
          <div class="table-responsive">
            <table class="table table-sm table-striped mb-0">
              <thead>
                <tr>
                  <th><?php echo htmlspecialchars(__t('Worker') ?: 'Worker'); ?></th>
                  <th class="text-end"><?php _e('Amount'); ?></th>
                  <th><?php _e('Paid On'); ?></th>
                  <th><?php echo htmlspecialchars(__t('Period') ?: 'Period'); ?></th>
                  <th><?php _e('Note'); ?></th>
                  <th><?php echo htmlspecialchars(__t('Created') ?: 'Created'); ?></th>
                  <th><?php echo htmlspecialchars(__t('Actions') ?: 'Actions'); ?></th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$recent_payments): ?>
                  <tr><td colspan="7" class="text-center text-muted"><?php echo htmlspecialchars(__t('No payments yet') ?: 'No payments yet'); ?></td></tr>
                <?php else: foreach ($recent_payments as $p): ?>
                  <tr>
                    <td><?= htmlspecialchars($p['worker_name']); ?></td>
                    <td class="text-end" data-currency-amount="<?= (float)$p['amount']; ?>"><?= format_currency((float)$p['amount']); ?></td>
                    <td><?= htmlspecialchars($p['paid_on']); ?></td>
                    <td><?= htmlspecialchars(($p['period_start']??'') . ($p['period_start'] && $p['period_end'] ? ' to ' : '') . ($p['period_end']??'')); ?></td>
                    <td><?= htmlspecialchars($p['note'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($p['created_at']); ?></td>
                    <td class="text-nowrap">
                      <form method="post" action="<?= base_url('delete_payment.php'); ?>" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this payment record? This will also remove the corresponding expense transaction.');">
                        <input type="hidden" name="payment_id" value="<?= $p['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
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
  </div>
<script>
// Handle worker type toggle
(function(){
  const typeSelect = document.getElementById('worker_type');
  const monthlyGroup = document.getElementById('monthly_salary_group');
  const dailyGroup = document.getElementById('daily_rate_group');

  function updateFormFields() {
    if (!(typeSelect && monthlyGroup && dailyGroup)) return;
    const monthlyInput = document.querySelector('[name="monthly_salary"]');
    const dailyInput = document.querySelector('[name="daily_rate"]');
    if (typeSelect.value === 'monthly') {
      monthlyGroup.classList.remove('d-none');
      dailyGroup.classList.add('d-none');
      if (monthlyInput) monthlyInput.required = true;
      if (dailyInput) dailyInput.required = false;
    } else {
      monthlyGroup.classList.add('d-none');
      dailyGroup.classList.remove('d-none');
      if (monthlyInput) monthlyInput.required = false;
      if (dailyInput) dailyInput.required = true;
    }
  }

  if (typeSelect) {
    typeSelect.addEventListener('change', updateFormFields);
    updateFormFields(); // Initialize
  }
})();

// Format currency inputs
function formatCurrencyInput(input) {
  input.addEventListener('blur', function() {
    const value = parseFloat(this.value);
    if (!isNaN(value)) {
      this.value = value.toFixed(2);
    }
  });
}

document.addEventListener('DOMContentLoaded', function() {
  // Format currency inputs
  document.querySelectorAll('input[type="number"][step="0.01"]').forEach(formatCurrencyInput);
  
  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
