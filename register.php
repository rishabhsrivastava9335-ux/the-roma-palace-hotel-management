<?php
/**
 * THE ROMA PALACE — Guest Account Registration
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
    $res = register_customer($_POST);
    if ($res['success']) {
        $_SESSION['flash_success'] = $res['message'];
        header("Location: customer-dashboard.php");
        exit;
    } else {
        $error = $res['message'];
    }
}

$pageTitle = 'Create Guest Account — The Roma Palace';
require_once __DIR__ . '/includes/header.php';
?>

<section class="section-spacing bg-ivory" style="padding-top: 6rem;">
  <div class="container">
    <div style="max-width: 600px; margin: 0 auto; background: var(--color-white); padding: 3rem 2.5rem; border-radius: 4px; border: 1px solid var(--border-gold); box-shadow: var(--shadow-luxury);">
      
      <div style="text-align: center; margin-bottom: 2rem;">
        <div class="rp-monogram" style="margin: 0 auto 1rem auto; background: var(--color-charcoal);">
          <span>RP</span>
        </div>
        <span class="section-tag">JOIN THE LEGACY</span>
        <h2>CREATE GUEST ACCOUNT</h2>
        <p style="font-size: 0.88rem;">Register to unlock royal booking privileges, express check-in, and curated offers.</p>
      </div>

      <?php if ($error): ?>
        <div style="background: #FDE8E8; color: #9B1C1C; padding: 0.8rem 1rem; border-radius: 2px; margin-bottom: 1.5rem; font-size: 0.85rem; border-left: 3px solid #E02424;">
          <?php echo htmlspecialchars($error); ?>
        </div>
      <?php endif; ?>

      <form method="POST" action="register.php">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
          
          <div class="form-group" style="grid-column: span 2;">
            <label for="full_name">Full Name (as per Govt ID)</label>
            <input type="text" name="full_name" id="full_name" class="form-control" required placeholder="e.g. Maharani Gayatri Devi">
          </div>

          <div class="form-group">
            <label for="email">Email Address</label>
            <input type="email" name="email" id="email" class="form-control" required placeholder="you@domain.com">
          </div>

          <div class="form-group">
            <label for="phone">Phone Number</label>
            <input type="tel" name="phone" id="phone" class="form-control" required placeholder="+91 98765 43210">
          </div>

          <div class="form-group">
            <label for="password">Password (min 6 characters)</label>
            <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
          </div>

          <div class="form-group">
            <label for="id_type">Government ID Type</label>
            <select name="id_type" id="id_type" class="form-control" required>
              <option value="Aadhaar Card">Aadhaar Card</option>
              <option value="Passport">Passport</option>
              <option value="Driving License">Driving License</option>
              <option value="Voter ID">Voter ID</option>
              <option value="PAN Card">PAN Card</option>
            </select>
          </div>

          <div class="form-group" style="grid-column: span 2;">
            <label for="id_number">Government ID Number</label>
            <input type="text" name="id_number" id="id_number" class="form-control" required placeholder="e.g. 4589 1234 9876">
          </div>

          <div class="form-group">
            <label for="city">City</label>
            <input type="text" name="city" id="city" class="form-control" placeholder="New Delhi">
          </div>

          <div class="form-group">
            <label for="state">State</label>
            <input type="text" name="state" id="state" class="form-control" placeholder="Delhi">
          </div>

          <div class="form-group" style="grid-column: span 2;">
            <label for="address">Full Postal Address</label>
            <textarea name="address" id="address" class="form-control" rows="2" placeholder="Street, landmark, pincode..."></textarea>
          </div>

        </div>

        <button type="submit" class="btn-primary" style="width: 100%; padding: 0.95rem; margin-top: 1rem; font-size: 0.85rem;">
          <i class="fa-solid fa-user-plus"></i>
          <span>REGISTER NEW GUEST ACCOUNT</span>
        </button>
      </form>

      <div style="text-align: center; margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-light); font-size: 0.85rem;">
        <span style="color: var(--text-muted);">Already have an account?</span>
        <a href="login.php" style="color: var(--color-gold-dark); font-weight: 700; text-decoration: underline; margin-left: 5px;">
          Sign In Here
        </a>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
