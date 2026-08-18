<?php
/**
 * THE ROMA PALACE — Comprehensive DBMS Analytical Reports & Exports
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Handle CSV Export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    $reportType = $_GET['type'] ?? 'revenue';
    
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=roma_palace_' . $reportType . '_report_' . date('Ymd_His') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    if ($reportType === 'revenue') {
        fputcsv($output, ['Hotel / Property', 'City', 'Total Bookings', 'Gross Revenue (INR)', 'Tax Collected (GST 18%)', 'Avg Booking Value']);
        $rows = db_fetch_all("SELECT h.name, h.city, COUNT(b.booking_id) AS bookings_count,
                                     COALESCE(SUM(b.total_amount), 0) AS gross_revenue,
                                     COALESCE(SUM(b.tax_amount), 0) AS total_tax,
                                     COALESCE(AVG(b.total_amount), 0) AS avg_booking
                              FROM hotels h
                              LEFT JOIN bookings b ON h.hotel_id = b.hotel_id AND b.payment_status = 'Paid'
                              GROUP BY h.hotel_id, h.name, h.city");
        foreach ($rows as $r) {
            fputcsv($output, [$r['name'], $r['city'], $r['bookings_count'], $r['gross_revenue'], $r['total_tax'], round($r['avg_booking'], 2)]);
        }
    } elseif ($reportType === 'guests') {
        fputcsv($output, ['Customer ID', 'Full Name', 'Phone', 'Email', 'ID Proof', 'Total Stays', 'Lifetime Spend (INR)']);
        $rows = db_fetch_all("SELECT c.customer_id, c.full_name, c.phone, u.email, c.id_type,
                                     COUNT(b.booking_id) AS total_stays,
                                     COALESCE(SUM(b.total_amount), 0) AS lifetime_spend
                              FROM customers c
                              INNER JOIN users u ON c.user_id = u.user_id
                              LEFT JOIN bookings b ON c.customer_id = b.customer_id AND b.payment_status = 'Paid'
                              GROUP BY c.customer_id, c.full_name, c.phone, u.email, c.id_type
                              ORDER BY lifetime_spend DESC");
        foreach ($rows as $r) {
            fputcsv($output, [$r['customer_id'], $r['full_name'], $r['phone'], $r['email'], $r['id_type'], $r['total_stays'], $r['lifetime_spend']]);
        }
    }
    fclose($output);
    exit;
}

require_once __DIR__ . '/includes/admin-header.php';

// 1. Property-Wise Financial Breakdown
$propertyFinancials = db_fetch_all("SELECT h.hotel_id, h.name AS hotel_name, h.city,
                                           COUNT(b.booking_id) AS total_bookings,
                                           COALESCE(SUM(b.room_charges), 0) AS total_room_charges,
                                           COALESCE(SUM(b.service_charges), 0) AS total_service_charges,
                                           COALESCE(SUM(b.tax_amount), 0) AS total_tax,
                                           COALESCE(SUM(b.total_amount), 0) AS gross_revenue
                                    FROM hotels h
                                    LEFT JOIN bookings b ON h.hotel_id = b.hotel_id AND b.payment_status = 'Paid'
                                    GROUP BY h.hotel_id, h.name, h.city
                                    ORDER BY gross_revenue DESC");

// 2. Room Occupancy & Inventory Utilization
$occupancyReport = db_fetch_all("SELECT h.name AS hotel_name,
                                        COUNT(r.room_id) AS total_rooms,
                                        SUM(CASE WHEN r.status = 'Occupied' THEN 1 ELSE 0 END) AS occupied_rooms,
                                        SUM(CASE WHEN r.status = 'Available' THEN 1 ELSE 0 END) AS available_rooms,
                                        SUM(CASE WHEN r.status = 'Maintenance' THEN 1 ELSE 0 END) AS maintenance_rooms,
                                        ROUND((SUM(CASE WHEN r.status = 'Occupied' THEN 1 ELSE 0 END) * 100.0 / COUNT(r.room_id)), 1) AS occupancy_rate
                                 FROM hotels h
                                 INNER JOIN rooms r ON h.hotel_id = r.hotel_id
                                 GROUP BY h.hotel_id, h.name");

// 3. High-Value Customer LTV (Top 5 Patrons)
$topCustomers = db_fetch_all("SELECT c.customer_id, c.full_name, c.phone, c.id_type, c.city,
                                     COUNT(b.booking_id) AS total_stays,
                                     SUM(b.total_amount) AS lifetime_value
                              FROM customers c
                              INNER JOIN bookings b ON c.customer_id = b.customer_id AND b.payment_status = 'Paid'
                              GROUP BY c.customer_id, c.full_name, c.phone, c.id_type, c.city
                              ORDER BY lifetime_value DESC LIMIT 5");

// 4. Service & Experience Popularity Ledger
$servicesReport = db_fetch_all("SELECT s.name AS service_name, s.category, s.price,
                                       COALESCE(SUM(bs.quantity), 0) AS units_sold,
                                       COALESCE(SUM(bs.total_price), 0) AS total_revenue
                                FROM services s
                                LEFT JOIN booking_services bs ON s.service_id = bs.service_id
                                GROUP BY s.service_id, s.name, s.category, s.price
                                ORDER BY total_revenue DESC");

$pageHeading = 'DBMS Analytical Reports & Financial Audits';
?>

<!-- Action Bar for Printing & CSV Export -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;" class="no-print">
  <div>
    <h2 style="font-size: 1.4rem; color: var(--admin-primary); margin: 0;">Management Audit & DBMS Reports</h2>
    <span style="font-size: 0.85rem; color: var(--admin-text-muted);">Real-time aggregated relational queries across 18 tables</span>
  </div>
  
  <div style="display: flex; gap: 0.8rem;">
    <a href="reports.php?export=csv&type=revenue" class="admin-btn-primary" style="background: #03543F; border-color: #03543F; color: #FFFFFF;">
      <i class="fa-solid fa-file-csv"></i> Export Revenue CSV
    </a>
    <a href="reports.php?export=csv&type=guests" class="admin-btn-primary" style="background: #1E40AF; border-color: #1E40AF; color: #FFFFFF;">
      <i class="fa-solid fa-file-csv"></i> Export Guest LTV CSV
    </a>
    <button onclick="window.print()" class="btn-outline-dark" style="padding: 0.6rem 1.2rem; font-weight: 600;">
      <i class="fa-solid fa-print"></i> Print Official Report
    </button>
  </div>
</div>

<!-- Report 1: Property Financial Performance -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-indian-rupee-sign text-gold"></i> Property-Wise Financial Breakdown</h3>
    <span style="font-size: 0.8rem; color: var(--admin-text-muted);">SQL Aggregate Query (SUM, COUNT, GROUP BY)</span>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Palace Destination</th>
          <th>Total Bookings</th>
          <th>Room Revenue</th>
          <th>Service Revenue</th>
          <th>GST Collected (18%)</th>
          <th>Gross Revenue (INR)</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $grandGross = 0; $grandTax = 0;
        foreach ($propertyFinancials as $pf): 
          $grandGross += $pf['gross_revenue'];
          $grandTax += $pf['total_tax'];
        ?>
          <tr>
            <td>
              <strong><?php echo htmlspecialchars($pf['hotel_name']); ?></strong><br>
              <small style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($pf['city']); ?></small>
            </td>
            <td><?php echo $pf['total_bookings']; ?> Confirmed</td>
            <td><?php echo format_inr($pf['total_room_charges']); ?></td>
            <td><?php echo format_inr($pf['total_service_charges']); ?></td>
            <td><?php echo format_inr($pf['total_tax']); ?></td>
            <td><strong style="font-size: 1rem; color: var(--admin-gold-dark);"><?php echo format_inr($pf['gross_revenue']); ?></strong></td>
          </tr>
        <?php endforeach; ?>
        <tr style="background: #F9FAFB; font-weight: 700; border-top: 2px solid var(--admin-border);">
          <td>TOTAL FINANCIAL SUMMARY</td>
          <td>—</td>
          <td>—</td>
          <td>—</td>
          <td><?php echo format_inr($grandTax); ?></td>
          <td style="color: #03543F; font-size: 1.15rem;"><?php echo format_inr($grandGross); ?></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Report 2: Occupancy Rate Utilization -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-chart-pie text-gold"></i> Occupancy & Inventory Utilization</h3>
    <span style="font-size: 0.8rem; color: var(--admin-text-muted);">Live Room Status Aggregation</span>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Palace Property</th>
          <th>Total Rooms</th>
          <th>Occupied</th>
          <th>Available</th>
          <th>Maintenance</th>
          <th>Occupancy Rate</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($occupancyReport as $or): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($or['hotel_name']); ?></strong></td>
            <td><?php echo $or['total_rooms']; ?> Rooms</td>
            <td><strong style="color: #9B1C1C;"><?php echo $or['occupied_rooms']; ?></strong></td>
            <td><strong style="color: #03543F;"><?php echo $or['available_rooms']; ?></strong></td>
            <td><?php echo $or['maintenance_rooms']; ?></td>
            <td>
              <div style="display: flex; align-items: center; gap: 0.8rem;">
                <div style="flex: 1; height: 8px; background: #E5E7EB; border-radius: 4px; overflow: hidden;">
                  <div style="width: <?php echo $or['occupancy_rate']; ?>%; height: 100%; background: var(--admin-gold-dark);"></div>
                </div>
                <strong><?php echo $or['occupancy_rate']; ?>%</strong>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Report 3: High-Value Customer LTV -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-crown text-gold"></i> High-Value Guest Patronage (Top LTV)</h3>
    <span style="font-size: 0.8rem; color: var(--admin-text-muted);">Customer Lifetime Value Ranking</span>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Patron Name</th>
          <th>Contact & ID</th>
          <th>City</th>
          <th>Completed Stays</th>
          <th>Lifetime Spend (INR)</th>
          <th>Patron Tier</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($topCustomers as $tc): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($tc['full_name']); ?></strong></td>
            <td>
              <small><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($tc['phone']); ?></small><br>
              <span class="badge badge-info" style="font-size: 0.68rem;"><?php echo htmlspecialchars($tc['id_type']); ?></span>
            </td>
            <td><?php echo htmlspecialchars($tc['city'] ?: 'India'); ?></td>
            <td><strong><?php echo $tc['total_stays']; ?> Stays</strong></td>
            <td><strong style="font-size: 1.05rem; color: var(--admin-gold-dark);"><?php echo format_inr($tc['lifetime_value']); ?></strong></td>
            <td><span class="badge badge-warning"><i class="fa-solid fa-gem"></i> Imperial Patron</span></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
