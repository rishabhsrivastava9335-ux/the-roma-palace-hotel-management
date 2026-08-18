<?php
/**
 * THE ROMA PALACE — Front Desk Reception Check-In Terminal
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/admin-header.php';

$searchQuery = trim($_GET['search'] ?? '');
$selectedBookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : null;

// Handle Check-In Confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_checkin') {
    $bookingId = (int)$_POST['booking_id'];
    $assignedKeycard = trim($_POST['keycard_number'] ?? 'KEY-' . rand(100, 999));
    $settlePayment = isset($_POST['settle_payment']) ? 1 : 0;

    $bk = db_fetch_one("SELECT * FROM bookings WHERE booking_id = ?", [$bookingId]);
    if ($bk && $bk['booking_status'] === 'Confirmed') {
        global $pdo;
        try {
            $pdo->beginTransaction();
            // 1. Update Booking Status to Checked-In
            if ($settlePayment && $bk['payment_status'] !== 'Paid') {
                db_execute("UPDATE bookings SET booking_status = 'Checked-In', payment_status = 'Paid' WHERE booking_id = ?", [$bookingId]);
                db_execute("UPDATE payments SET status = 'Paid' WHERE booking_id = ?", [$bookingId]);
            } else {
                db_execute("UPDATE bookings SET booking_status = 'Checked-In' WHERE booking_id = ?", [$bookingId]);
            }

            // 2. Mark Room Status as Occupied (DBMS Relational Integrity)
            db_execute("UPDATE rooms SET status = 'Occupied' WHERE room_id = ?", [$bk['room_id']]);

            $pdo->commit();
            $_SESSION['flash_success'] = "Guest successfully Checked-In for {$bk['booking_ref']}! Keycard {$assignedKeycard} issued and room marked OCCUPIED.";
            header("Location: checkin.php");
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['flash_error'] = "Check-in failed: " . $e->getMessage();
        }
    }
}

// Fetch Arriving Bookings (Confirmed)
$whereSql = "b.booking_status = 'Confirmed'";
$params = [];

if (!empty($searchQuery)) {
    $whereSql .= " AND (b.booking_ref LIKE ? OR c.full_name LIKE ? OR c.phone LIKE ? OR r.room_number LIKE ?)";
    $term = "%{$searchQuery}%";
    $params = [$term, $term, $term, $term];
}

$arrivingBookings = db_fetch_all("SELECT b.*, c.full_name, c.phone, c.id_type, c.id_number, c.address, c.city,
                                         h.name AS hotel_name, r.room_number, r.room_type, r.price_per_night,
                                         p.payment_method, p.transaction_id, p.status AS pay_status
                                  FROM bookings b
                                  INNER JOIN customers c ON b.customer_id = c.customer_id
                                  INNER JOIN hotels h ON b.hotel_id = h.hotel_id
                                  INNER JOIN rooms r ON b.room_id = r.room_id
                                  LEFT JOIN payments p ON b.booking_id = p.booking_id
                                  WHERE {$whereSql}
                                  ORDER BY b.check_in_date ASC", $params);

// Selected booking for modal inspection
$selectedBooking = null;
if ($selectedBookingId) {
    foreach ($arrivingBookings as $ab) {
        if ($ab['booking_id'] == $selectedBookingId) {
            $selectedBooking = $ab;
            break;
        }
    }
}

$pageHeading = 'Front Desk Reception — Guest Check-In';
?>

<!-- Reception Welcome Bar -->
<div class="viva-banner" style="margin-bottom: 2rem;">
  <h2><i class="fa-solid fa-bell-concierge"></i> Reception Check-In Terminal</h2>
  <p style="margin: 0.3rem 0 0 0; color: #DFCAAB; font-size: 0.9rem;">
    Search arriving guests by Booking ID, verify government identification credentials, issue physical keycards, and execute immediate room status transition (<code>Available &rarr; Occupied</code>).
  </p>
</div>

<!-- Search & Fast Reception Bar -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-user-check text-gold"></i> Arriving Guests Awaiting Check-In</h3>
    <form method="GET" action="checkin.php" class="search-input-box">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search Booking ID, Guest Name, Room..." style="width: 320px;">
    </form>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Booking Ref</th>
          <th>Guest Name</th>
          <th>Govt ID Proof</th>
          <th>Room & Property</th>
          <th>Check-in Date</th>
          <th>Total Bill</th>
          <th>Payment</th>
          <th>Front Desk Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($arrivingBookings)): ?>
          <tr>
            <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--admin-text-muted);">
              <i class="fa-solid fa-circle-check" style="font-size: 2rem; color: #10B981; margin-bottom: 0.5rem; display: block;"></i>
              No pending check-ins found for the selected search filter.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($arrivingBookings as $b): ?>
            <tr style="<?php echo ($selectedBookingId == $b['booking_id']) ? 'background: #FEF3C7;' : ''; ?>">
              <td><strong style="font-size: 1rem; color: var(--admin-primary);"><?php echo htmlspecialchars($b['booking_ref']); ?></strong></td>
              <td>
                <strong><?php echo htmlspecialchars($b['full_name']); ?></strong><br>
                <small style="color: var(--admin-text-muted);"><i class="fa-solid fa-phone" style="font-size: 0.7rem;"></i> <?php echo htmlspecialchars($b['phone']); ?></small>
              </td>
              <td>
                <span class="badge badge-info"><?php echo htmlspecialchars($b['id_type']); ?></span><br>
                <code style="font-size: 0.78rem; font-weight: 700;"><?php echo htmlspecialchars($b['id_number']); ?></code>
              </td>
              <td>
                <strong>Room <?php echo htmlspecialchars($b['room_number']); ?></strong> (<?php echo htmlspecialchars($b['room_type']); ?>)<br>
                <small style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($b['hotel_name']); ?></small>
              </td>
              <td>
                <strong><?php echo format_stay_date($b['check_in_date']); ?></strong><br>
                <small style="color: var(--admin-text-muted);">till <?php echo format_stay_date($b['check_out_date']); ?></small>
              </td>
              <td><strong><?php echo format_inr($b['total_amount']); ?></strong></td>
              <td>
                <span class="badge badge-<?php echo ($b['payment_status'] === 'Paid') ? 'success' : 'warning'; ?>">
                  <?php echo htmlspecialchars($b['payment_status']); ?>
                </span>
              </td>
              <td>
                <button type="button" class="admin-btn-primary" onclick='openCheckinModal(<?php echo json_encode($b); ?>)'>
                  <i class="fa-solid fa-key"></i> Confirm Check-In
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Reception Check-In Confirmation -->
<div id="checkinModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.65); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: #FFFFFF; max-width: 620px; width: 90%; border-radius: 6px; padding: 2.5rem; border: 2px solid var(--admin-gold); box-shadow: 0 25px 60px rgba(0,0,0,0.3); position: relative;">
    <button onclick="closeCheckinModal()" style="position: absolute; top: 1.2rem; right: 1.2rem; background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
    
    <div style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1.2rem;">
      <div class="kpi-icon-box icon-green" style="width: 40px; height: 40px; font-size: 1.1rem;">
        <i class="fa-solid fa-bell-concierge"></i>
      </div>
      <div>
        <h3 style="font-size: 1.3rem; margin: 0;">Confirm Guest Check-In</h3>
        <span style="font-size: 0.8rem; color: var(--color-gold-dark);" id="modal_checkin_ref">RP-2026-XXXX</span>
      </div>
    </div>

    <div style="background: #F9FAFB; border: 1px solid var(--admin-border); border-radius: 4px; padding: 1.2rem; margin-bottom: 1.5rem; font-size: 0.88rem; line-height: 1.6;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem;">
        <div>Guest Name: <strong id="modal_checkin_name">—</strong></div>
        <div>Room Assigned: <strong id="modal_checkin_room">—</strong></div>
        <div>Govt ID Verified: <strong id="modal_checkin_id">—</strong></div>
        <div>Total Stay Cost: <strong id="modal_checkin_amount">—</strong></div>
      </div>
    </div>

    <form method="POST" action="checkin.php">
      <input type="hidden" name="action" value="confirm_checkin">
      <input type="hidden" name="booking_id" id="modal_checkin_id_field" value="">

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
        <div class="form-group">
          <label>Issue Electronic Keycard ID</label>
          <input type="text" name="keycard_number" id="modal_keycard" class="form-control" value="KEY-PALACE-<?php echo rand(101, 999); ?>" required>
        </div>

        <div class="form-group" style="display: flex; flex-direction: column; justify-content: center;">
          <label style="cursor: pointer; display: flex; align-items: center; gap: 0.5rem; margin-top: 1rem;">
            <input type="checkbox" name="settle_payment" value="1" checked style="width: 18px; height: 18px; accent-color: #03543F;">
            <span style="font-size: 0.82rem; font-weight: 700; color: #03543F;">Mark Balance Paid at Desk</span>
          </label>
        </div>
      </div>

      <div style="background: #DEF7EC; color: #03543F; padding: 0.8rem 1rem; border-radius: 4px; font-size: 0.82rem; margin-bottom: 1.5rem;">
        <i class="fa-solid fa-circle-info"></i> Clicking confirm will update the booking to <strong>Checked-In</strong> and set the room inventory status to <strong>Occupied</strong> in the database.
      </div>

      <div style="text-align: right;">
        <button type="button" class="btn-outline-dark" onclick="closeCheckinModal()" style="padding: 0.6rem 1.2rem; margin-right: 0.5rem;">Cancel</button>
        <button type="submit" class="admin-btn-primary" style="padding: 0.7rem 1.8rem; background: #03543F; border-color: #03543F; color: #FFFFFF;">
          <i class="fa-solid fa-check"></i> Complete Check-In
        </button>
      </div>
    </form>

  </div>
</div>

<script>
function openCheckinModal(b) {
  document.getElementById('modal_checkin_id_field').value = b.booking_id;
  document.getElementById('modal_checkin_ref').textContent = 'Booking Ref: ' + b.booking_ref;
  document.getElementById('modal_checkin_name').textContent = b.full_name;
  document.getElementById('modal_checkin_room').textContent = 'Room ' + b.room_number + ' (' + b.room_type + ')';
  document.getElementById('modal_checkin_id').textContent = b.id_type + ' (' + b.id_number + ')';
  document.getElementById('modal_checkin_amount').textContent = '₹' + parseFloat(b.total_amount).toLocaleString('en-IN');
  document.getElementById('checkinModal').style.display = 'flex';
}
function closeCheckinModal() {
  document.getElementById('checkinModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
