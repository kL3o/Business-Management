<?php
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

// Check if user is already logged in
if (is_logged_in()) {
    redirect('profile.php');
}

// Get any error messages
$error = $_SESSION['flash_error'] ?? '';
unset($_SESSION['flash_error']);

// Set page title
$page_title = 'Login';

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
                <h3 class="mt-2 mb-0">Pasqyra</h3>
                <p class="text-muted">Sign in to your account</p>
              </div>
              <?php if ($error): ?>
                <div class="alert alert-danger" role="alert">
                  <?= htmlspecialchars($error); ?>
                </div>
              <?php endif; ?>
              <form action="<?= base_url('process_login.php'); ?>" method="post" novalidate>
                <div class="mb-3">
                  <label for="email" class="form-label">Email</label>
                  <input type="email" class="form-control" id="email" name="email" placeholder="you@example.com" required>
                </div>
                <div class="mb-3">
                  <label for="password" class="form-label">Password</label>
                  <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                </div>
                <div class="d-grid gap-2">
                  <button type="submit" class="btn btn-primary">Login</button>
                </div>
              </form>
              <div class="text-center mt-3">
                <small class="text-muted">Don't have an account? <a href="<?= base_url('register.php'); ?>">Create one</a></small>
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
