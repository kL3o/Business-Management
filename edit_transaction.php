<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid transaction id.';
    redirect('dashboard.php');
}

$user = current_user($mysqli);
$brand = get_branding($mysqli, (int)$user['id']);

// Load transaction for this user
$stmt = $mysqli->prepare('SELECT id, type, category, amount, occurred_on, description FROM transactions WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->bind_param('ii', $id, $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
$tx = $res->fetch_assoc();
if (!$tx) {
    $_SESSION['flash_error'] = 'Transaction not found.';
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Transaction - Pasqyra</title>
  <link rel="icon" href="<?= htmlspecialchars($brand['favicon']); ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/bootstrap.min.css'); ?>">
  <link rel="stylesheet" href="<?= base_url('assets/css/app.min.css'); ?>">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="<?= base_url('dashboard.php'); ?>">
      <img src="<?= htmlspecialchars($brand['logo']); ?>" alt="Logo" height="32" class="me-2">
      <span>Pasqyra</span>
    </a>
    <div class="d-flex align-items-center gap-2">
      <a class="btn btn-outline-light" href="<?= base_url('dashboard.php'); ?>">Dashboard</a>
      <a class="btn btn-outline-light" href="<?= base_url('reports.php'); ?>">Reports</a>
      <a class="btn btn-outline-light" href="<?= base_url('materials.php'); ?>">Materials</a>
      <a class="btn btn-outline-light" href="<?= base_url('workers.php'); ?>">Workers</a>
      <a class="btn btn-outline-light" href="<?= base_url('logout.php'); ?>">Logout</a>
    </div>
  </div>
</nav>
<div class="container py-4">
  <div class="row justify-content-center">
    <div class="col-12 col-lg-8">
      <div class="card shadow-sm">
        <div class="card-body">
          <h4 class="card-title mb-3">Edit Transaction</h4>
          <form class="row gy-3" method="post" action="<?= base_url('update_transaction.php'); ?>">
            <input type="hidden" name="id" value="<?= (int)$tx['id']; ?>">
            <div class="col-12 col-md-4">
              <label class="form-label">Type</label>
              <select class="form-select" name="type" required>
                <option value="income" <?= $tx['type']==='income'?'selected':''; ?>>Income</option>
                <option value="expense" <?= $tx['type']==='expense'?'selected':''; ?>>Expense</option>
              </select>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label">Category</label>
              <input type="text" class="form-control" name="category" value="<?= htmlspecialchars($tx['category']); ?>" required>
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label">Amount</label>
              <input type="number" step="0.01" min="0" class="form-control" name="amount" value="<?= htmlspecialchars(number_format((float)$tx['amount'], 2, '.', '')); ?>" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Date</label>
              <input type="date" class="form-control" name="occurred_on" value="<?= htmlspecialchars($tx['occurred_on']); ?>" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Description</label>
              <input type="text" class="form-control" name="description" value="<?= htmlspecialchars($tx['description'] ?? ''); ?>">
            </div>
            <div class="col-12 d-flex gap-2">
              <button class="btn btn-primary" type="submit">Save Changes</button>
              <a class="btn btn-secondary" href="<?= base_url('dashboard.php'); ?>">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="<?= base_url('assets/js/vendor.min.js'); ?>"></script>
<script src="<?= base_url('assets/js/app.min.js'); ?>"></script>
</body>
</html>
