<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();
$user = current_user($mysqli);
$brand = get_branding($mysqli, (int)$user['id']);

// Get user preferences
$prefs = get_user_preferences($mysqli, (int)$user['id']);
$theme = $prefs['theme'] ?? 'light';
$currency = $prefs['currency'] ?? 'EUR';

// Store in session for easy access
$_SESSION['user_prefs'] = $prefs;

// Filters
$from = isset($_GET['from']) ? sanitize($_GET['from']) : date('Y-m-01');
$to = isset($_GET['to']) ? sanitize($_GET['to']) : date('Y-m-t');

// Fetch transactions in range for ACTIVE partner only
$partner = current_partner_id();
if ($partner) {
    $sql = 'SELECT occurred_on, type, category, amount, description FROM transactions WHERE user_id = ? AND partner_id = ? AND occurred_on >= ? AND occurred_on <= ? ORDER BY occurred_on ASC, id ASC';
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iiss', $user['id'], $partner, $from, $to);
} else {
    $sql = 'SELECT occurred_on, type, category, amount, description FROM transactions WHERE user_id = ? AND partner_id IS NULL AND occurred_on >= ? AND occurred_on <= ? ORDER BY occurred_on ASC, id ASC';
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param('iss', $user['id'], $from, $to);
}
$stmt->execute();
$res = $stmt->get_result();
$rows = $res->fetch_all(MYSQLI_ASSOC);

$income = 0.0; $expense = 0.0;
foreach ($rows as $r) {
    if ($r['type'] === 'income') $income += (float)$r['amount'];
    if ($r['type'] === 'expense') $expense += (float)$r['amount'];
}
$net = $income - $expense;

$page_title = 'Reports';
require_once __DIR__ . '/includes/header.php';
?>

<style>
  @media print {
    .no-print { display: none !important; }
    .card { border: none; box-shadow: none; }
  }
</style>
<div class="container py-4">
  <div class="card shadow-sm mb-3 no-print">
    <div class="card-body">
      <form class="row gy-2 align-items-end" method="get">
        <div class="col-12 col-md-3">
          <label class="form-label">From</label>
          <input type="date" class="form-control" name="from" value="<?= htmlspecialchars($from); ?>">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">To</label>
          <input type="date" class="form-control" name="to" value="<?= htmlspecialchars($to); ?>">
        </div>
        <div class="col-12 col-md-3">
          <button class="btn btn-primary" type="submit">Apply</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <h4 class="card-title mb-3">Financial Summary</h4>
      <div class="row g-2">
        <div class="col-12 col-md-4">
          <div class="p-3 bg-light rounded">
            <h6 class="text-muted mb-1">Total Income</h6>
            <h4 class="mb-0" data-currency-amount="<?= $income; ?>"><?= format_currency($income); ?></h4>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="p-3 bg-light rounded">
            <h6 class="text-muted mb-1">Total Expenses</h6>
            <h4 class="mb-0" data-currency-amount="<?= $expense; ?>"><?= format_currency($expense); ?></h4>
          </div>
        </div>
        <div class="col-12 col-md-4">
          <div class="p-3 bg-light rounded">
            <h6 class="text-muted mb-1">Net Profit</h6>
            <h4 class="mb-0" data-currency-amount="<?= $net; ?>"><?= format_currency($net); ?></h4>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h4 class="card-title mb-3">Transactions (<?= htmlspecialchars($from); ?> to <?= htmlspecialchars($to); ?>)</h4>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>Date</th>
              <th>Type</th>
              <th>Category</th>
              <th class="text-end">Amount</th>
              <th>Description</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$rows): ?>
              <tr><td colspan="5" class="text-center text-muted">No data</td></tr>
            <?php else: foreach ($rows as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['occurred_on']); ?></td>
                <td><span class="badge bg-<?= $r['type']==='income'?'success':'danger'; ?>"><?= htmlspecialchars($r['type']); ?></span></td>
                <td><?= htmlspecialchars($r['category']); ?></td>
                <td class="text-end" data-currency-amount="<?= $r['amount']; ?>"><?= format_currency($r['amount']); ?></td>
                <td><?= htmlspecialchars($r['description'] ?? ''); ?></td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
