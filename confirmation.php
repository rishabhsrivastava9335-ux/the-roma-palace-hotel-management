<?php
/**
 * THE ROMA PALACE — Luxury Booking Confirmation & Tax Invoice Receipt
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Booking Confirmed — The Roma Palace';

$ref = isset($_GET['ref']) ? trim($_GET['ref']) : '';

if (empty($ref)) {
    header("Location: index.php");
    exit;
}

$booking = db_fetch_one("SELECT b.*, c.full_name, c.phone, c.address, c.id_type, c.id_number, u.email AS customer_email,
                                 h.name AS hotel_name, h.city, h.state, h.address AS hotel_address, h.phone AS hotel_phone, h.email AS hotel_email,
                                 r.room_number, r.room_type, r.floor, r.bed_type, r.price_per_night,
                                 p.payment_method, p.transaction_id, p.payment_date, p.status AS pay_status
                          FROM bookings b
                          INNER JOIN customers c ON b.customer_id = c.customer_id
                          INNER JOIN users u ON c.user_id = u.user_id
                          INNER JOIN hotels h ON b.hotel_id = h.hotel_id
                          INNER JOIN rooms r ON b.room_id = r.room_id
                          LEFT JOIN payments p ON b.booking_id = p.booking_id
                          WHERE b.booking_ref = ?", [$ref]);

if (!$booking) {
    header("Location: index.php");
    exit;
}

$services = db_fetch_all("SELECT bs.*, s.name, s.category FROM booking_services bs INNER JOIN services s ON bs.service_id = s.service_id WHERE bs.booking_id = ?", [$booking['booking_id']]);

$nights = max(1, round((strtotime($booking['check_out_date']) - strtotime($booking['check_in_date'])) / (60 * 60 * 24)));

require_once __DIR__ . '/includes/header.php';
?>

<section class="section-spacing bg-ivory" style="padding-top: 5rem;">
  <div class="container">
    
    <!-- Top Action Bar (Print & Return) -->
    <div style="max-width: 860px; margin: 0 auto 2rem auto; display: flex; justify-content: space-between; align-items: center;" class="no-print">
      <a href="index.php" class="btn-outline-dark" style="padding: 0.6rem 1.2rem; font-size: 0.8rem;">
        <i class="fa-solid fa-house"></i> Return Home
      </a>
      <div style="display: flex; gap: 0.8rem;">
        <button onclick="window.print()" class="btn-primary" style="padding: 0.6rem 1.5rem; font-size: 0.8rem;">
          <i class="fa-solid fa-print"></i> PRINT OFFICIAL TAX INVOICE
        </button>
      </div>
    </div>

    <!-- Official Printable Luxury Tax Invoice -->
    <div class="confirmation-invoice-wrapper">
      
      <!-- Invoice Header -->
      <div class="invoice-header">
        <div class="invoice-brand-block">
          <div style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 0.5rem;">
            <div class="rp-monogram" style="background: var(--color-charcoal);">
              <span>RP</span>
            </div>
            <div>
              <h2>THE ROMA PALACE</h2>
              <p>Luxury Hotels & Heritage Retreats</p>
            </div>
          </div>
          <p style="font-size: 0.82rem; color: var(--text-dark-secondary); margin-top: 0.5rem; line-height: 1.5;">
            <strong><?php echo htmlspecialchars($booking['hotel_name']); ?></strong><br>
            <?php echo htmlspecialchars($booking['hotel_address']); ?><br>
            GSTIN: 08AAAAA0000A1Z5 | Phone: <?php echo htmlspecialchars($booking['hotel_phone']); ?>
          </p>
        </div>

        <div class="invoice-meta-block">
          <span class="booking-ref-badge"><?php echo htmlspecialchars($booking['booking_ref']); ?></span>
          <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.3rem;">
            Date of Issue: <?php echo date('d M Y, h:i A'); ?>
          </div>
          <div style="margin-top: 0.5rem;">
            <span class="badge badge-success">BOOKING <?php echo strtoupper($booking['booking_status']); ?></span>
          </div>
        </div>
      </div>

      <!-- Guest & Stay Coordinates Grid -->
      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2.5rem; background: var(--color-ivory); padding: 1.5rem; border-radius: 3px; border: 1px solid var(--border-light);">
        
        <div>
          <strong style="font-family: var(--font-accent); font-size: 0.72rem; letter-spacing: 1.5px; text-transform: uppercase; color: var(--color-gold-dark); display: block; margin-bottom: 0.6rem;">
            Guest Information
          </strong>
          <h4 style="font-size: 1.1rem; color: var(--color-charcoal); margin-bottom: 0.3rem;"><?php echo htmlspecialchars($booking['full_name']); ?></h4>
          <div style="font-size: 0.86rem; color: var(--text-dark-secondary); line-height: 1.6;">
            Email: <?php echo htmlspecialchars($booking['customer_email']); ?><br>
            Phone: <?php echo htmlspecialchars($booking['phone']); ?><br>
            Govt ID: <?php echo htmlspecialchars($booking['id_type'] . ' (' . $booking['id_number'] . ')'); ?>
          </div>
        </div>

        <div>
          <strong style="font-family: var(--font-accent); font-size: 0.72rem; letter-spacing: 1.5px; text-transform: uppercase; color: var(--color-gold-dark); display: block; margin-bottom: 0.6rem;">
            Stay Schedule & Room
          </strong>
          <h4 style="font-size: 1.1rem; color: var(--color-charcoal); margin-bottom: 0.3rem;">
            <?php echo htmlspecialchars($booking['room_type']); ?> (Room <?php echo htmlspecialchars($booking['room_number']); ?>)
          </h4>
          <div style="font-size: 0.86rem; color: var(--text-dark-secondary); line-height: 1.6;">
            Check-in: <strong><?php echo format_stay_date($booking['check_in_date']); ?></strong> (02:00 PM)<br>
            Check-out: <strong><?php echo format_stay_date($booking['check_out_date']); ?></strong> (12:00 Noon)<br>
            Duration: <?php echo $nights; ?> Night<?php echo ($nights > 1) ? 's' : ''; ?> | <?php echo $booking['total_guests']; ?> Guests
          </div>
        </div>

      </div>

      <!-- Itemized Financial Breakdown Table -->
      <table style="width: 100%; border-collapse: collapse; margin-bottom: 2rem; font-size: 0.88rem;">
        <thead>
          <tr style="background: var(--color-charcoal); color: var(--color-gold-light);">
            <th style="padding: 0.8rem 1rem; text-align: left;">Description</th>
            <th style="padding: 0.8rem 1rem; text-align: center;">Qty / Nights</th>
            <th style="padding: 0.8rem 1rem; text-align: right;">Unit Rate</th>
            <th style="padding: 0.8rem 1rem; text-align: right;">Total Amount</th>
          </tr>
        </thead>
        <tbody>
          <!-- Room Charges -->
          <tr style="border-bottom: 1px solid var(--border-light);">
            <td style="padding: 1rem;">
              <strong>Accommodation: <?php echo htmlspecialchars($booking['room_type']); ?></strong><br>
              <small style="color: var(--text-muted);"><?php echo htmlspecialchars($booking['hotel_name']); ?></small>
            </td>
            <td style="padding: 1rem; text-align: center;"><?php echo $nights; ?> Night<?php echo ($nights > 1) ? 's' : ''; ?></td>
            <td style="padding: 1rem; text-align: right;"><?php echo format_inr($booking['price_per_night']); ?></td>
            <td style="padding: 1rem; text-align: right; font-weight: 600;"><?php echo format_inr($booking['room_charges']); ?></td>
          </tr>

          <!-- Selected Services -->
          <?php foreach ($services as $srv): ?>
            <tr style="border-bottom: 1px solid var(--border-light);">
              <td style="padding: 1rem;">
                <strong>Service: <?php echo htmlspecialchars($srv['name']); ?></strong><br>
                <small style="color: var(--text-muted);"><?php echo htmlspecialchars($srv['category']); ?></small>
              </td>
              <td style="padding: 1rem; text-align: center;"><?php echo $srv['quantity']; ?></td>
              <td style="padding: 1rem; text-align: right;"><?php echo format_inr($srv['unit_price']); ?></td>
              <td style="padding: 1rem; text-align: right; font-weight: 600;"><?php echo format_inr($srv['total_price']); ?></td>
            </tr>
          <?php endforeach; ?>

          <!-- Promo Discount if any -->
          <?php if ($booking['discount_amount'] > 0): ?>
            <tr style="border-bottom: 1px solid var(--border-light); color: #03543F;">
              <td style="padding: 0.8rem 1rem;" colspan="3">
                <strong>Privilege Promo Savings (<?php echo htmlspecialchars($booking['promo_code']); ?>)</strong>
              </td>
              <td style="padding: 0.8rem 1rem; text-align: right; font-weight: 600;">- <?php echo format_inr($booking['discount_amount']); ?></td>
            </tr>
          <?php endif; ?>

          <!-- Tax Row -->
          <tr style="border-bottom: 1px solid var(--border-light);">
            <td style="padding: 0.8rem 1rem;" colspan="3">
              <strong>Luxury Goods & Services Tax (GST 18%)</strong>
            </td>
            <td style="padding: 0.8rem 1rem; text-align: right; font-weight: 600;"><?php echo format_inr($booking['tax_amount']); ?></td>
          </tr>

          <!-- Grand Total -->
          <tr style="background: var(--color-cream); font-family: var(--font-serif-brand);">
            <td style="padding: 1.2rem 1rem; font-size: 1.1rem; font-weight: 700;" colspan="3">GRAND TOTAL (INR)</td>
            <td style="padding: 1.2rem 1rem; text-align: right; font-size: 1.3rem; font-weight: 700; color: var(--color-charcoal);"><?php echo format_inr($booking['total_amount']); ?></td>
          </tr>
        </tbody>
      </table>

      <!-- Payment & Transaction Details -->
      <div style="display: flex; justify-content: space-between; align-items: center; background: var(--color-ivory); padding: 1.2rem 1.5rem; border-radius: 2px; border-left: 4px solid var(--color-gold-dark); margin-bottom: 2rem;">
        <div>
          <span class="price-label">Settlement Mode</span>
          <strong style="color: var(--color-charcoal); font-size: 0.95rem;"><?php echo htmlspecialchars($booking['payment_method'] ?? 'UPI'); ?></strong>
        </div>
        <div>
          <span class="price-label">Transaction ID</span>
          <code style="background: var(--color-white); padding: 0.2rem 0.5rem; border-radius: 2px; border: 1px solid var(--border-light); font-weight: 700;"><?php echo htmlspecialchars($booking['transaction_id'] ?? 'TXN_SIMULATED_2026'); ?></code>
        </div>
        <div>
          <span class="price-label">Payment Status</span>
          <span class="badge badge-<?php echo ($booking['pay_status'] === 'Paid') ? 'success' : 'warning'; ?>"><?php echo strtoupper($booking['pay_status'] ?? 'PAID'); ?></span>
        </div>
      </div>

      <!-- Barcode / QR Simulation for Reception Check-in -->
      <div style="text-align: center; padding-top: 1.5rem; border-top: 1px dashed var(--border-light);">
        <div style="font-family: 'Courier New', monospace; letter-spacing: 5px; font-size: 1.4rem; color: var(--color-charcoal); margin-bottom: 0.4rem;">
          ||||| | |||| ||| |||||| | |||| |||||
        </div>
        <small style="color: var(--text-muted); font-size: 0.72rem; letter-spacing: 1px; text-transform: uppercase;">
          Please present this digital receipt or Booking Reference ID upon arrival at the Reception Desk.
        </small>
      </div>

    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
