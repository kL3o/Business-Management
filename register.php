<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

// Get any error or success messages
$err = $_SESSION['flash_error'] ?? '';
$ok = $_SESSION['flash_success'] ?? '';
unset($_SESSION['flash_error'], $_SESSION['flash_success']);

// Set page title
$page_title = 'Create Account';

// Include header (which will detect if it's a login page)
require_once __DIR__ . '/includes/header.php';
?>

<style>
  body.authentication-bg {
    background: url('<?= base_url('images/logobg.png'); ?>') no-repeat center center fixed;
    background-size: cover;
    min-height: 100vh;
    display: flex;
    align-items: center;
  }
  .account-pages {
    width: 100%;
  }
</style>

<div class="account-pages my-5 pt-5">
  <div class="account-pages my-5 pt-5">
    <div class="container">
      <div class="row justify-content-center">
        <div class="col-xl-4 col-lg-5 col-md-6">
          <div class="card shadow-sm">
            <div class="card-body p-4">
              <div class="text-center mb-4">
                <img src="<?= base_url('images/logo.png'); ?>" alt="Logo" height="56">
                <h3 class="mt-2">Create Account</h3>
                <p class="text-muted">Sign up to start using Pasqyra</p>
              </div>
              <?php if ($err): ?>
                <div class="alert alert-danger" role="alert">
                  <?= htmlspecialchars($err); ?>
                </div>
              <?php endif; ?>
              <?php if ($ok): ?>
                <div class="alert alert-success" role="alert">
                  <?= htmlspecialchars($ok); ?>
                </div>
              <?php endif; ?>
              <form action="<?= base_url('process_register.php'); ?>" method="post" novalidate>
                <div class="mb-3">
                  <label for="name" class="form-label">Full Name</label>
                  <input type="text" class="form-control" id="name" name="name" placeholder="John Doe" required>
                </div>
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" name="password" placeholder="Choose a strong password" required>
                </div>
                <div class="mb-3">
                  <label for="confirm_password" class="form-label">Confirm Password</label>
                  <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Re-enter password" required>
                </div>
                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-primary">Create Account</button>
                </div>
              </form>
              <div class="text-center mt-3">
                <small class="text-muted">Already have an account? <a href="<?= base_url('login.php'); ?>">Login</a></small>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php 
// Include footer (which will detect if it's a login page)
require_once __DIR__ . '/includes/footer.php'; 
?>
