<?php
/**
 * THE ROMA PALACE — Master Reservation Ledger
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/admin-header.php';

$hotelFilter = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : null;
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : null;
$paymentFilter = isset($_GET['payment']) ? trim($_GET['payment']) : null;

// Handle Status Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $bId = (int)$_POST['booking_id'];

    if ($action === 'confirm_booking') {
        db_execute("UPDATE bookings SET booking_status = 'Confirmed' WHERE booking_id = ?", [$bId]);
        $_SESSION['flash_success'] = "Booking status updated to Confirmed.";
        header("Location: bookings.php");
        exit;
    }

    if ($action === 'cancel_booking') {
        $bk = db_fetch_one("SELECT * FROM bookings WHERE booking_id = ?", [$bId]);
        if ($bk) {
            db_execute("UPDATE bookings SET booking_status = 'Cancelled', payment_status = 'Refunded' WHERE booking_id = ?", [$bId]);
            db_execute("UPDATE rooms SET status = 'Available' WHERE room_id = ?", [$bk['room_id']]);
            $_SESSION['flash_success'] = "Booking {$bk['booking_ref']} cancelled and room inventory released.";
        }
        header("Location: bookings.php");
        exit;
    }
}

// Master Bookings Query (Relational Joins across 5 tables)
$sql = "SELECT b.*, c.full_name, c.phone, c.id_type, c.id_number,
               h.name AS hotel_name, h.city, 
               r.room_number, r.room_type,
               p.payment_method, p.transaction_id, p.status AS pay_status
        FROM bookings b
        INNER JOIN customers c ON b.customer_id = c.customer_id
        INNER JOIN hotels h ON b.hotel_id = h.hotel_id
        INNER JOIN rooms r ON b.room_id = r.room_id
        LEFT JOIN payments p ON b.booking_id = p.booking_id
        WHERE 1=1";

$params = [];
if ($hotelFilter) {
    $sql .= " AND b.hotel_id = ?";
    $params[] = $hotelFilter;
}
if ($statusFilter) {
    $sql .= " AND b.booking_status = ?";
    $params[] = $statusFilter;
}
if ($paymentFilter) {
    $sql .= " AND b.payment_status = ?";
    $params[] = $paymentFilter;
}

$sql .= " ORDER BY b.booking_id DESC";
$bookings = db_fetch_all($sql, $params);
$hotels = db_fetch_all("SELECT * FROM hotels ORDER BY name ASC");

$pageHeading = 'Master Reservation Ledger';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
      <h3 class="admin-card-title"><i class="fa-solid fa-calendar-check text-gold"></i> Master Bookings (<?php echo count($bookings); ?> Records)</h3>
      
      <!-- Filters -->
      <form method="GET" action="bookings.php" style="display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
        <select name="hotel_id" onchange="this.form.submit()" style="padding: 0.45rem 0.7rem; border-radius: 4px; border: 1px solid var(--admin-border); font-size: 0.82rem;">
          <option value="">All Palaces</option>
          <?php foreach ($hotels as $ht): ?>
            <option value="<?php echo $ht['hotel_id']; ?>" <?php echo ($hotelFilter == $ht['hotel_id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($ht['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <select name="status" onchange="this.form.submit()" style="padding: 0.45rem 0.7rem; border-radius: 4px; border: 1px solid var(--admin-border); font-size: 0.82rem;">
          <option value="">All Statuses</option>
          <option value="Confirmed" <?php echo ($statusFilter === 'Confirmed') ? 'selected' : ''; ?>>Confirmed</option>
          <option value="Checked-In" <?php echo ($statusFilter === 'Checked-In') ? 'selected' : ''; ?>>Checked-In</option>
          <option value="Completed" <?php echo ($statusFilter === 'Completed') ? 'selected' : ''; ?>>Completed</option>
          <option value="Cancelled" <?php echo ($statusFilter === 'Cancelled') ? 'selected' : ''; ?>>Cancelled</option>
        </select>

        <select name="payment" onchange="this.form.submit()" style="padding: 0.45rem 0.7rem; border-radius: 4px; border: 1px solid var(--admin-border); font-size: 0.82rem;">
          <option value="">All Payments</option>
          <option value="Paid" <?php echo ($paymentFilter === 'Paid') ? 'selected' : ''; ?>>Paid</option>
          <option value="Pending" <?php echo ($paymentFilter === 'Pending') ? 'selected' : ''; ?>>Pending</option>
          <option value="Refunded" <?php echo ($paymentFilter === 'Refunded') ? 'selected' : ''; ?>>Refunded</option>
        </select>

        <?php if ($hotelFilter || $statusFilter || $paymentFilter): ?>
          <a href="bookings.php" style="font-size: 0.78rem; color: var(--admin-gold-dark); text-decoration: underline;">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <div class="admin-actions-bar">
      <div class="search-input-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search ref, guest, phone..." data-table-search="bookingsMasterTable">
      </div>
      <a href="../booking.php" target="_blank" class="admin-btn-primary">
        <i class="fa-solid fa-plus"></i> New Reservation
      </a>
    </div>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table" id="bookingsMasterTable">
      <thead>
        <tr>
          <th>Booking Ref</th>
          <th>Guest Details</th>
          <th>Property & Room</th>
          <th>Stay Dates</th>
          <th>Financials</th>
          <th>Payment</th>
          <th>Status</th>
          <th>Reception Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($bookings as $b): ?>
          <tr>
            <td>
              <strong style="font-size: 0.95rem;"><?php echo htmlspecialchars($b['booking_ref']); ?></strong><br>
              <small style="color: var(--admin-text-muted);"><?php echo date('d M Y, h:i A', strtotime($b['created_at'])); ?></small>
            </td>
            <td>
              <strong><?php echo htmlspecialchars($b['full_name']); ?></strong><br>
              <small style="color: var(--admin-text-muted);"><i class="fa-solid fa-phone" style="font-size: 0.7rem;"></i> <?php echo htmlspecialchars($b['phone']); ?></small><br>
              <small style="color: var(--color-gold-dark); font-weight: 600;"><?php echo htmlspecialchars($b['id_type']); ?></small>
            </td>
            <td>
              <strong><?php echo htmlspecialchars($b['hotel_name']); ?></strong><br>
              <span style="font-size: 0.82rem; color: var(--admin-text-muted);"><?php echo htmlspecialchars($b['room_type']); ?> (Rm <?php echo htmlspecialchars($b['room_number']); ?>)</span>
            </td>
            <td>
              <strong><?php echo format_stay_date($b['check_in_date']); ?></strong><br>
              <small style="color: var(--admin-text-muted);">to <?php echo format_stay_date($b['check_out_date']); ?></small>
            </td>
            <td>
              <strong style="font-size: 1rem; color: var(--admin-primary);"><?php echo format_inr($b['total_amount']); ?></strong><br>
              <small style="color: var(--admin-text-muted);">Tax: <?php echo format_inr($b['tax_amount']); ?></small>
            </td>
            <td>
              <span class="badge badge-<?php echo ($b['payment_status'] === 'Paid') ? 'success' : (($b['payment_status'] === 'Pending') ? 'warning' : 'danger'); ?>">
                <?php echo htmlspecialchars($b['payment_status']); ?>
              </span><br>
              <small style="font-size: 0.72rem; color: var(--admin-text-muted);"><?php echo htmlspecialchars($b['payment_method'] ?? 'UPI'); ?></small>
            </td>
            <td>
              <span class="badge badge-<?php 
                if ($b['booking_status'] === 'Confirmed') echo 'success';
                elseif ($b['booking_status'] === 'Checked-In') echo 'info';
                elseif ($b['booking_status'] === 'Completed') echo 'secondary';
                else echo 'danger';
              ?>"><?php echo strtoupper($b['booking_status']); ?></span>
            </td>
            <td>
              <div class="action-btn-group">
                <a href="../confirmation.php?ref=<?php echo urlencode($b['booking_ref']); ?>" target="_blank" class="btn-action-icon" title="View / Print Tax Invoice">
                  <i class="fa-solid fa-file-invoice"></i>
                </a>

                <?php if ($b['booking_status'] === 'Confirmed'): ?>
                  <a href="checkin.php?booking_id=<?php echo $b['booking_id']; ?>" class="btn-action-icon" title="Perform Check-In" style="color: #03543F;">
                    <i class="fa-solid fa-bell-concierge"></i>
                  </a>
                  <form method="POST" action="bookings.php" onsubmit="return confirm('Cancel reservation <?php echo $b['booking_ref']; ?>?');" style="display: inline;">
                    <input type="hidden" name="action" value="cancel_booking">
                    <input type="hidden" name="booking_id" value="<?php echo $b['booking_id']; ?>">
                    <button type="submit" class="btn-action-icon" style="color: #EF4444;" title="Cancel Reservation">
                      <i class="fa-solid fa-ban"></i>
                    </button>
                  </form>
                <?php elseif ($b['booking_status'] === 'Checked-In'): ?>
                  <a href="checkout.php?booking_id=<?php echo $b['booking_id']; ?>" class="btn-action-icon" title="Perform Check-Out" style="color: #9B1C1C;">
                    <i class="fa-solid fa-receipt"></i>
                  </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
