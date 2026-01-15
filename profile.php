<?php
require_once __DIR__ . '/includes/functions.php';
require_auth();
$user = current_user($mysqli);

// Get user preferences
$prefs = get_user_preferences($mysqli, (int)$user['id']);
$theme = $prefs['theme'] ?? 'light';
$currency = $prefs['currency'] ?? 'EUR';

// Store in session for easy access
$_SESSION['user_prefs'] = $prefs;

$success = $_SESSION['flash_success'] ?? '';
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

$profile = get_business_profile($mysqli, (int)$user['id']);
$ibans = get_bank_ibans($mysqli, (int)$user['id']);
$brand = get_branding($mysqli, (int)$user['id']);
$partners = get_partners($mysqli, (int)$user['id']);
$activePartner = get_active_partner($mysqli, (int)$user['id']);
$global = get_global_totals($mysqli, (int)$user['id']);

$page_title = 'Profile';
require_once __DIR__ . '/includes/header.php';

// Display flash messages
if ($success || $error) {
    echo '<div class="row"><div class="col-12">';
    if ($success) echo '<div class="alert alert-success">' . htmlspecialchars($success) . '</div>';
    if ($error) echo '<div class="alert alert-danger">' . htmlspecialchars($error) . '</div>';
    echo '</div></div>';
}
?>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title mb-3"><?php _e('Work In (Partners'); ?></h5>
      <div class="row g-3 align-items-end">
        <div class="col-12 col-lg-4">
          <form class="row gy-2" method="post" action="<?= base_url('partners_add.php'); ?>">
            <label class="form-label"><?php _e('Add Partner'); ?></label>
            <div class="input-group">
              <input type="text" name="name" class="form-control" placeholder="<?= htmlspecialchars(__t('Partner / Project name')); ?>" required>
              <button class="btn btn-primary" type="submit"><?php _e('Add'); ?></button>
            </div>
          </form>
        </div>
        <div class="col-12 col-lg-8">
          <div class="alert alert-info mb-0">
            <strong><?php _e('Active partner:'); ?></strong>
            <?= htmlspecialchars($activePartner['name'] ?? 'None'); ?>
          </div>
        </div>
      </div>

      <div class="table-responsive mt-3">
        <table class="table table-sm table-bordered align-middle">
          <thead>
            <tr>
              <th style="width:55%"><?php echo htmlspecialchars(__t('Name') ?: 'Name'); ?></th>
              <th><?php echo htmlspecialchars(__t('Created') ?: 'Created'); ?></th>
              <th class="text-end" style="width:30%"><?php echo htmlspecialchars(__t('Actions') ?: 'Actions'); ?></th>
            </tr>
          </thead>
          <tbody>
            <?php if (!$partners): ?>
              <tr><td colspan="3" class="text-center text-muted"><?php echo htmlspecialchars(__t('No partners yet. Add one above.')); ?></td></tr>
            <?php else: foreach ($partners as $p): ?>
              <tr>
                <td><?= htmlspecialchars($p['name']); ?> <?= ($activePartner && $activePartner['id']===$p['id'])?'<span class="badge bg-primary ms-1">Active</span>':''; ?></td>
                <td><?= htmlspecialchars($p['created_at']); ?></td>
                <td class="text-end">
                  <form class="d-inline" method="post" action="<?= base_url('partners_set_active.php'); ?>">
                    <input type="hidden" name="id" value="<?= (int)$p['id']; ?>">
                    <button class="btn btn-sm btn-outline-primary" type="submit"><?php _e('Set Active'); ?></button>
                  </form>
                  <form class="d-inline" method="post" action="<?= base_url('partners_delete.php'); ?>" onsubmit="return confirm('Delete this partner? Allowed only if it has no data.');">
                    <input type="hidden" name="id" value="<?= (int)$p['id']; ?>">
                    <button class="btn btn-sm btn-outline-danger" type="submit"><?php _e('Delete'); ?></button>
                  </form>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title mb-2">Global Totals (All Partners)</h5>
      <div class="row g-3">
        <div class="col-md-4">
          <div class="p-3 bg-light rounded border">
            <div class="text-muted">Total Income</div>
            <div class="h4 mb-0"><?= format_currency((float)$global['income'], $currency); ?></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3 bg-light rounded border">
            <div class="text-muted">Total Expenses</div>
            <div class="h4 mb-0"><?= format_currency((float)$global['expense'], $currency); ?></div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="p-3 bg-light rounded border">
            <div class="text-muted">Net</div>
            <div class="h4 mb-0"><?= format_currency((float)$global['net'], $currency); ?></div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div class="card shadow-sm mb-4">
    <div class="card-body">
      <h5 class="card-title">Business Profile</h5>
      <form method="post" action="<?= base_url('save_profile.php'); ?>" enctype="multipart/form-data">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Business Name</label>
            <input type="text" class="form-control" name="business_name" value="<?= htmlspecialchars($profile['business_name'] ?? ''); ?>" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">NIPTI</label>
            <input type="text" class="form-control" name="nipti" value="<?= htmlspecialchars($profile['nipti'] ?? ''); ?>">
          </div>
          <div class="col-md-8">
            <label class="form-label">Address</label>
            <input type="text" class="form-control" name="address" value="<?= htmlspecialchars($profile['address'] ?? ''); ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Year of Creation</label>
            <input type="number" min="1900" max="2100" class="form-control" name="year_of_creation" value="<?= htmlspecialchars($profile['year_of_creation'] ?? ''); ?>">
          </div>
        </div>

        <hr class="my-4" />
        <h6 class="mb-3 d-flex align-items-center">Bank IBANs
          <button type="button" class="btn btn-sm btn-outline-primary ms-2" id="addIbanBtn">+ Add IBAN</button>
        </h6>
        <div id="ibanList">
          <?php if (!$ibans): ?>
            <div class="row g-2 align-items-end mb-2 iban-row">
              <div class="col-md-6">
                <label class="form-label">IBAN</label>
                <input type="text" name="ibans[0][iban]" class="form-control iban-input" placeholder="IBAN" pattern="^[A-Z0-9 ]{10,}$" />
              </div>
              <div class="col-md-5">
                <label class="form-label">Bank Name</label>
                <input type="text" name="ibans[0][bank_name]" class="form-control" placeholder="Bank name" />
              </div>
              <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100 remove-iban">-</button>
              </div>
            </div>
          <?php else: foreach ($ibans as $idx => $row): ?>
            <div class="row g-2 align-items-end mb-2 iban-row">
              <div class="col-md-6">
                <label class="form-label">IBAN</label>
                <input type="text" name="ibans[<?= $idx; ?>][iban]" class="form-control iban-input" value="<?= htmlspecialchars($row['iban']); ?>" pattern="^[A-Z0-9 ]{10,}$" />
              </div>
              <div class="col-md-5">
                <label class="form-label">Bank Name</label>
                <input type="text" name="ibans[<?= $idx; ?>][bank_name]" class="form-control" value="<?= htmlspecialchars($row['bank_name']); ?>" />
              </div>
              <div class="col-md-1">
                <button type="button" class="btn btn-outline-danger w-100 remove-iban">-</button>
              </div>
            </div>
          <?php endforeach; endif; ?>
        </div>
        <div class="mt-3">
          <button type="submit" class="btn btn-primary">Save Profile</button>
        </div>
      </form>
    </div>
  </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
  const list = document.getElementById('ibanList');
  const addBtn = document.getElementById('addIbanBtn');
  let ibanCount = <?= count($ibans); ?>;

  function addIbanField(iban = '', bank = '') {
    const id = 'iban_' + (ibanCount++);
    const div = document.createElement('div');
    div.className = 'row g-2 mb-2';
    div.innerHTML = `
      <div class="col-12 col-md-5">
        <input type="text" class="form-control" name="ibans[${id}][iban]" value="${iban}" placeholder="IBAN" required>
      </div>
      <div class="col-12 col-md-5">
        <input type="text" class="form-control" name="ibans[${id}][bank]" value="${bank}" placeholder="Bank Name" required>
      </div>
      <div class="col-12 col-md-2">
        <button type="button" class="btn btn-outline-danger w-100 remove-iban">Remove</button>
      </div>
    `;
    list.appendChild(div);
    
    // Initialize tooltips for the new element
    const tooltipTriggerList = [].slice.call(div.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
      return new bootstrap.Tooltip(tooltipTriggerEl);
    });
  }

  // Add existing IBANs
  <?php foreach ($ibans as $i => $iban): ?>
    addIbanField('<?= addslashes($iban['iban']); ?>', '<?= addslashes($iban['bank_name']); ?>');
  <?php endforeach; ?>

  // Add new IBAN
  if (addBtn) {
    addBtn.addEventListener('click', function(e) {
      e.preventDefault();
      addIbanField();
    });
  }

  // Remove IBAN
  list.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-iban')) {
      e.target.closest('.row').remove();
    }
  });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
