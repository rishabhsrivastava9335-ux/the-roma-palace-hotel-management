<?php
/**
 * THE ROMA PALACE — "MY ROMA PALACE" Customer Portal
 * BTech CSE DBMS Mini Project &bull; Founder: Rishabh Srivastava
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

require_customer('login.php');

$customer = current_customer();
$userId = $_SESSION['user_id'];
$customerId = $customer['customer_id'];

// Handle Booking Cancellation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel_booking') {
    $cancelBookingId = (int)$_POST['booking_id'];
    $bk = db_fetch_one("SELECT * FROM bookings WHERE booking_id = ? AND customer_id = ?", [$cancelBookingId, $customerId]);
    if ($bk && in_array($bk['booking_status'], ['Confirmed', 'Pending'])) {
        db_execute("UPDATE bookings SET booking_status = 'Cancelled', payment_status = 'Refunded' WHERE booking_id = ?", [$cancelBookingId]);
        db_execute("UPDATE rooms SET status = 'Available' WHERE room_id = ?", [$bk['room_id']]);
        $_SESSION['flash_success'] = "Reservation {$bk['booking_ref']} has been successfully cancelled. Any refund has been initiated.";
        header("Location: customer-dashboard.php");
        exit;
    }
}

// Handle Service Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'order_service') {
    $orderBookingId = (int)$_POST['booking_id'];
    $serviceId = (int)$_POST['service_id'];
    $qty = max(1, (int)$_POST['quantity']);
    $instructions = trim($_POST['instructions'] ?? '');

    $srv = db_fetch_one("SELECT * FROM services WHERE service_id = ?", [$serviceId]);
    if ($srv) {
        $total = $srv['price'] * $qty;
        db_execute("INSERT INTO service_orders (booking_id, customer_id, total_amount, status, instructions) VALUES (?, ?, ?, 'Pending', ?)", [
            $orderBookingId, $customerId, $total, $instructions
        ]);
        $orderId = db_insert_id();
        db_execute("INSERT INTO service_order_items (order_id, service_id, quantity, price) VALUES (?, ?, ?, ?)", [
            $orderId, $serviceId, $qty, $srv['price']
        ]);
        $_SESSION['flash_success'] = "Service request for '{$srv['name']}' placed successfully! Concierge notified.";
        header("Location: customer-dashboard.php#services");
        exit;
    }
}

// Handle Review Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_review') {
    $hotelId = (int)$_POST['hotel_id'];
    $rating = max(1, min(5, (int)$_POST['rating']));
    $title = trim($_POST['review_title']);
    $comments = trim($_POST['comments']);
    $stayDate = trim($_POST['stay_date'] ?? date('F Y'));

    if (!empty($title) && !empty($comments)) {
        db_execute("INSERT INTO reviews (customer_id, hotel_id, rating, review_title, comments, stay_date, is_approved) VALUES (?, ?, ?, ?, ?, ?, 1)", [
            $customerId, $hotelId, $rating, $title, $comments, $stayDate
        ]);
        $_SESSION['flash_success'] = "Thank you for sharing your royal feedback! Your review has been recorded.";
        header("Location: customer-dashboard.php#reviews");
        exit;
    }
}

// Fetch Customer Data
$bookings = db_fetch_all("SELECT b.*, h.name AS hotel_name, h.city, r.room_number, r.room_type, p.payment_method, p.transaction_id, p.status AS pay_status 
                          FROM bookings b 
                          INNER JOIN hotels h ON b.hotel_id = h.hotel_id 
                          INNER JOIN rooms r ON b.room_id = r.room_id 
                          LEFT JOIN payments p ON b.booking_id = p.booking_id 
                          WHERE b.customer_id = ? 
                          ORDER BY b.check_in_date DESC", [$customerId]);

$serviceOrders = db_fetch_all("SELECT so.*, b.booking_ref, s.name AS service_name, s.category, soi.quantity 
                               FROM service_orders so 
                               INNER JOIN bookings b ON so.booking_id = b.booking_id 
                               INNER JOIN service_order_items soi ON so.order_id = soi.order_id 
                               INNER JOIN services s ON soi.service_id = s.service_id 
                               WHERE so.customer_id = ? 
                               ORDER BY so.order_date DESC", [$customerId]);

$availableServices = db_fetch_all("SELECT * FROM services WHERE status = 'Available' ORDER BY category ASC");
$allHotels = db_fetch_all("SELECT * FROM hotels ORDER BY name ASC");

$pageTitle = 'My Roma Palace — Member Dashboard';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Portal Header -->
<section style="background: linear-gradient(135deg, #121316 0%, #1F1A17 100%); color: #FFFFFF; padding: 7.5rem 2rem 3.5rem 2rem; border-bottom: 2px solid var(--color-gold);">
  <div class="container">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
      <div style="display: flex; align-items: center; gap: 1.2rem;">
        <div class="rp-monogram" style="width: 54px; height: 54px; font-size: 1.3rem;">
          <span>RP</span>
        </div>
        <div>
          <span class="section-tag" style="color: var(--color-gold-light); margin-bottom: 0.2rem;">PRIVILEGED MEMBER</span>
          <h1 style="color: #FFFFFF; font-size: clamp(1.8rem, 3.5vw, 2.5rem); margin: 0;"><?php echo htmlspecialchars($customer['full_name']); ?></h1>
          <p style="color: var(--text-light-secondary); font-size: 0.88rem; margin-top: 0.2rem;">
            Member ID: #CUST-<?php echo str_pad($customerId, 4, '0', STR_PAD_LEFT); ?> | <?php echo htmlspecialchars($customer['email']); ?>
          </p>
        </div>
      </div>

      <div style="display: flex; gap: 0.8rem;">
        <a href="booking.php" class="btn-primary" style="padding: 0.75rem 1.4rem; font-size: 0.8rem;">
          <i class="fa-solid fa-calendar-plus"></i> NEW RESERVATION
        </a>
        <a href="logout.php" class="btn-outline-light" style="padding: 0.75rem 1.2rem; font-size: 0.8rem;">
          <i class="fa-solid fa-right-from-bracket"></i> Sign Out
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Dashboard Main Grid -->
<section class="section-spacing bg-ivory">
  <div class="container">
    
    <div style="display: grid; grid-template-columns: 1fr 2.4fr; gap: 2.5rem; align-items: start;">
      
      <!-- Left Profile & Verification Card -->
      <div style="background: var(--color-white); border-radius: 4px; border: 1px solid var(--border-light); padding: 2rem; box-shadow: var(--shadow-soft);">
        <h3 style="font-size: 1.2rem; margin-bottom: 1.2rem; padding-bottom: 0.8rem; border-bottom: 1px solid var(--border-light); color: var(--color-charcoal);">
          <i class="fa-solid fa-id-card-clip text-gold" style="margin-right: 6px;"></i> Guest Credentials
        </h3>

        <div style="font-size: 0.88rem; line-height: 1.8; color: var(--text-dark-secondary);">
          <div style="margin-bottom: 0.8rem;">
            <strong style="display: block; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Full Name</strong>
            <?php echo htmlspecialchars($customer['full_name']); ?>
          </div>

          <div style="margin-bottom: 0.8rem;">
            <strong style="display: block; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Contact Phone</strong>
            <?php echo htmlspecialchars($customer['phone']); ?>
          </div>

          <div style="margin-bottom: 0.8rem;">
            <strong style="display: block; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Government ID Proof</strong>
            <span class="badge badge-success"><?php echo htmlspecialchars($customer['id_type']); ?></span>
            <div style="font-family: monospace; font-weight: 600; margin-top: 2px;"><?php echo htmlspecialchars($customer['id_number']); ?></div>
          </div>

          <div style="margin-bottom: 0.8rem;">
            <strong style="display: block; font-size: 0.72rem; color: var(--text-muted); text-transform: uppercase;">Registered Address</strong>
            <?php echo htmlspecialchars($customer['address'] ? $customer['address'] . ', ' . $customer['city'] : 'India'); ?>
          </div>
        </div>

        <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--border-light); text-align: center;">
          <small style="color: var(--color-gold-dark); font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 0.8rem;">
            <i class="fa-solid fa-bell-concierge"></i> 24/7 ROYAL CONCIERGE ASSISTANCE
          </small>
          <div style="font-size: 0.82rem; color: var(--text-dark-secondary);">
            Dial <strong style="color: var(--color-charcoal);">+91 (0) 141 278 9000</strong> from your room phone.
          </div>
        </div>
      </div>

      <!-- Right Stays, Orders & Reviews Tabs -->
      <div>
        
        <!-- Bookings Master Ledger -->
        <div style="background: var(--color-white); border-radius: 4px; border: 1px solid var(--border-light); padding: 2.2rem; box-shadow: var(--shadow-soft); margin-bottom: 2.5rem;">
          <h2 style="font-size: 1.5rem; margin-bottom: 1.5rem; color: var(--color-charcoal); display: flex; align-items: center; justify-content: space-between;">
            <span><i class="fa-solid fa-hotel text-gold" style="margin-right: 8px;"></i> My Palace Stays & Reservations</span>
            <span style="font-size: 0.85rem; color: var(--text-muted); font-family: var(--font-sans); font-weight: 400;"><?php echo count($bookings); ?> Stays Recorded</span>
          </h2>

          <?php if (empty($bookings)): ?>
            <div style="text-align: center; padding: 2.5rem; background: var(--color-ivory); border-radius: 2px;">
              <p style="margin-bottom: 1rem;">You have not made any palace reservations yet.</p>
              <a href="booking.php" class="btn-primary">Book Your First Stay</a>
            </div>
          <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
              <?php foreach ($bookings as $b): ?>
                <div style="border: 1px solid var(--border-light); border-radius: 3px; padding: 1.5rem; background: var(--color-ivory); display: flex; flex-direction: column; gap: 1rem;">
                  
                  <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.8rem;">
                    <div>
                      <span class="booking-ref-badge" style="font-size: 0.88rem; padding: 0.2rem 0.6rem;"><?php echo htmlspecialchars($b['booking_ref']); ?></span>
                      <h4 style="font-size: 1.2rem; color: var(--color-charcoal); margin-top: 0.4rem;">
                        <?php echo htmlspecialchars($b['hotel_name']); ?>
                      </h4>
                      <div style="font-size: 0.85rem; color: var(--color-gold-dark); font-weight: 600;">
                        <?php echo htmlspecialchars($b['room_type']); ?> (Room <?php echo htmlspecialchars($b['room_number']); ?>)
                      </div>
                    </div>

                    <div style="text-align: right;">
                      <span class="badge badge-<?php 
                        if ($b['booking_status'] === 'Confirmed') echo 'success';
                        elseif ($b['booking_status'] === 'Checked-In') echo 'info';
                        elseif ($b['booking_status'] === 'Completed') echo 'secondary';
                        else echo 'danger';
                      ?>"><?php echo strtoupper($b['booking_status']); ?></span>
                      <div style="font-family: var(--font-serif-brand); font-size: 1.2rem; font-weight: 700; color: var(--color-charcoal); margin-top: 0.3rem;">
                        <?php echo format_inr($b['total_amount']); ?>
                      </div>
                    </div>
                  </div>

                  <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; font-size: 0.82rem; color: var(--text-dark-secondary); padding: 0.8rem 0; border-top: 1px solid var(--border-light); border-bottom: 1px solid var(--border-light);">
                    <div>Check-in: <strong><?php echo format_stay_date($b['check_in_date']); ?></strong></div>
                    <div>Check-out: <strong><?php echo format_stay_date($b['check_out_date']); ?></strong></div>
                    <div>Payment: <strong><?php echo htmlspecialchars($b['payment_status']); ?> (<?php echo htmlspecialchars($b['payment_method'] ?? 'UPI'); ?>)</strong></div>
                  </div>

                  <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.8rem;">
                    <a href="confirmation.php?ref=<?php echo urlencode($b['booking_ref']); ?>" class="btn-outline-dark" style="padding: 0.45rem 0.9rem; font-size: 0.75rem;">
                      <i class="fa-solid fa-receipt"></i> View Official Invoice
                    </a>

                    <div style="display: flex; gap: 0.6rem;">
                      <?php if (in_array($b['booking_status'], ['Confirmed', 'Checked-In'])): ?>
                        <button type="button" class="btn-primary" style="padding: 0.45rem 0.9rem; font-size: 0.75rem;" onclick="openServiceModal(<?php echo $b['booking_id']; ?>, '<?php echo htmlspecialchars($b['booking_ref']); ?>')">
                          <i class="fa-solid fa-bell-concierge"></i> Request In-Room Service
                        </button>
                      <?php endif; ?>

                      <?php if ($b['booking_status'] === 'Confirmed'): ?>
                        <form method="POST" action="customer-dashboard.php" onsubmit="return confirm('Are you sure you want to cancel booking <?php echo $b['booking_ref']; ?>?');" style="display: inline;">
                          <input type="hidden" name="action" value="cancel_booking">
                          <input type="hidden" name="booking_id" value="<?php echo $b['booking_id']; ?>">
                          <button type="submit" style="background: #FDE8E8; color: #9B1C1C; border: 1px solid #F8B4B4; padding: 0.45rem 0.9rem; border-radius: 2px; font-size: 0.75rem; font-weight: 600; cursor: pointer;">
                            Cancel Stay
                          </button>
                        </form>
                      <?php endif; ?>
                    </div>
                  </div>

                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

        </div>

        <!-- Service Orders Tracker -->
        <div style="background: var(--color-white); border-radius: 4px; border: 1px solid var(--border-light); padding: 2.2rem; box-shadow: var(--shadow-soft); margin-bottom: 2.5rem;" id="services">
          <h2 style="font-size: 1.4rem; margin-bottom: 1.2rem; color: var(--color-charcoal);">
            <i class="fa-solid fa-bell-concierge text-gold" style="margin-right: 8px;"></i> On-Demand In-Room Service Orders
          </h2>

          <?php if (empty($serviceOrders)): ?>
            <p style="font-size: 0.88rem; color: var(--text-muted);">No in-room service requests placed yet.</p>
          <?php else: ?>
            <table class="admin-table">
              <thead>
                <tr>
                  <th>Stay Ref</th>
                  <th>Service Requested</th>
                  <th>Quantity</th>
                  <th>Total Cost</th>
                  <th>Order Status</th>
                  <th>Date</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($serviceOrders as $so): ?>
                  <tr>
                    <td><strong><?php echo htmlspecialchars($so['booking_ref']); ?></strong></td>
                    <td><?php echo htmlspecialchars($so['service_name']); ?></td>
                    <td><?php echo $so['quantity']; ?></td>
                    <td><strong><?php echo format_inr($so['total_amount']); ?></strong></td>
                    <td><span class="badge badge-<?php echo ($so['status'] === 'Delivered') ? 'success' : 'warning'; ?>"><?php echo htmlspecialchars($so['status']); ?></span></td>
                    <td><?php echo date('d M Y, h:i A', strtotime($so['order_date'])); ?></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>

        <!-- Submit Review Section -->
        <div style="background: var(--color-white); border-radius: 4px; border: 1px solid var(--border-light); padding: 2.2rem; box-shadow: var(--shadow-soft);" id="reviews">
          <h2 style="font-size: 1.4rem; margin-bottom: 0.5rem; color: var(--color-charcoal);">
            <i class="fa-solid fa-star text-gold" style="margin-right: 8px;"></i> Share Your Palace Experience
          </h2>
          <p style="font-size: 0.85rem; color: var(--text-dark-secondary); margin-bottom: 1.5rem;">Help future guests discover the grandeur of The Roma Palace.</p>

          <form method="POST" action="customer-dashboard.php">
            <input type="hidden" name="action" value="submit_review">

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1rem;">
              <div class="form-group">
                <label for="rev_hotel">Palace Visited</label>
                <select name="hotel_id" id="rev_hotel" class="form-control" required>
                  <?php foreach ($allHotels as $ht): ?>
                    <option value="<?php echo $ht['hotel_id']; ?>"><?php echo htmlspecialchars($ht['name'] . ' (' . $ht['city'] . ')'); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="form-group">
                <label for="rev_rating">Rating (1 to 5 Stars)</label>
                <select name="rating" id="rev_rating" class="form-control" required>
                  <option value="5" selected>⭐⭐⭐⭐⭐ (5 Stars — Exceptional)</option>
                  <option value="4">⭐⭐⭐⭐ (4 Stars — Excellent)</option>
                  <option value="3">⭐⭐⭐ (3 Stars — Good)</option>
                  <option value="2">⭐⭐ (2 Stars — Fair)</option>
                  <option value="1">⭐ (1 Star — Poor)</option>
                </select>
              </div>
            </div>

            <div class="form-group">
              <label for="rev_title">Review Headline</label>
              <input type="text" name="review_title" id="rev_title" class="form-control" placeholder="e.g. An Unmatched Epitome of Regal Hospitality" required>
            </div>

            <div class="form-group">
              <label for="rev_comments">Your Review Details</label>
              <textarea name="comments" id="rev_comments" class="form-control" rows="3" placeholder="Tell us about the service, dining, courtyards, and overall stay..." required></textarea>
            </div>

            <button type="submit" class="btn-primary" style="padding: 0.8rem 1.8rem; font-size: 0.8rem;">
              <i class="fa-solid fa-paper-plane"></i> SUBMIT ROYAL REVIEW
            </button>
          </form>
        </div>

      </div>

    </div>

  </div>
</section>

<!-- In-Room Service Request Modal -->
<div id="serviceRequestModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 2000; align-items: center; justify-content: center;">
  <div style="background: #FFFFFF; max-width: 500px; width: 90%; border-radius: 4px; padding: 2.5rem; border: 1px solid var(--border-gold); box-shadow: var(--shadow-luxury); position: relative;">
    <button onclick="closeServiceModal()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.3rem; cursor: pointer;">&times;</button>
    
    <h3 style="font-size: 1.3rem; color: var(--color-charcoal); margin-bottom: 0.3rem;">Request In-Room Service</h3>
    <p style="font-size: 0.82rem; color: var(--color-gold-dark); margin-bottom: 1.5rem;" id="modalBookingRef">Booking: RP-2026-XXXX</p>

    <form method="POST" action="customer-dashboard.php">
      <input type="hidden" name="action" value="order_service">
      <input type="hidden" name="booking_id" id="modalBookingId" value="">

      <div class="form-group">
        <label for="modal_service">Select Service</label>
        <select name="service_id" id="modal_service" class="form-control" required>
          <?php foreach ($availableServices as $as): ?>
            <option value="<?php echo $as['service_id']; ?>">
              <?php echo htmlspecialchars($as['name'] . ' (' . format_inr($as['price']) . ' - ' . $as['category'] . ')'); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="modal_qty">Quantity</label>
        <input type="number" name="quantity" id="modal_qty" value="1" min="1" max="10" class="form-control" required>
      </div>

      <div class="form-group">
        <label for="modal_notes">Delivery Time / Special Instructions</label>
        <textarea name="instructions" id="modal_notes" class="form-control" rows="2" placeholder="e.g. Please deliver by 8:30 PM, extra ice..."></textarea>
      </div>

      <button type="submit" class="btn-primary" style="width: 100%; padding: 0.85rem;">
        SUBMIT SERVICE ORDER
      </button>
    </form>
  </div>
</div>

<script>
function openServiceModal(bookingId, ref) {
  document.getElementById('modalBookingId').value = bookingId;
  document.getElementById('modalBookingRef').textContent = 'For Reservation: ' + ref;
  document.getElementById('serviceRequestModal').style.display = 'flex';
}
function closeServiceModal() {
  document.getElementById('serviceRequestModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
