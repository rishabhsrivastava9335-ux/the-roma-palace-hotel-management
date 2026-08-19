<?php
/**
 * THE ROMA PALACE — Payments & Financial Ledger
 * BTech CSE DBMS Mini Project &bull; Founder: Rishabh Srivastava
 */
require_once __DIR__ . '/includes/admin-header.php';

// Handle Refund Action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'refund_payment') {
    $paymentId = (int)$_POST['payment_id'];
    $p = db_fetch_one("SELECT * FROM payments WHERE payment_id = ?", [$paymentId]);
    if ($p) {
        db_execute("UPDATE payments SET status = 'Refunded' WHERE payment_id = ?", [$paymentId]);
        db_execute("UPDATE bookings SET payment_status = 'Refunded', booking_status = 'Cancelled' WHERE booking_id = ?", [$p['booking_id']]);
        $_SESSION['flash_success'] = "Payment {$p['transaction_id']} refunded and booking cancelled.";
    }
    header("Location: payments.php");
    exit;
}

$payments = db_fetch_all("SELECT p.*, b.booking_ref, c.full_name, c.phone, h.name AS hotel_name 
                          FROM payments p 
                          INNER JOIN bookings b ON p.booking_id = b.booking_id 
                          INNER JOIN customers c ON p.customer_id = c.customer_id 
                          INNER JOIN hotels h ON b.hotel_id = h.hotel_id 
                          ORDER BY p.payment_id DESC");

$totalCollected = db_fetch_one("SELECT SUM(amount) AS total FROM payments WHERE status = 'Paid'")['total'] ?? 0;
$totalRefunded = db_fetch_one("SELECT SUM(amount) AS total FROM payments WHERE status = 'Refunded'")['total'] ?? 0;

$pageHeading = 'Financial Transactions & Payment Ledger';
?>

<div class="kpi-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 2rem;">
  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">TOTAL REVENUE COLLECTED</span>
      <span class="kpi-value" style="color: #03543F;"><?php echo format_inr($totalCollected); ?></span>
      <span class="kpi-subtitle">Verified & Settled</span>
    </div>
    <div class="kpi-icon-box icon-green"><i class="fa-solid fa-circle-check"></i></div>
  </div>

  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">TOTAL TRANSACTIONS</span>
      <span class="kpi-value"><?php echo count($payments); ?></span>
      <span class="kpi-subtitle">Ledger Audit Entries</span>
    </div>
    <div class="kpi-icon-box icon-blue"><i class="fa-solid fa-receipt"></i></div>
  </div>

  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">REFUNDS PROCESSED</span>
      <span class="kpi-value" style="color: #9B1C1C;"><?php echo format_inr($totalRefunded); ?></span>
      <span class="kpi-subtitle">Cancelled Reservations</span>
    </div>
    <div class="kpi-icon-box icon-red"><i class="fa-solid fa-rotate-left"></i></div>
  </div>
</div>

<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-credit-card text-gold"></i> Payment Transactions Ledger</h3>
    <div class="admin-actions-bar">
      <div class="search-input-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search TXN ID, guest, ref..." data-table-search="paymentsTable">
      </div>
    </div>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table" id="paymentsTable">
      <thead>
        <tr>
          <th>Transaction ID</th>
          <th>Booking Ref</th>
          <th>Guest Details</th>
          <th>Palace Property</th>
          <th>Amount (INR)</th>
          <th>Payment Mode</th>
          <th>Date & Time</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($payments as $p): ?>
          <tr>
            <td><code style="font-size: 0.82rem; font-weight: 700; background: #F3F4F6; padding: 0.2rem 0.5rem; border-radius: 2px;"><?php echo htmlspecialchars($p['transaction_id']); ?></code></td>
            <td><strong><?php echo htmlspecialchars($p['booking_ref']); ?></strong></td>
            <td>
              <strong><?php echo htmlspecialchars($p['full_name']); ?></strong><br>
              <small style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($p['phone']); ?></small>
            </td>
            <td><?php echo htmlspecialchars($p['hotel_name']); ?></td>
            <td><strong style="font-size: 1rem; color: var(--admin-primary);"><?php echo format_inr($p['amount']); ?></strong></td>
            <td><span class="badge badge-secondary"><?php echo htmlspecialchars($p['payment_method']); ?></span></td>
            <td><?php echo date('d M Y, h:i A', strtotime($p['payment_date'])); ?></td>
            <td>
              <span class="badge badge-<?php echo ($p['status'] === 'Paid') ? 'success' : (($p['status'] === 'Refunded') ? 'danger' : 'warning'); ?>">
                <?php echo strtoupper($p['status']); ?>
              </span>
            </td>
            <td>
              <?php if ($p['status'] === 'Paid'): ?>
                <form method="POST" action="payments.php" onsubmit="return confirm('Process refund for transaction <?php echo $p['transaction_id']; ?>?');" style="display: inline;">
                  <input type="hidden" name="action" value="refund_payment">
                  <input type="hidden" name="payment_id" value="<?php echo $p['payment_id']; ?>">
                  <button type="submit" class="btn-action-icon" style="color: #9B1C1C;" title="Process Refund">
                    <i class="fa-solid fa-rotate-left"></i>
                  </button>
                </form>
              <?php else: ?>
                <span style="color: var(--admin-text-muted); font-size: 0.75rem;">—</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
