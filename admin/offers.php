<?php
/**
 * THE ROMA PALACE — Offers & Campaigns Management (CRUD)
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/admin-header.php';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_offer') {
        $code = trim(strtoupper($_POST['code']));
        $title = trim($_POST['title']);
        $tag = trim($_POST['tag']);
        $discPercent = (int)($_POST['discount_percent'] ?? 0);
        $flatDisc = (float)($_POST['flat_discount'] ?? 0);
        $desc = trim($_POST['description']);
        $benefits = trim($_POST['benefits']);
        $validity = $_POST['validity_date'];
        $img = trim($_POST['image_url']);
        $priceNote = trim($_POST['price_note']);

        try {
            db_execute("INSERT INTO offers (code, title, tag, discount_percent, flat_discount, description, benefits, validity_date, image_url, price_note, is_active) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)", [
                $code, $title, $tag, $discPercent, $flatDisc, $desc, $benefits, $validity, $img, $priceNote
            ]);
            $_SESSION['flash_success'] = "Offer '{$title}' created!";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Failed to add offer: " . $e->getMessage();
        }
        header("Location: offers.php");
        exit;
    }

    if ($action === 'delete_offer') {
        $oId = (int)$_POST['offer_id'];
        db_execute("DELETE FROM offers WHERE offer_id = ?", [$oId]);
        $_SESSION['flash_success'] = "Offer removed.";
        header("Location: offers.php");
        exit;
    }
}

$offers = db_fetch_all("SELECT * FROM offers ORDER BY offer_id ASC");
$pageHeading = 'Privilege Offers & Promotional Packages';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-tags text-gold"></i> Promotional Offers (<?php echo count($offers); ?> Active)</h3>
    <button type="button" class="admin-btn-primary" onclick="openOfferModal()">
      <i class="fa-solid fa-plus"></i> Add Signature Offer
    </button>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Promo Code</th>
          <th>Offer Title & Tag</th>
          <th>Discount Structure</th>
          <th>Included Perks</th>
          <th>Validity Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($offers as $o): ?>
          <tr>
            <td><code style="font-size: 0.85rem; font-weight: 700; background: #FEF3C7; color: #92400E; padding: 0.25rem 0.6rem; border-radius: 3px;"><?php echo htmlspecialchars($o['code']); ?></code></td>
            <td>
              <strong><?php echo htmlspecialchars($o['title']); ?></strong><br>
              <span class="badge badge-info" style="font-size: 0.68rem;"><?php echo htmlspecialchars($o['tag']); ?></span>
            </td>
            <td>
              <?php if ($o['discount_percent'] > 0): ?>
                <strong style="color: #03543F; font-size: 1rem;"><?php echo $o['discount_percent']; ?>% OFF</strong>
              <?php elseif ($o['flat_discount'] > 0): ?>
                <strong style="color: #03543F; font-size: 1rem;"><?php echo format_inr($o['flat_discount']); ?> Flat OFF</strong>
              <?php else: ?>
                <span>Value Add Package</span>
              <?php endif; ?><br>
              <small style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($o['price_note']); ?></small>
            </td>
            <td><small><?php echo htmlspecialchars(substr($o['benefits'], 0, 60)) . '...'; ?></small></td>
            <td><strong><?php echo format_stay_date($o['validity_date']); ?></strong></td>
            <td>
              <form method="POST" action="offers.php" onsubmit="return confirm('Delete offer?');" style="display: inline;">
                <input type="hidden" name="action" value="delete_offer">
                <input type="hidden" name="offer_id" value="<?php echo $o['offer_id']; ?>">
                <button type="submit" class="btn-action-icon" style="color: #EF4444;" title="Delete Offer">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Offer Form -->
<div id="offerModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.65); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: #FFFFFF; max-width: 600px; width: 90%; border-radius: 6px; padding: 2.5rem; border: 1px solid var(--admin-border); margin: 2rem auto; box-shadow: 0 20px 50px rgba(0,0,0,0.25); position: relative;">
    <button onclick="closeOfferModal()" style="position: absolute; top: 1.2rem; right: 1.2rem; background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
    <h3 style="font-size: 1.3rem; margin-bottom: 1.5rem;">Add Signature Offer</h3>

    <form method="POST" action="offers.php">
      <input type="hidden" name="action" value="create_offer">

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label>Promo Code</label>
          <input type="text" name="code" class="form-control" placeholder="e.g. ROYAL25" required>
        </div>

        <div class="form-group">
          <label>Tag Category</label>
          <input type="text" name="tag" class="form-control" placeholder="Weekend Getaway" required>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Offer Title</label>
          <input type="text" name="title" class="form-control" placeholder="e.g. THE ROYAL MONSOON RETREAT" required>
        </div>

        <div class="form-group">
          <label>Discount Percentage (%)</label>
          <input type="number" name="discount_percent" class="form-control" value="15">
        </div>

        <div class="form-group">
          <label>Validity Date</label>
          <input type="date" name="validity_date" class="form-control" value="2026-12-31" required>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Price / Promo Note</label>
          <input type="text" name="price_note" class="form-control" placeholder="15% Off Stays + Champagne Breakfast">
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Image URL</label>
          <input type="url" name="image_url" class="form-control" value="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1200&q=85" required>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Included Perks (comma separated)</label>
          <input type="text" name="benefits" class="form-control" placeholder="Breakfast Buffet, Spa Privilege, Late Checkout" required>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Description</label>
          <textarea name="description" class="form-control" rows="2" placeholder="Description of package..." required></textarea>
        </div>
      </div>

      <div style="text-align: right; margin-top: 1.5rem;">
        <button type="button" class="btn-outline-dark" onclick="closeOfferModal()" style="padding: 0.6rem 1.2rem; margin-right: 0.5rem;">Cancel</button>
        <button type="submit" class="admin-btn-primary" style="padding: 0.6rem 1.5rem;">Publish Offer</button>
      </div>
    </form>
  </div>
</div>

<script>
function openOfferModal() { document.getElementById('offerModal').style.display = 'flex'; }
function closeOfferModal() { document.getElementById('offerModal').style.display = 'none'; }
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
