<?php
/**
 * THE ROMA PALACE — Hotel Services Catalog & Service Orders
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/admin-header.php';

// Handle Service CRUD & Order Updates
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_service') {
        $name = trim($_POST['name']);
        $cat = $_POST['category'];
        $price = (float)$_POST['price'];
        $unit = trim($_POST['unit']);
        $desc = trim($_POST['description']);
        $icon = trim($_POST['icon_class'] ?: 'fa-solid fa-bell-concierge');

        db_execute("INSERT INTO services (name, category, price, unit, description, icon_class, status) VALUES (?, ?, ?, ?, ?, ?, 'Available')", [
            $name, $cat, $price, $unit, $desc, $icon
        ]);
        $_SESSION['flash_success'] = "Service '{$name}' created!";
        header("Location: services.php");
        exit;
    }

    if ($action === 'update_order_status') {
        $orderId = (int)$_POST['order_id'];
        $newStatus = $_POST['status'];
        db_execute("UPDATE service_orders SET status = ? WHERE order_id = ?", [$newStatus, $orderId]);
        $_SESSION['flash_success'] = "Service order #{$orderId} status updated to {$newStatus}.";
        header("Location: services.php#orders");
        exit;
    }

    if ($action === 'delete_service') {
        $serviceId = (int)$_POST['service_id'];
        try {
            db_execute("DELETE FROM services WHERE service_id = ?", [$serviceId]);
            $_SESSION['flash_success'] = "Service deleted.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Cannot delete service associated with historical orders.";
        }
        header("Location: services.php");
        exit;
    }
}

$services = db_fetch_all("SELECT * FROM services ORDER BY category ASC, service_id ASC");
$serviceOrders = db_fetch_all("SELECT so.*, b.booking_ref, c.full_name, c.phone, r.room_number, s.name AS service_name, soi.quantity, soi.price 
                               FROM service_orders so 
                               INNER JOIN bookings b ON so.booking_id = b.booking_id 
                               INNER JOIN customers c ON so.customer_id = c.customer_id 
                               INNER JOIN rooms r ON b.room_id = r.room_id 
                               INNER JOIN service_order_items soi ON so.order_id = soi.order_id 
                               INNER JOIN services s ON soi.service_id = s.service_id 
                               ORDER BY so.order_id DESC");

$pageHeading = 'Hotel Services Catalog & In-Room Requests';
?>

<!-- Section 1: In-Room Service Orders -->
<div class="admin-card" id="orders">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-bell-concierge text-gold"></i> Live In-Room Guest Orders (<?php echo count($serviceOrders); ?> Requests)</h3>
    <div class="search-input-box">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" placeholder="Search orders..." data-table-search="serviceOrdersTable">
    </div>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table" id="serviceOrdersTable">
      <thead>
        <tr>
          <th>Order ID</th>
          <th>Room & Ref</th>
          <th>Guest Details</th>
          <th>Service Item</th>
          <th>Qty & Bill</th>
          <th>Instructions</th>
          <th>Date</th>
          <th>Order Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($serviceOrders)): ?>
          <tr><td colspan="8" style="text-align: center; padding: 2rem; color: var(--admin-text-muted);">No in-room service orders currently placed.</td></tr>
        <?php else: ?>
          <?php foreach ($serviceOrders as $so): ?>
            <tr>
              <td><strong>#ORD-<?php echo $so['order_id']; ?></strong></td>
              <td>
                <strong style="color: #9B1C1C;">Room <?php echo htmlspecialchars($so['room_number']); ?></strong><br>
                <small style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($so['booking_ref']); ?></small>
              </td>
              <td>
                <strong><?php echo htmlspecialchars($so['full_name']); ?></strong><br>
                <small><i class="fa-solid fa-phone" style="font-size: 0.7rem;"></i> <?php echo htmlspecialchars($so['phone']); ?></small>
              </td>
              <td><strong><?php echo htmlspecialchars($so['service_name']); ?></strong></td>
              <td>
                <?php echo $so['quantity']; ?> Unit(s)<br>
                <strong><?php echo format_inr($so['total_amount']); ?></strong>
              </td>
              <td><small><?php echo htmlspecialchars($so['instructions'] ?: 'Standard delivery'); ?></small></td>
              <td><?php echo date('d M Y, h:i A', strtotime($so['order_date'])); ?></td>
              <td>
                <form method="POST" action="services.php" style="display: inline;">
                  <input type="hidden" name="action" value="update_order_status">
                  <input type="hidden" name="order_id" value="<?php echo $so['order_id']; ?>">
                  <select name="status" onchange="this.form.submit()" class="badge badge-<?php 
                    if ($so['status'] === 'Delivered') echo 'success';
                    elseif ($so['status'] === 'In Progress') echo 'info';
                    elseif ($so['status'] === 'Cancelled') echo 'danger';
                    else echo 'warning';
                  ?>" style="border: none; cursor: pointer;">
                    <option value="Pending" <?php echo ($so['status'] === 'Pending') ? 'selected' : ''; ?>>Pending</option>
                    <option value="In Progress" <?php echo ($so['status'] === 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                    <option value="Delivered" <?php echo ($so['status'] === 'Delivered') ? 'selected' : ''; ?>>Delivered</option>
                    <option value="Cancelled" <?php echo ($so['status'] === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                  </select>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Section 2: Services Catalog Management -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-spa text-gold"></i> Hotel Amenities & Experiences Catalog</h3>
    <button type="button" class="admin-btn-primary" onclick="openServiceModal()">
      <i class="fa-solid fa-plus"></i> Add New Service
    </button>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Service Name</th>
          <th>Category</th>
          <th>Rate (INR)</th>
          <th>Billing Unit</th>
          <th>Description</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($services as $srv): ?>
          <tr>
            <td>
              <i class="<?php echo htmlspecialchars($srv['icon_class']); ?> text-gold" style="margin-right: 6px;"></i>
              <strong><?php echo htmlspecialchars($srv['name']); ?></strong>
            </td>
            <td><span class="badge badge-secondary"><?php echo htmlspecialchars($srv['category']); ?></span></td>
            <td><strong><?php echo format_inr($srv['price']); ?></strong></td>
            <td><small><?php echo htmlspecialchars($srv['unit']); ?></small></td>
            <td><small><?php echo htmlspecialchars($srv['description']); ?></small></td>
            <td><span class="badge badge-success"><?php echo htmlspecialchars($srv['status']); ?></span></td>
            <td>
              <form method="POST" action="services.php" onsubmit="return confirm('Delete service?');" style="display: inline;">
                <input type="hidden" name="action" value="delete_service">
                <input type="hidden" name="service_id" value="<?php echo $srv['service_id']; ?>">
                <button type="submit" class="btn-action-icon" style="color: #EF4444;" title="Delete Service">
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

<!-- Modal: Service Form -->
<div id="serviceModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.65); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: #FFFFFF; max-width: 550px; width: 90%; border-radius: 6px; padding: 2.5rem; border: 1px solid var(--admin-border); margin: 2rem auto; box-shadow: 0 20px 50px rgba(0,0,0,0.25); position: relative;">
    <button onclick="closeServiceModal()" style="position: absolute; top: 1.2rem; right: 1.2rem; background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
    <h3 style="font-size: 1.3rem; margin-bottom: 1.5rem;">Add New Service</h3>

    <form method="POST" action="services.php">
      <input type="hidden" name="action" value="create_service">

      <div class="form-group">
        <label>Service Title</label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Royal Aromatherapy Bath" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label>Category</label>
          <select name="category" class="form-control" required>
            <option value="Dining">Dining</option>
            <option value="Wellness & Spa">Wellness & Spa</option>
            <option value="Transport">Transport</option>
            <option value="Housekeeping">Housekeeping</option>
            <option value="Recreation">Recreation</option>
            <option value="Special">Special</option>
          </select>
        </div>

        <div class="form-group">
          <label>Price (INR)</label>
          <input type="number" name="price" class="form-control" placeholder="3500" required>
        </div>
      </div>

      <div class="form-group">
        <label>Billing Unit</label>
        <input type="text" name="unit" class="form-control" value="Per Session / 60 Mins" required>
      </div>

      <div class="form-group">
        <label>FontAwesome Icon Class</label>
        <input type="text" name="icon_class" class="form-control" value="fa-solid fa-spa">
      </div>

      <div class="form-group">
        <label>Service Description</label>
        <textarea name="description" class="form-control" rows="2" placeholder="Description of amenity..." required></textarea>
      </div>

      <div style="text-align: right; margin-top: 1.5rem;">
        <button type="button" class="btn-outline-dark" onclick="closeServiceModal()" style="padding: 0.6rem 1.2rem; margin-right: 0.5rem;">Cancel</button>
        <button type="submit" class="admin-btn-primary" style="padding: 0.6rem 1.5rem;">Save Service</button>
      </div>
    </form>
  </div>
</div>

<script>
function openServiceModal() { document.getElementById('serviceModal').style.display = 'flex'; }
function closeServiceModal() { document.getElementById('serviceModal').style.display = 'none'; }
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
