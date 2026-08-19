<?php
/**
 * THE ROMA PALACE — Customer Sign In
 * BTech CSE DBMS Mini Project &bull; Founder: Rishabh Srivastava
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

if (is_customer()) {
    header("Location: customer-dashboard.php");
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $res = login_user($email, $password, 'customer');
    if ($res['success']) {
        $_SESSION['flash_success'] = 'Welcome back to The Roma Palace!';
        header("Location: customer-dashboard.php");
        exit;
    } else {
        $error = $res['message'];
    }
}

$pageTitle = 'Guest Sign In — The Roma Palace';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section-spacing bg-ivory" style="padding-top: 6rem;">
  <div class="container">
    <div style="max-width: 480px; margin: 0 auto; background: var(--color-white); padding: 3rem 2.5rem; border-radius: 4px; border: 1px solid var(--border-gold); box-shadow: var(--shadow-luxury);">
      
      <!-- Monogram -->
      <div style="text-align: center; margin-bottom: 2rem;">
        <div class="rp-monogram" style="margin: 0 auto 1rem auto; background: var(--color-charcoal);">
          <span>RP</span>
        </div>
        <span class="section-tag">MY ROMA PALACE</span>
        <h2>GUEST SIGN IN</h2>
        <p style="font-size: 0.88rem;">Access your royal reservations, invoices, and privileged benefits.</p>
      </div>

      <!-- Demo Credentials Notice -->
      <div class="demo-credentials-box">
        <h4><i class="fa-solid fa-graduation-cap text-gold"></i> Demo Guest Credentials</h4>
        <p>Email: <code>guest@romapalace.com</code></p>
        <p>Password: <code>Guest@123</code></p>
        <button type="button" class="demo-fill-btn" onclick="fillDemoGuest()">
          <i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Fill Demo Guest
        </button>
      </div>

      <?php if ($error): ?>
        <div style="background: #FDE8E8; color: #9B1C1C; padding: 0.8rem 1rem; border-radius: 2px; margin-bottom: 1.5rem; font-size: 0.85rem; border-left: 3px solid #E02424;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="login.php">
        <div class="form-group">
          <label for="email">Email Address</label>
          <input type="email" name="email" id="email" class="form-control" required placeholder="guest@romapalace.com">
        </div>

        <div class="form-group">
          <label for="password">Password</label>
          <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 0.95rem; margin-top: 0.5rem; font-size: 0.85rem;">
          <i class="fa-solid fa-right-to-bracket"></i>
          <span>SIGN IN TO MY ACCOUNT</span>
        </button>
      </form>

      <div style="text-align: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-light); font-size: 0.85rem;">
        <span style="color: var(--text-muted);">New to The Roma Palace?</span>
        <a href="register.php" style="color: var(--color-gold-dark); font-weight: 700; text-decoration: underline; margin-left: 5px;">
          Create an Account
        </a>
      </div>

      <div style="text-align: center; margin-top: 1rem;">
        <a href="admin/admin-login.php" style="font-size: 0.78rem; color: var(--text-muted);">
          <i class="fa-solid fa-lock" style="font-size: 0.7rem;"></i> Hotel Management Admin Login
        </a>
      </div>

    </div>
  </div>
</section>

<script>
function fillDemoGuest() {
  document.getElementById('email').value = 'guest@romapalace.com';
  document.getElementById('password').value = 'Guest@123';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
