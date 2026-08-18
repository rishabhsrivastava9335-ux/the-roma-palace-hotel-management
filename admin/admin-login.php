<?php
/**
 * THE ROMA PALACE — Admin Control Center Sign In
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

if (is_admin()) {
    header("Location: dashboard.php");
    exit;
}

$error = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $res = login_user($email, $password, 'admin');
    if ($res['success']) {
        $_SESSION['flash_success'] = 'Welcome to The Roma Palace Executive Management Portal.';
        header("Location: dashboard.php");
        exit;
    } else {
        $error = $res['message'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Sign In | The Roma Palace HMS</title>
  
  <!-- FontAwesome 6 & Styles -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body style="background: #0C0D0F; min-height: 100vh; display: flex; align-items: center; justify-content: center; padding: 2rem;">

  <div style="max-width: 460px; width: 100%; background: #181A1F; border: 1px solid rgba(197, 168, 128, 0.35); border-radius: 6px; padding: 3rem 2.5rem; box-shadow: 0 25px 60px rgba(0,0,0,0.6); color: #FFFFFF;">
    
    <!-- Monogram & Title -->
    <div style="text-align: center; margin-bottom: 2rem;">
      <div class="rp-monogram" style="margin: 0 auto 1rem auto; width: 50px; height: 50px;">
        <span>RP</span>
      </div>
      <h2 style="font-family: var(--font-serif-brand); font-size: 1.3rem; letter-spacing: 2px; color: #FFFFFF; margin: 0;">THE ROMA PALACE</h2>
      <span style="font-size: 0.72rem; color: var(--color-gold); letter-spacing: 2px; text-transform: uppercase; display: block; margin-top: 4px;">
        Administration & Hotel Management System
      </span>
    </div>

    <!-- Demo Credentials Notice -->
    <div class="demo-credentials-box" style="background: rgba(197, 168, 128, 0.1); border-color: var(--color-gold); color: #E5E7EB;">
      <h4 style="color: var(--color-gold);"><i class="fa-solid fa-graduation-cap"></i> Demo Admin Credentials</h4>
      <p style="color: #D1D5DB; margin-bottom: 3px;">Email: <code>admin@romapalace.com</code></p>
      <p style="color: #D1D5DB; margin-bottom: 6px;">Password: <code>Admin@123</code></p>
      <button type="button" class="demo-fill-btn" onclick="fillDemoAdmin()">
        <i class="fa-solid fa-wand-magic-sparkles"></i> Auto-Fill Demo Admin
      </button>
    </div>

    <?php if ($error): ?>
      <div style="background: rgba(239, 68, 68, 0.15); color: #FCA5A5; padding: 0.8rem 1rem; border-radius: 4px; margin-bottom: 1.5rem; font-size: 0.85rem; border-left: 3px solid #EF4444;">
        <i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i>
        <?php echo htmlspecialchars($error); ?>
      </div>
    <?php endif; ?>

    <form method="POST" action="admin-login.php">
      <div class="form-group">
        <label for="admin_email" style="color: #9CA3AF;">Admin Email Address</label>
        <input type="email" name="email" id="admin_email" class="form-control" style="background: #111316; border-color: #2D3139; color: #FFFFFF;" required placeholder="admin@romapalace.com">
      </div>

      <div class="form-group">
        <label for="admin_pass" style="color: #9CA3AF;">Password</label>
        <input type="password" name="password" id="admin_pass" class="form-control" style="background: #111316; border-color: #2D3139; color: #FFFFFF;" required placeholder="••••••••">
      </div>

      <button type="submit" class="btn-primary" style="width: 100%; padding: 0.95rem; margin-top: 1rem; font-size: 0.85rem;">
        <i class="fa-solid fa-shield-halved"></i>
        <span>AUTHENTICATE & ENTER CONTROL CENTER</span>
      </button>
    </form>

    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.08); text-align: center; font-size: 0.82rem;">
      <a href="../index.php" style="color: var(--color-gold); text-decoration: none;">
        &larr; Return to Guest Website
      </a>
      <span style="margin: 0 8px; opacity: 0.3;">|</span>
      <a href="demo-presentation.php" style="color: #9CA3AF; text-decoration: underline;">
        DBMS Viva Mode
      </a>
    </div>

  </div>

  <script>
  function fillDemoAdmin() {
    document.getElementById('admin_email').value = 'admin@romapalace.com';
    document.getElementById('admin_pass').value = 'Admin@123';
  }
  </script>

</body>
</html>
