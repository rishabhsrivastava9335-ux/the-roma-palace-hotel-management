<?php
/**
 * THE ROMA PALACE — System Settings & Database Reset
 * BTech CSE DBMS Mini Project &bull; Founder: Rishabh Srivastava
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Handle Reset Database Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reset_database') {
    $sqlFile = __DIR__ . '/../database/roma_palace.sql';
    if (file_exists($sqlFile)) {
        $sql = file_get_contents($sqlFile);
        try {
            global $pdo;
            if (CURRENT_DB_DRIVER === 'sqlite') {
                $sqliteDbFile = __DIR__ . '/../database/roma_palace.sqlite';
                if (file_exists($sqliteDbFile)) {
                    $pdo = null;
                    @unlink($sqliteDbFile);
                }
                // Trigger re-init
                require_once __DIR__ . '/../includes/db.php';
            } else {
                $pdo->exec($sql);
            }
            $_SESSION['flash_success'] = "Database successfully reset to pristine original state with all 18 tables and sample seed records!";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Reset failed: " . $e->getMessage();
        }
    }
    header("Location: settings.php");
    exit;
}

require_once __DIR__ . '/includes/admin-header.php';

$pageHeading = 'System Settings & Database Maintenance';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-sliders text-gold"></i> Global Hotel System Configuration</h3>
  </div>

  <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem;">
    
    <!-- System Profile Card -->
    <div style="background: #F9FAFB; padding: 1.8rem; border-radius: 4px; border: 1px solid var(--admin-border);">
      <h4 style="font-size: 1.1rem; color: var(--admin-primary); margin-bottom: 1rem;"><i class="fa-solid fa-building text-gold"></i> Property Brand Identity</h4>
      
      <div style="font-size: 0.88rem; line-height: 2; color: var(--admin-text-secondary);">
        <div><strong>Application Name:</strong> THE ROMA PALACE</div>
        <div><strong>Tagline:</strong> “A Legacy of Luxury, A Stay to Remember.”</div>
        <div><strong>GSTIN Registration:</strong> 08AAAAA0000A1Z5</div>
        <div><strong>Applicable Luxury GST:</strong> 18.00% (Central & State GST)</div>
        <div><strong>Base Currency:</strong> Indian Rupee (INR — ₹)</div>
        <div><strong>DBMS Engine:</strong> <?php echo strtoupper(CURRENT_DB_DRIVER); ?> (PDO Abstraction)</div>
      </div>
    </div>

    <!-- 1-Click Database Reset Maintenance Card -->
    <div style="background: #FFFBEB; padding: 1.8rem; border-radius: 4px; border: 1px solid #FCD34D;">
      <h4 style="font-size: 1.1rem; color: #92400E; margin-bottom: 0.5rem;">
        <i class="fa-solid fa-triangle-exclamation"></i> Academic Presentation Reset Tool
      </h4>
      <p style="font-size: 0.85rem; color: #78350F; line-height: 1.6; margin-bottom: 1.5rem;">
        If you or the examining professor have created, modified, or cancelled bookings and wish to restore the database to its clean original state with all 18 tables and 100+ sample records, click the button below.
      </p>

      <form method="POST" action="settings.php" onsubmit="return confirm('Reset database back to clean sample state? All test bookings will be restored to default.');">
        <input type="hidden" name="action" value="reset_database">
        <button type="submit" class="admin-btn-primary" style="background: #92400E; border-color: #92400E; color: #FFFFFF; width: 100%; padding: 0.85rem;">
          <i class="fa-solid fa-rotate-left"></i> RESTORE CLEAN DATABASE SEED DATA
        </button>
      </form>
    </div>

  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
