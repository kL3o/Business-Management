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

$docs = get_documents($mysqli, (int)$user['id']);

$page_title = 'Documents';
require_once __DIR__ . '/includes/header.php';

// Display flash messages
if ($success || $error) {
    echo '<div class="row"><div class="col-12">';
    if ($success) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
    if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
    echo '</div></div>';
}
?>

<style>
  @media print { 
    .no-print { display: none !important; } 
    .card { border: none; box-shadow: none; }
  }
  .truncate { 
    max-width: 380px; 
    overflow: hidden; 
    text-overflow: ellipsis; 
    white-space: nowrap; 
  }
</style>
<div class="container py-4">
  <div class="row g-4">
    <div class="col-12">
      <div class="card shadow-sm no-print">
        <div class="card-body">
          <h5 class="card-title">Upload Document</h5>
          <form class="row gy-2" method="post" action="<?= base_url('doc_upload.php'); ?>" enctype="multipart/form-data">
            <div class="col-12 col-md-6">
              <label class="form-label">Document Name (optional)</label>
              <input type="text" class="form-control" name="display_name" placeholder="e.g., Contract Q3.pdf">
            </div>
            <div class="col-12 col-md-6">
              <label class="form-label">Choose File</label>
              <input type="file" class="form-control" name="file" required>
            </div>
            <div class="col-12">
              <button class="btn btn-primary" type="submit">Upload</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title">Your Documents</h5>
          <div class="table-responsive">
            <table class="table table-striped align-middle">
              <thead>
                <tr>
                  <th>Name</th>
                  <th class="text-end">Size</th>
                  <th>Uploaded</th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if (!$docs): ?>
                  <tr><td colspan="4" class="text-center text-muted">No documents uploaded</td></tr>
                <?php else: foreach ($docs as $d): ?>
                  <tr>
                    <td class="truncate" title="<?= htmlspecialchars($d['original_name']); ?>">
                      <?= htmlspecialchars($d['original_name']); ?>
                    </td>
                    <td class="text-end"><?= $d['size_bytes'] !== null ? number_format((int)$d['size_bytes'] / 1024, 1) . ' KB' : ''; ?></td>
                    <td><?= htmlspecialchars($d['uploaded_at']); ?></td>
                    <td class="text-end">
                      <a class="btn btn-sm btn-outline-secondary" target="_blank" href="<?= base_url('doc_download.php?id=' . (int)$d['id'] . '&inline=1'); ?>">Open</a>
                      <form class="d-inline" method="post" action="<?= base_url('doc_delete.php'); ?>" onsubmit="return confirm('Delete this document?');">
                        <input type="hidden" name="id" value="<?= (int)$d['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Delete</button>
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
  // Initialize tooltips
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });
  
  // Add tooltips to truncated text
  document.querySelectorAll('.truncate').forEach(function(el) {
    if (el.scrollWidth > el.offsetWidth) {
      el.setAttribute('data-bs-toggle', 'tooltip');
      el.setAttribute('title', el.textContent);
      new bootstrap.Tooltip(el);
    }
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
