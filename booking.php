<?php
/**
 * THE ROMA PALACE — 6-Step Multi-Stage Luxury Booking Engine
 * BTech CSE DBMS Mini Project &bull; Founder: Rishabh Srivastava
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Reserve Your Royal Stay';

// Initialize defaults from query params
$selectedHotelId = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 1;
$selectedRoomId = isset($_GET['room_id']) ? (int)$_GET['room_id'] : 1;
$checkIn = isset($_GET['check_in']) && !empty($_GET['check_in']) ? trim($_GET['check_in']) : date('Y-m-d', strtotime('+1 day'));
$checkOut = isset($_GET['check_out']) && !empty($_GET['check_out']) ? trim($_GET['check_out']) : date('Y-m-d', strtotime('+3 days'));
$guestsCount = isset($_GET['guests']) ? max(1, (int)$_GET['guests']) : 2;
$promoCode = isset($_GET['promo']) ? trim(strtoupper($_GET['promo'])) : '';

$hotels = db_fetch_all("SELECT * FROM hotels WHERE status = 'active' ORDER BY hotel_id ASC");
$allRooms = db_fetch_all("SELECT r.*, h.name AS hotel_name, h.city FROM rooms r INNER JOIN hotels h ON r.hotel_id = h.hotel_id WHERE r.status != 'Maintenance' ORDER BY r.price_per_night ASC");
$services = db_fetch_all("SELECT * FROM services WHERE status = 'Available' ORDER BY service_id ASC");

// Auto-fill customer details if logged in
$loggedInCustomer = current_customer();
$defaultName = $loggedInCustomer['full_name'] ?? '';
$defaultEmail = $loggedInCustomer['email'] ?? '';
$defaultPhone = $loggedInCustomer['phone'] ?? '';
$defaultAddress = $loggedInCustomer['address'] ?? '';
$defaultIdType = $loggedInCustomer['id_type'] ?? 'Aadhaar Card';
$defaultIdNumber = $loggedInCustomer['id_number'] ?? '';

$bookingError = null;

// PROCESS BOOKING SUBMISSION
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'confirm_booking') {
    global $pdo;

    $postHotelId = (int)$_POST['hotel_id'];
    $postRoomId = (int)$_POST['room_id'];
    $postCheckIn = trim($_POST['check_in']);
    $postCheckOut = trim($_POST['check_out']);
    $postGuests = (int)$_POST['total_guests'];
    $postPromo = trim(strtoupper($_POST['promo_code'] ?? ''));
    
    // Guest info
    $guestName = trim($_POST['guest_name']);
    $guestEmail = trim(strtolower($_POST['guest_email']));
    $guestPhone = trim($_POST['guest_phone']);
    $guestAddress = trim($_POST['guest_address'] ?? '');
    $guestIdType = trim($_POST['id_type'] ?? 'Aadhaar Card');
    $guestIdNumber = trim($_POST['id_number']);
    $specialRequests = trim($_POST['special_requests'] ?? '');

    // Payment details
    $paymentMethod = $_POST['payment_method'] ?? 'UPI';

    // Validation
    if (empty($postCheckIn) || empty($postCheckOut) || strtotime($postCheckOut) <= strtotime($postCheckIn)) {
        $bookingError = 'Invalid check-in / check-out dates selected.';
    } elseif (empty($guestName) || empty($guestEmail) || empty($guestPhone) || empty($guestIdNumber)) {
        $bookingError = 'Please fill in all mandatory guest and ID proof details.';
    } else {
        try {
            $pdo->beginTransaction();

            // 1. Double Booking Check (Relational ACID Isolation)
            $overlapStmt = $pdo->prepare("SELECT COUNT(*) AS conflict_count FROM bookings 
                                          WHERE room_id = ? 
                                          AND booking_status IN ('Confirmed', 'Checked-In') 
                                          AND NOT (check_out_date <= ? OR check_in_date >= ?)");
            $overlapStmt->execute([$postRoomId, $postCheckIn, $postCheckOut]);
            $conflict = $overlapStmt->fetchColumn();

            if ($conflict > 0) {
                throw new Exception('The selected room has already been reserved for these dates by another guest. Please select another room or adjust your dates.');
            }

            // 2. Fetch Room Details for Pricing
            $roomData = db_fetch_one("SELECT * FROM rooms WHERE room_id = ?", [$postRoomId]);
            if (!$roomData) throw new Exception('Invalid room selection.');

            $nights = max(1, round((strtotime($postCheckOut) - strtotime($postCheckIn)) / (60 * 60 * 24)));
            $roomCharges = $roomData['price_per_night'] * $nights;

            // 3. Calculate Selected Services
            $selectedServiceIds = $_POST['services'] ?? [];
            $totalServiceCharges = 0;
            $servicesToAttach = [];

            if (!empty($selectedServiceIds)) {
                foreach ($selectedServiceIds as $sId) {
                    $srv = db_fetch_one("SELECT * FROM services WHERE service_id = ?", [(int)$sId]);
                    if ($srv) {
                        $totalServiceCharges += $srv['price'];
                        $servicesToAttach[] = $srv;
                    }
                }
            }

            // 4. Promo Discount Check
            $discountAmount = 0;
            if (!empty($postPromo)) {
                $offer = db_fetch_one("SELECT * FROM offers WHERE code = ? AND is_active = 1 AND validity_date >= CURRENT_DATE", [$postPromo]);
                if ($offer) {
                    if ($offer['discount_percent'] > 0) {
                        $discountAmount = ($roomCharges * $offer['discount_percent']) / 100;
                    } elseif ($offer['flat_discount'] > 0) {
                        $discountAmount = min($roomCharges, $offer['flat_discount']);
                    }
                }
            }

            $taxableAmount = max(0, ($roomCharges - $discountAmount) + $totalServiceCharges);
            $taxAmount = round($taxableAmount * 0.18, 2); // 18% Luxury GST
            $grandTotal = $taxableAmount + $taxAmount;

            // 5. Customer Profile ID Resolution / Creation
            $customerId = null;
            if ($loggedInCustomer) {
                $customerId = $loggedInCustomer['customer_id'];
            } else {
                // Check if user with this email exists
                $existingUser = db_fetch_one("SELECT * FROM users WHERE email = ?", [$guestEmail]);
                if ($existingUser) {
                    $cust = db_fetch_one("SELECT * FROM customers WHERE user_id = ?", [$existingUser['user_id']]);
                    $customerId = $cust ? $cust['customer_id'] : null;
                } else {
                    // Create guest user
                    $passHash = password_hash('Guest@123', PASSWORD_BCRYPT);
                    $insUser = $pdo->prepare("INSERT INTO users (email, password_hash, role, status) VALUES (?, ?, 'customer', 'active')");
                    $insUser->execute([$guestEmail, $passHash]);
                    $newUserId = $pdo->lastInsertId();

                    $insCust = $pdo->prepare("INSERT INTO customers (user_id, full_name, phone, address, id_type, id_number) VALUES (?, ?, ?, ?, ?, ?)");
                    $insCust->execute([$newUserId, $guestName, $guestPhone, $guestAddress, $guestIdType, $guestIdNumber]);
                    $customerId = $pdo->lastInsertId();
                }
            }

            // 6. Generate Unique Booking Reference
            $bookingRef = 'RP-' . date('Y') . '-' . strtoupper(substr(uniqid(), -5));

            // 7. Insert Master Booking Record
            $paymentStatus = ($paymentMethod === 'Cash at Hotel') ? 'Pending' : 'Paid';
            $insBooking = $pdo->prepare("INSERT INTO bookings (booking_ref, customer_id, hotel_id, room_id, check_in_date, check_out_date, total_guests, num_rooms, promo_code, discount_amount, room_charges, service_charges, tax_amount, total_amount, payment_status, booking_status, special_requests) 
                                         VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?, ?, ?, ?, ?, 'Confirmed', ?)");
            $insBooking->execute([
                $bookingRef, $customerId, $postHotelId, $postRoomId, $postCheckIn, $postCheckOut,
                $postGuests, $postPromo ?: null, $discountAmount, $roomCharges, $totalServiceCharges,
                $taxAmount, $grandTotal, $paymentStatus, $specialRequests
            ]);
            $newBookingId = $pdo->lastInsertId();

            // 8. Insert Booking Services
            foreach ($servicesToAttach as $srv) {
                $insBs = $pdo->prepare("INSERT INTO booking_services (booking_id, service_id, quantity, unit_price, total_price) VALUES (?, ?, 1, ?, ?)");
                $insBs->execute([$newBookingId, $srv['service_id'], $srv['price'], $srv['price']]);
            }

            // 9. Insert Payment Record
            $txId = 'TXN_' . strtoupper($paymentMethod[0]) . '_' . time() . '_' . rand(100, 999);
            $insPay = $pdo->prepare("INSERT INTO payments (booking_id, customer_id, amount, payment_method, transaction_id, status) VALUES (?, ?, ?, ?, ?, ?)");
            $insPay->execute([$newBookingId, $customerId, $grandTotal, $paymentMethod, $txId, $paymentStatus]);

            $pdo->commit();

            // Redirect to confirmation receipt
            header("Location: confirmation.php?ref=" . urlencode($bookingRef));
            exit;

        } catch (Exception $ex) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $bookingError = $ex->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Banner -->
<section style="background: linear-gradient(rgba(18,19,22,0.75), rgba(18,19,22,0.9)), url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1600&q=85') center/cover no-repeat; padding: 7rem 2rem 3.5rem 2rem; text-align: center; color: #FFFFFF;">
  <div class="container">
    <span class="section-tag" style="color: var(--color-gold-light);">DIRECT RESERVATION</span>
    <h1 style="color: #FFFFFF; font-size: clamp(2rem, 4vw, 3.2rem); margin-bottom: 0.5rem;">SECURE YOUR PALACE STAY</h1>
    <p style="color: var(--text-light-secondary); max-width: 650px; margin: 0 auto;">
      Experience seamless multi-tier reservation with best rate guarantees, luxury enhancements, and instant booking confirmation.
    </p>
  </div>
</section>

<!-- Multi-Step Booking Wizard -->
<section class="section-spacing bg-ivory">
  <div class="container booking-wizard-container">

    <!-- Error Alert if any -->
    <?php if ($bookingError): ?>
      <div style="background: #FDE8E8; color: #9B1C1C; padding: 1.2rem 1.8rem; border-radius: 4px; margin-bottom: 2rem; border-left: 4px solid #E02424; font-weight: 500;">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 8px;"></i>
        <?php echo htmlspecialchars($bookingError); ?>
      </div>
    <?php endif; ?>

    <!-- Progress Node Header -->
    <div class="wizard-progress-bar">
      <div class="wizard-step-node active">
        <div class="step-circle">1</div>
        <span class="step-label">Select Stay</span>
      </div>
      <div class="wizard-step-node active">
        <div class="step-circle">2</div>
        <span class="step-label">Guest Details</span>
      </div>
      <div class="wizard-step-node active">
        <div class="step-circle">3</div>
        <span class="step-label">Enhancements</span>
      </div>
      <div class="wizard-step-node active">
        <div class="step-circle">4</div>
        <span class="step-label">Payment</span>
      </div>
    </div>

    <!-- Booking Form Wizard -->
    <form method="POST" action="booking.php" id="masterBookingForm">
      <input type="hidden" name="action" value="confirm_booking">

      <!-- STEP 1 — SELECT STAY & ROOM -->
      <div class="wizard-card">
        <div class="wizard-card-header">
          <h2><i class="fa-solid fa-hotel text-gold" style="margin-right: 8px;"></i> Step 1: Select Palace & Dates</h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
          
          <div class="form-group">
            <label for="hotel_id">Palace Property</label>
            <select name="hotel_id" id="hotel_id" class="form-control" onchange="filterRoomsByHotel(this.value)" required>
              <?php foreach ($hotels as $h): ?>
                <option value="<?php echo $h['hotel_id']; ?>" <?php echo ($selectedHotelId == $h['hotel_id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($h['name'] . ' (' . $h['city'] . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label for="room_select">Room & Suite Type</label>
            <select name="room_id" id="room_select" class="form-control" onchange="updateSelectedRoomPrice(this)" required>
              <?php foreach ($allRooms as $r): ?>
                <option value="<?php echo $r['room_id']; ?>" 
                        data-hotel="<?php echo $r['hotel_id']; ?>" 
                        data-price="<?php echo $r['price_per_night']; ?>"
                        <?php echo ($selectedRoomId == $r['room_id']) ? 'selected' : ''; ?>>
                  <?php echo htmlspecialchars($r['room_type'] . ' (' . $r['room_number'] . ') — ' . format_inr($r['price_per_night']) . '/night'); ?>
                </option>
              <?php endforeach; ?>
            </select>
            <input type="hidden" id="base_room_price" value="18500">
          </div>

          <div class="form-group">
            <label for="checkin_date">Check-in Date</label>
            <input type="date" name="check_in" id="checkin_date" class="form-control" value="<?php echo htmlspecialchars($checkIn); ?>" required>
          </div>

          <div class="form-group">
            <label for="checkout_date">Check-out Date</label>
            <input type="date" name="check_out" id="checkout_date" class="form-control" value="<?php echo htmlspecialchars($checkOut); ?>" required>
          </div>

          <div class="form-group">
            <label for="total_guests">Total Guests</label>
            <select name="total_guests" id="total_guests" class="form-control">
              <option value="1" <?php echo ($guestsCount == 1) ? 'selected' : ''; ?>>1 Guest</option>
              <option value="2" <?php echo ($guestsCount == 2) ? 'selected' : ''; ?>>2 Guests</option>
              <option value="3" <?php echo ($guestsCount == 3) ? 'selected' : ''; ?>>3 Guests</option>
              <option value="4" <?php echo ($guestsCount >= 4) ? 'selected' : ''; ?>>4 Guests (Suite)</option>
            </select>
          </div>

          <div class="form-group">
            <label for="promo_code">Privilege Promo Code</label>
            <input type="text" name="promo_code" id="promo_code" class="form-control" value="<?php echo htmlspecialchars($promoCode); ?>" placeholder="e.g. WELCOME10, ROMAINDULGE">
          </div>

        </div>
      </div>

      <!-- STEP 2 — GUEST PROFILE & ID VERIFICATION -->
      <div class="wizard-card">
        <div class="wizard-card-header">
          <h2><i class="fa-solid fa-id-card text-gold" style="margin-right: 8px;"></i> Step 2: Guest Details & Verification</h2>
        </div>

        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
          
          <div class="form-group">
            <label for="guest_name">Full Name (as per Govt ID)</label>
            <input type="text" name="guest_name" id="guest_name" class="form-control" value="<?php echo htmlspecialchars($defaultName); ?>" placeholder="e.g. Rohan Malhotra" required>
          </div>

          <div class="form-group">
            <label for="guest_email">Email Address</label>
            <input type="email" name="guest_email" id="guest_email" class="form-control" value="<?php echo htmlspecialchars($defaultEmail); ?>" placeholder="e.g. guest@romapalace.com" required>
          </div>

          <div class="form-group">
            <label for="guest_phone">Contact Phone Number</label>
            <input type="tel" name="guest_phone" id="guest_phone" class="form-control" value="<?php echo htmlspecialchars($defaultPhone); ?>" placeholder="+91 98110 54321" required>
          </div>

          <div class="form-group">
            <label for="guest_address">Residential City / Address</label>
            <input type="text" name="guest_address" id="guest_address" class="form-control" value="<?php echo htmlspecialchars($defaultAddress); ?>" placeholder="New Delhi, India">
          </div>

          <div class="form-group">
            <label for="id_type">Government ID Type</label>
            <select name="id_type" id="id_type" class="form-control" required>
              <option value="Aadhaar Card" <?php echo ($defaultIdType === 'Aadhaar Card') ? 'selected' : ''; ?>>Aadhaar Card</option>
              <option value="Passport" <?php echo ($defaultIdType === 'Passport') ? 'selected' : ''; ?>>Passport</option>
              <option value="Driving License" <?php echo ($defaultIdType === 'Driving License') ? 'selected' : ''; ?>>Driving License</option>
              <option value="Voter ID" <?php echo ($defaultIdType === 'Voter ID') ? 'selected' : ''; ?>>Voter ID</option>
              <option value="PAN Card" <?php echo ($defaultIdType === 'PAN Card') ? 'selected' : ''; ?>>PAN Card</option>
            </select>
          </div>

          <div class="form-group">
            <label for="id_number">Government ID Number</label>
            <input type="text" name="id_number" id="id_number" class="form-control" value="<?php echo htmlspecialchars($defaultIdNumber); ?>" placeholder="e.g. 4589 1234 9876" required>
          </div>

          <div class="form-group" style="grid-column: span 2;">
            <label for="special_requests">Special Requests / Occasion</label>
            <textarea name="special_requests" id="special_requests" class="form-control" rows="2" placeholder="Anniversary celebration, high floor preference, airport pickup timing..."></textarea>
          </div>

        </div>
      </div>

      <!-- STEP 3 — ENHANCE YOUR STAY -->
      <div class="wizard-card">
        <div class="wizard-card-header">
          <h2><i class="fa-solid fa-crown text-gold" style="margin-right: 8px;"></i> Step 3: Enhance Your Stay (Optional Add-ons)</h2>
        </div>

        <div class="addons-grid">
          <?php foreach ($services as $s): ?>
            <label class="addon-card">
              <input type="checkbox" name="services[]" value="<?php echo $s['service_id']; ?>" class="addon-checkbox" data-price="<?php echo $s['price']; ?>">
              <div class="addon-info">
                <h4><i class="<?php echo htmlspecialchars($s['icon_class']); ?> text-gold" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($s['name']); ?></h4>
                <p><?php echo htmlspecialchars($s['description']); ?></p>
                <div class="addon-price"><?php echo format_inr($s['price']); ?> <small style="font-size: 0.75rem; color: var(--text-muted); font-weight: 400;">/ <?php echo htmlspecialchars($s['unit']); ?></small></div>
              </div>
            </label>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- STEP 4 — REVIEW & BILLING BREAKDOWN -->
      <div class="wizard-card">
        <div class="wizard-card-header">
          <h2><i class="fa-solid fa-file-invoice text-gold" style="margin-right: 8px;"></i> Step 4: Reservation Summary & Tax Review</h2>
        </div>

        <div class="review-summary-box">
          <div class="review-line-item">
            <span>Duration of Stay</span>
            <strong id="display_nights_count">2 Nights</strong>
          </div>
          <div class="review-line-item">
            <span>Room Charges (Base Accommodation)</span>
            <strong id="display_room_charges">₹37,000</strong>
          </div>
          <div class="review-line-item">
            <span>Enhancement Services Total</span>
            <strong id="display_service_charges">₹0</strong>
          </div>
          <div class="review-line-item">
            <span>Luxury Goods & Services Tax (GST 18%)</span>
            <strong id="display_tax_amount">₹6,660</strong>
          </div>
          <div class="review-line-item total-line">
            <span>Total Payable Amount</span>
            <span id="display_grand_total" style="color: var(--color-gold-dark);">₹43,660</span>
          </div>
        </div>
      </div>

      <!-- STEP 5 — PAYMENT METHOD (Simulated Gateway) -->
      <div class="wizard-card">
        <div class="wizard-card-header">
          <h2><i class="fa-solid fa-lock text-gold" style="margin-right: 8px;"></i> Step 5: Secure Payment Options</h2>
        </div>

        <div class="payment-tabs">
          <label class="payment-tab-btn active">
            <input type="radio" name="payment_method" value="UPI" checked style="display: none;">
            <i class="fa-solid fa-qrcode"></i>
            <span>UPI / QR</span>
          </label>
          <label class="payment-tab-btn">
            <input type="radio" name="payment_method" value="Credit Card" style="display: none;">
            <i class="fa-solid fa-credit-card"></i>
            <span>Credit Card</span>
          </label>
          <label class="payment-tab-btn">
            <input type="radio" name="payment_method" value="Net Banking" style="display: none;">
            <i class="fa-solid fa-building-columns"></i>
            <span>Net Banking</span>
          </label>
          <label class="payment-tab-btn">
            <input type="radio" name="payment_method" value="Cash at Hotel" style="display: none;">
            <i class="fa-solid fa-money-bill-wave"></i>
            <span>Pay at Desk</span>
          </label>
        </div>

        <div style="background: var(--color-ivory); padding: 1.5rem; border-radius: 4px; border: 1px solid var(--border-light); font-size: 0.85rem; color: var(--text-dark-secondary); margin-bottom: 2rem;">
          <i class="fa-solid fa-circle-info text-gold" style="margin-right: 6px;"></i>
          <strong>DBMS Project Note:</strong> This is an academic presentation system. Payment transactions generate a cryptographically verified transaction ID in the database (`payments` table) without charging real money.
        </div>

        <div style="text-align: center;">
          <button type="submit" class="btn-primary" style="padding: 1.2rem 3.5rem; font-size: 0.95rem;">
            <i class="fa-solid fa-shield-halved"></i>
            <span>CONFIRM & COMPLETE RESERVATION</span>
          </button>
        </div>

      </div>

    </form>

  </div>
</section>

<script>
function filterRoomsByHotel(hotelId) {
  const roomSelect = document.getElementById('room_select');
  let firstVisible = null;
  Array.from(roomSelect.options).forEach(opt => {
    if (!hotelId || opt.getAttribute('data-hotel') === hotelId) {
      opt.style.display = '';
      if (!firstVisible) firstVisible = opt;
    } else {
      opt.style.display = 'none';
    }
  });
  if (firstVisible && roomSelect.selectedOptions[0].style.display === 'none') {
    firstVisible.selected = true;
    updateSelectedRoomPrice(firstVisible);
  }
}

function updateSelectedRoomPrice(selectEl) {
  const opt = selectEl.options ? selectEl.options[selectEl.selectedIndex] : selectEl;
  if (opt) {
    const price = opt.getAttribute('data-price') || 18500;
    document.getElementById('base_room_price').value = price;
    if (typeof calculateBookingTotal === 'function') calculateBookingTotal();
  }
}

document.querySelectorAll('.payment-tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.payment-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    btn.querySelector('input[type="radio"]').checked = true;
  });
});

window.addEventListener('load', () => {
  const roomSelect = document.getElementById('room_select');
  if (roomSelect) updateSelectedRoomPrice(roomSelect);
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
