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

$success = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$todos = get_todos($mysqli, (int)$user['id']);

$page_title = 'To-Do List';
require_once __DIR__ . '/includes/header.php';

// Display flash messages
if ($success || $error) {
    echo '<div class="row"><div class="col-12">';
    if ($success) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
    if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
    echo '</div></div>';
}
?>

<div class="container py-4">
  <div class="row g-4">
    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Add To-Do</h5>
          <form class="row gy-2" method="post" action="<?= base_url('todo_add.php'); ?>">
            <div class="col-12 col-md-6">
              <label class="form-label">Title</label>
              <input type="text" class="form-control" name="title" placeholder="Task title" required>
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Notes</label>
              <input type="text" class="form-control" name="notes" placeholder="Optional notes">
            </div>
            <div class="col-12 col-md-3">
              <label class="form-label">Due Date</label>
              <input type="date" class="form-control" name="due_date">
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit">Add</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Your Tasks</h5>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Status</th>
                  <th>Title</th>
                  <th>Notes</th>
                  <th>Due</th>
                  <th>Created</th>
                  <th>Updated</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$todos): ?>
                  <tr><td colspan="6" class="text-center text-muted">No tasks yet</td></tr>
                <?php else: foreach ($todos as $t): ?>
                  <tr>
                    <td>
                      <form method="post" action="<?= base_url('todo_update.php'); ?>">
                        <input type="hidden" name="id" value="<?= (int)$t['id']; ?>">
                        <input type="hidden" name="title" value="<?= htmlspecialchars($t['title'] ?? ''); ?>">
                        <input type="hidden" name="notes" value="<?= htmlspecialchars($t['notes'] ?? ''); ?>">
                        <input type="hidden" name="is_done" value="<?= $t['is_done'] ? 0 : 1; ?>">
                        <button class="btn btn-sm <?= $t['is_done'] ? 'btn-success' : 'btn-outline-secondary'; ?>" type="submit">
                          <?= $t['is_done'] ? 'Done' : 'Mark Done'; ?>
                        </button>
                      </form>
                    </td>
                    <td><?= htmlspecialchars($t['title'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($t['notes'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($t['due_date'] ?? ''); ?></td>
                    <td><?= htmlspecialchars($t['created_at']); ?></td>
                    <td><?= htmlspecialchars($t['updated_at']); ?></td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-secondary" href="<?= base_url('edit_todo.php?id=' . (int)$t['id']); ?>">Edit</a>
                      <form class="d-inline" method="post" action="<?= base_url('todo_delete.php'); ?>" onsubmit="return confirm('Delete this task?');">
                        <input type="hidden" name="id" value="<?= (int)$t['id']; ?>">
                        <button class="btn btn-sm btn-danger" type="submit">Delete</button>
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
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Toggle task completion
  document.querySelectorAll('.todo-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
      const form = this.closest('form');
      if (form) form.submit();
    });
  });
  
  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
