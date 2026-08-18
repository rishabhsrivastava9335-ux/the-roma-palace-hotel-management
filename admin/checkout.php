<?php
/**
 * THE ROMA PALACE — Front Desk Reception Check-Out & Folio Terminal
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/admin-header.php';

$searchQuery = trim($_GET['search'] ?? '');
$activeBookingId = isset($_GET['booking_id']) ? (int)$_GET['booking_id'] : null;

// Handle Check-Out Settlement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_checkout') {
    $bookingId = (int)$_POST['booking_id'];
    $extraCharges = (float)($_POST['extra_charges'] ?? 0);
    $extraReason = trim($_POST['extra_reason'] ?? 'Minibar & Final Incidentals');

    $bk = db_fetch_one("SELECT * FROM bookings WHERE booking_id = ?", [$bookingId]);
    if ($bk && $bk['booking_status'] === 'Checked-In') {
        global $pdo;
        try {
            $pdo->beginTransaction();

            $newTotal = $bk['total_amount'] + $extraCharges;

            // 1. If extra incidentals added, record into payments / booking
            if ($extraCharges > 0) {
                db_execute("UPDATE bookings SET total_amount = total_amount + ?, service_charges = service_charges + ? WHERE booking_id = ?", [
                    $extraCharges, $extraCharges, $bookingId
                ]);
                $txId = 'TXN_CHECKOUT_' . time();
                db_execute("INSERT INTO payments (booking_id, customer_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, 'Credit Card', ?, 'Paid')", [
                    $bookingId, $bk['customer_id'], $extraCharges, $txId
                ]);
            }

            // 2. Mark Booking as Completed
            db_execute("UPDATE bookings SET booking_status = 'Completed', payment_status = 'Paid' WHERE booking_id = ?", [$bookingId]);

            // 3. Mark Room Inventory as Available (Relational Transition)
            db_execute("UPDATE rooms SET status = 'Available' WHERE room_id = ?", [$bk['room_id']]);

            $pdo->commit();
            $_SESSION['flash_success'] = "Check-Out completed for {$bk['booking_ref']}! Room inventory released back to AVAILABLE.";
            header("Location: checkout.php");
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $_SESSION['flash_error'] = "Check-Out failed: " . $e->getMessage();
        }
    }
}

// Fetch Active In-House Guests (Checked-In)
$whereSql = "b.booking_status = 'Checked-In'";
$params = [];

if (!empty($searchQuery)) {
    $whereSql .= " AND (b.booking_ref LIKE ? OR c.full_name LIKE ? OR c.phone LIKE ? OR r.room_number LIKE ?)";
    $term = "%{$searchQuery}%";
    $params = [$term, $term, $term, $term];
}

$inHouseBookings = db_fetch_all("SELECT b.*, c.full_name, c.phone, c.id_type, c.id_number,
                                        h.name AS hotel_name, r.room_number, r.room_type, r.price_per_night,
                                        p.payment_method, p.transaction_id, p.status AS pay_status
                                 FROM bookings b
                                 INNER JOIN customers c ON b.customer_id = c.customer_id
                                 INNER JOIN hotels h ON b.hotel_id = h.hotel_id
                                 INNER JOIN rooms r ON b.room_id = r.room_id
                                 LEFT JOIN payments p ON b.booking_id = p.booking_id
                                 WHERE {$whereSql}
                                 ORDER BY b.check_out_date ASC", $params);

$pageHeading = 'Front Desk Reception — Guest Check-Out & Folio Settlement';
?>

<!-- Reception Welcome Bar -->
<div class="viva-banner" style="margin-bottom: 2rem; border-left-color: #9B1C1C;">
  <h2 style="color: #FCA5A5;"><i class="fa-solid fa-receipt"></i> Reception Check-Out & Final Folio Settlement</h2>
  <p style="margin: 0.3rem 0 0 0; color: #DFCAAB; font-size: 0.9rem;">
    Process departing in-house guests, review room stay durations, append any incidental / minibar charges, finalize tax folio, and automatically trigger room release (<code>Occupied &rarr; Available</code>).
  </p>
</div>

<!-- Search & In-House List -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-bed text-gold"></i> Currently In-House Guests (<?php echo count($inHouseBookings); ?> Occupied Rooms)</h3>
    <form method="GET" action="checkout.php" class="search-input-box">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" name="search" value="<?php echo htmlspecialchars($searchQuery); ?>" placeholder="Search Room No, Booking ID, Guest..." style="width: 320px;">
    </form>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Room Number</th>
          <th>Guest Name & ID</th>
          <th>Palace Location</th>
          <th>Stay Window</th>
          <th>Settled Amount</th>
          <th>Payment</th>
          <th>Front Desk Settlement</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($inHouseBookings)): ?>
          <tr>
            <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--admin-text-muted);">
              <i class="fa-solid fa-door-open" style="font-size: 2rem; color: var(--admin-gold); margin-bottom: 0.5rem; display: block;"></i>
              No active in-house guests found matching the search criteria.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($inHouseBookings as $b): ?>
            <tr>
              <td>
                <strong style="font-size: 1.15rem; color: #9B1C1C;">Room <?php echo htmlspecialchars($b['room_number']); ?></strong><br>
                <small style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($b['room_type']); ?></small>
              </td>
              <td>
                <strong><?php echo htmlspecialchars($b['full_name']); ?></strong><br>
                <small style="color: var(--admin-text-muted);">Ref: <?php echo htmlspecialchars($b['booking_ref']); ?></small><br>
                <small><i class="fa-solid fa-phone" style="font-size: 0.7rem;"></i> <?php echo htmlspecialchars($b['phone']); ?></small>
              </td>
              <td><?php echo htmlspecialchars($b['hotel_name']); ?></td>
              <td>
                Checked In: <strong><?php echo format_stay_date($b['check_in_date']); ?></strong><br>
                Departure: <strong><?php echo format_stay_date($b['check_out_date']); ?></strong>
              </td>
              <td><strong style="font-size: 1rem;"><?php echo format_inr($b['total_amount']); ?></strong></td>
              <td>
                <span class="badge badge-success"><?php echo htmlspecialchars($b['payment_status']); ?></span>
              </td>
              <td>
                <button type="button" class="admin-btn-primary" style="background: #9B1C1C; border-color: #9B1C1C; color: #FFFFFF;" onclick='openCheckoutModal(<?php echo json_encode($b); ?>)'>
                  <i class="fa-solid fa-file-invoice-dollar"></i> Settle & Check-Out
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Final Folio Settlement & Check-Out -->
<div id="checkoutModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.65); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: #FFFFFF; max-width: 650px; width: 90%; border-radius: 6px; padding: 2.5rem; border: 2px solid #9B1C1C; box-shadow: 0 25px 60px rgba(0,0,0,0.3); position: relative;">
    <button onclick="closeCheckoutModal()" style="position: absolute; top: 1.2rem; right: 1.2rem; background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
    
    <div style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1.2rem;">
      <div class="kpi-icon-box icon-red" style="width: 40px; height: 40px; font-size: 1.1rem;">
        <i class="fa-solid fa-receipt"></i>
      </div>
      <div>
        <h3 style="font-size: 1.3rem; margin: 0;">Final Folio Settlement & Check-Out</h3>
        <span style="font-size: 0.8rem; color: var(--color-gold-dark);" id="modal_checkout_ref">RP-2026-XXXX</span>
      </div>
    </div>

    <div style="background: #F9FAFB; border: 1px solid var(--admin-border); border-radius: 4px; padding: 1.2rem; margin-bottom: 1.5rem; font-size: 0.88rem; line-height: 1.6;">
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.8rem;">
        <div>Guest Name: <strong id="modal_checkout_name">—</strong></div>
        <div>Occupied Room: <strong id="modal_checkout_room">—</strong></div>
        <div>Base Stay Total: <strong id="modal_checkout_amount">—</strong></div>
        <div>Payment Status: <span class="badge badge-success">SETTLED</span></div>
      </div>
    </div>

    <form method="POST" action="checkout.php">
      <input type="hidden" name="action" value="complete_checkout">
      <input type="hidden" name="booking_id" id="modal_checkout_id_field" value="">

      <div style="border: 1px dashed var(--admin-border); border-radius: 4px; padding: 1.2rem; margin-bottom: 1.5rem; background: var(--color-ivory);">
        <h4 style="font-size: 0.95rem; margin-bottom: 0.8rem; color: var(--admin-primary);"><i class="fa-solid fa-martini-glass-citrus text-gold"></i> Last-Minute Incidental / Minibar Add-ons (Optional)</h4>
        
        <div style="display: grid; grid-template-columns: 1fr 1.5fr; gap: 1rem;">
          <div class="form-group">
            <label>Incidental Amount (INR)</label>
            <input type="number" step="10" name="extra_charges" id="modal_extra_charges" value="0" min="0" class="form-control">
          </div>
          <div class="form-group">
            <label>Charge Description</label>
            <input type="text" name="extra_reason" class="form-control" value="Minibar & Late Dining Bill">
          </div>
        </div>
      </div>

      <div style="background: #DEF7EC; color: #03543F; padding: 0.8rem 1rem; border-radius: 4px; font-size: 0.82rem; margin-bottom: 1.5rem;">
        <i class="fa-solid fa-circle-check"></i> Completing check-out will mark booking <strong>Completed</strong> and automatically update the room inventory status to <strong>Available</strong> for new bookings.
      </div>

      <div style="text-align: right;">
        <button type="button" class="btn-outline-dark" onclick="closeCheckoutModal()" style="padding: 0.6rem 1.2rem; margin-right: 0.5rem;">Cancel</button>
        <button type="submit" class="admin-btn-primary" style="padding: 0.7rem 1.8rem; background: #9B1C1C; border-color: #9B1C1C; color: #FFFFFF;">
          <i class="fa-solid fa-door-open"></i> Complete Check-Out & Release Room
        </button>
      </div>
    </form>

  </div>
</div>

<script>
function openCheckoutModal(b) {
  document.getElementById('modal_checkout_id_field').value = b.booking_id;
  document.getElementById('modal_checkout_ref').textContent = 'Booking Ref: ' + b.booking_ref;
  document.getElementById('modal_checkout_name').textContent = b.full_name;
  document.getElementById('modal_checkout_room').textContent = 'Room ' + b.room_number + ' (' + b.room_type + ')';
  document.getElementById('modal_checkout_amount').textContent = '₹' + parseFloat(b.total_amount).toLocaleString('en-IN');
  document.getElementById('modal_extra_charges').value = '0';
  document.getElementById('checkoutModal').style.display = 'flex';
}
function closeCheckoutModal() {
  document.getElementById('checkoutModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
