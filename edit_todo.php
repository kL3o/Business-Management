<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_error'] = 'Invalid task id.';
    redirect('todos.php');
}

$user = current_user($mysqli);
$brand = get_branding($mysqli, (int)$user['id']);

// Load todo for this user
$stmt = $mysqli->prepare('SELECT id, title, notes, due_date, is_done, created_at, updated_at FROM todo_items WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->bind_param('ii', $id, $_SESSION['user_id']);
$stmt->execute();
$res = $stmt->get_result();
$todo = $res->fetch_assoc();
if (!$todo) {
    $_SESSION['flash_error'] = 'Task not found.';
    redirect('todos.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit To-Do - Pasqyra</title>
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
      <a class="btn btn-outline-light" href="<?= base_url('todos.php'); ?>">To-Do</a>
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
          <h4 class="card-title mb-3">Edit Task</h4>
          <form class="row gy-3" method="post" action="<?= base_url('todo_update.php'); ?>">
            <input type="hidden" name="id" value="<?= (int)$todo['id']; ?>">
            <div class="col-12">
              <label class="form-label">Title</label>
              <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($todo['title'] ?? ''); ?>" required>
            </div>
            <div class="col-12">
              <label class="form-label">Notes</label>
              <input type="text" class="form-control" name="notes" value="<?= htmlspecialchars($todo['notes'] ?? ''); ?>">
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label">Due Date</label>
              <input type="date" class="form-control" name="due_date" value="<?= htmlspecialchars($todo['due_date'] ?? ''); ?>">
            </div>
            <div class="col-12 col-md-4">
              <label class="form-label">Status</label>
              <select class="form-select" name="is_done">
                <option value="0" <?= !(int)$todo['is_done'] ? 'selected' : ''; ?>>Pending</option>
                <option value="1" <?= (int)$todo['is_done'] ? 'selected' : ''; ?>>Done</option>
              </select>
            </div>
            <div class="col-12 d-flex gap-2">
              <button class="btn btn-primary" type="submit">Save Changes</button>
              <a class="btn btn-secondary" href="<?= base_url('todos.php'); ?>">Cancel</a>
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
