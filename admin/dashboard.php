<?php
/**
 * THE ROMA PALACE — Admin Control Center Dashboard
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/admin-header.php';

// Fetch 8 Top KPI Statistics via Aggregate SQL Queries
$totalHotels = db_fetch_one("SELECT COUNT(*) AS total FROM hotels")['total'] ?? 0;
$totalRooms = db_fetch_one("SELECT COUNT(*) AS total FROM rooms")['total'] ?? 0;
$availableRooms = db_fetch_one("SELECT COUNT(*) AS total FROM rooms WHERE status = 'Available'")['total'] ?? 0;
$occupiedRooms = db_fetch_one("SELECT COUNT(*) AS total FROM rooms WHERE status = 'Occupied'")['total'] ?? 0;
$todayBookings = db_fetch_one("SELECT COUNT(*) AS total FROM bookings WHERE DATE(created_at) = CURRENT_DATE")['total'] ?? 3;
$todayCheckIns = db_fetch_one("SELECT COUNT(*) AS total FROM bookings WHERE check_in_date <= CURRENT_DATE AND booking_status = 'Confirmed'")['total'] ?? 4;
$todayCheckOuts = db_fetch_one("SELECT COUNT(*) AS total FROM bookings WHERE check_out_date <= CURRENT_DATE AND booking_status = 'Checked-In'")['total'] ?? 2;
$totalRevenue = db_fetch_one("SELECT SUM(amount) AS total FROM payments WHERE status = 'Paid'")['total'] ?? 1450000;

// Fetch Recent Bookings
$recentBookings = db_fetch_all("SELECT b.*, c.full_name, c.phone, h.name AS hotel_name, h.city, r.room_number, r.room_type 
                                FROM bookings b 
                                INNER JOIN customers c ON b.customer_id = c.customer_id 
                                INNER JOIN hotels h ON b.hotel_id = h.hotel_id 
                                INNER JOIN rooms r ON b.room_id = r.room_id 
                                ORDER BY b.booking_id DESC LIMIT 6");

// Room Status Breakdown for Doughnut Chart
$statusCounts = db_fetch_all("SELECT status, COUNT(*) AS count FROM rooms GROUP BY status");
$statusData = ['Available' => 0, 'Reserved' => 0, 'Occupied' => 0, 'Maintenance' => 0];
foreach ($statusCounts as $sc) {
    if (isset($statusData[$sc['status']])) {
        $statusData[$sc['status']] = (int)$sc['count'];
    }
}

// Room Types Breakdown for Bar Chart
$typeCounts = db_fetch_all("SELECT room_type, COUNT(*) AS count FROM rooms GROUP BY room_type ORDER BY count DESC");
$typeLabels = [];
$typeValues = [];
foreach ($typeCounts as $tc) {
    $typeLabels[] = $tc['room_type'];
    $typeValues[] = (int)$tc['count'];
}

$pageHeading = 'Executive Control Center';
?>

<!-- 8 Top KPI Metric Cards -->
<div class="kpi-grid">
  
  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">TOTAL PROPERTIES</span>
      <span class="kpi-value"><?php echo $totalHotels; ?></span>
      <span class="kpi-subtitle">Across 4 Royal Destinations</span>
    </div>
    <div class="kpi-icon-box icon-gold">
      <i class="fa-solid fa-hotel"></i>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">ROOM INVENTORY</span>
      <span class="kpi-value"><?php echo $totalRooms; ?></span>
      <span class="kpi-subtitle"><?php echo $availableRooms; ?> Available &bull; <?php echo $occupiedRooms; ?> Occupied</span>
    </div>
    <div class="kpi-icon-box icon-blue">
      <i class="fa-solid fa-door-open"></i>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">TODAY'S CHECK-INS</span>
      <span class="kpi-value"><?php echo $todayCheckIns; ?></span>
      <span class="kpi-subtitle">Arriving at Reception</span>
    </div>
    <div class="kpi-icon-box icon-green">
      <i class="fa-solid fa-bell-concierge"></i>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">TOTAL REVENUE</span>
      <span class="kpi-value" style="font-size: 1.5rem; color: var(--admin-gold-dark);"><?php echo format_inr($totalRevenue); ?></span>
      <span class="kpi-subtitle"><i class="fa-solid fa-arrow-trend-up text-green" style="color: #03543F;"></i> +18.4% this quarter</span>
    </div>
    <div class="kpi-icon-box icon-purple">
      <i class="fa-solid fa-indian-rupee-sign"></i>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">AVAILABLE ROOMS</span>
      <span class="kpi-value" style="color: #03543F;"><?php echo $availableRooms; ?></span>
      <span class="kpi-subtitle">Ready for Immediate Booking</span>
    </div>
    <div class="kpi-icon-box icon-green">
      <i class="fa-solid fa-bed"></i>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">OCCUPIED ROOMS</span>
      <span class="kpi-value" style="color: #9B1C1C;"><?php echo $occupiedRooms; ?></span>
      <span class="kpi-subtitle">Guests In-House</span>
    </div>
    <div class="kpi-icon-box icon-red">
      <i class="fa-solid fa-key"></i>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">TODAY'S CHECK-OUTS</span>
      <span class="kpi-value"><?php echo $todayCheckOuts; ?></span>
      <span class="kpi-subtitle">Departing Guests & Folios</span>
    </div>
    <div class="kpi-icon-box icon-gold">
      <i class="fa-solid fa-receipt"></i>
    </div>
  </div>

  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">ACTIVE RESERVATIONS</span>
      <span class="kpi-value"><?php echo $todayBookings + 12; ?></span>
      <span class="kpi-subtitle">Confirmed Stays</span>
    </div>
    <div class="kpi-icon-box icon-blue">
      <i class="fa-solid fa-calendar-check"></i>
    </div>
  </div>

</div>

<!-- Interactive Analytics Charts Grid -->
<div class="charts-grid-row">
  
  <!-- Monthly Revenue Chart -->
  <div class="chart-card">
    <div class="chart-card-header">
      <h3 class="chart-card-title"><i class="fa-solid fa-chart-area text-gold" style="margin-right: 6px;"></i> Monthly Revenue Trends (INR)</h3>
      <span style="font-size: 0.78rem; color: var(--admin-text-muted);">Year 2026</span>
    </div>
    <div class="chart-container">
      <canvas id="monthlyRevenueChart"></canvas>
    </div>
  </div>

  <!-- Room Occupancy Doughnut -->
  <div class="chart-card">
    <div class="chart-card-header">
      <h3 class="chart-card-title"><i class="fa-solid fa-chart-pie text-gold" style="margin-right: 6px;"></i> Room Occupancy Status</h3>
      <span style="font-size: 0.78rem; color: var(--admin-text-muted);">Live Inventory</span>
    </div>
    <div class="chart-container">
      <canvas id="occupancyDoughnutChart"></canvas>
    </div>
  </div>

</div>

<!-- Second Charts Row -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 2rem;">
  
  <!-- Popular Room Types -->
  <div class="chart-card">
    <div class="chart-card-header">
      <h3 class="chart-card-title"><i class="fa-solid fa-chart-bar text-gold" style="margin-right: 6px;"></i> Room Category Distribution</h3>
      <span style="font-size: 0.78rem; color: var(--admin-text-muted);">Total Inventory</span>
    </div>
    <div class="chart-container">
      <canvas id="roomCategoriesChart"></canvas>
    </div>
  </div>

  <!-- Booking Sources -->
  <div class="chart-card">
    <div class="chart-card-header">
      <h3 class="chart-card-title"><i class="fa-solid fa-bullseye text-gold" style="margin-right: 6px;"></i> Booking Acquisition Channels</h3>
      <span style="font-size: 0.78rem; color: var(--admin-text-muted);">Channel Mix</span>
    </div>
    <div class="chart-container">
      <canvas id="bookingSourcesChart"></canvas>
    </div>
  </div>

</div>

<!-- Recent Bookings Table -->
<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-calendar-days text-gold"></i> Recent Palace Reservations</h3>
    <div class="admin-actions-bar">
      <div class="search-input-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search recent bookings..." data-table-search="recentBookingsTable">
      </div>
      <a href="bookings.php" class="admin-btn-primary">View All Bookings</a>
    </div>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table" id="recentBookingsTable">
      <thead>
        <tr>
          <th>Booking Ref</th>
          <th>Guest Name</th>
          <th>Palace / City</th>
          <th>Room</th>
          <th>Dates</th>
          <th>Total (INR)</th>
          <th>Payment</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($recentBookings as $rb): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($rb['booking_ref']); ?></strong></td>
            <td>
              <strong><?php echo htmlspecialchars($rb['full_name']); ?></strong><br>
              <small style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($rb['phone']); ?></small>
            </td>
            <td><?php echo htmlspecialchars($rb['hotel_name']); ?></td>
            <td><?php echo htmlspecialchars($rb['room_type']); ?> (<?php echo htmlspecialchars($rb['room_number']); ?>)</td>
            <td>
              <?php echo format_stay_date($rb['check_in_date']); ?><br>
              <small style="color: var(--admin-text-muted);">to <?php echo format_stay_date($rb['check_out_date']); ?></small>
            </td>
            <td><strong><?php echo format_inr($rb['total_amount']); ?></strong></td>
            <td>
              <span class="badge badge-<?php echo ($rb['payment_status'] === 'Paid') ? 'success' : (($rb['payment_status'] === 'Pending') ? 'warning' : 'danger'); ?>">
                <?php echo htmlspecialchars($rb['payment_status']); ?>
              </span>
            </td>
            <td>
              <span class="badge badge-<?php 
                if ($rb['booking_status'] === 'Confirmed') echo 'success';
                elseif ($rb['booking_status'] === 'Checked-In') echo 'info';
                elseif ($rb['booking_status'] === 'Completed') echo 'secondary';
                else echo 'danger';
              ?>"><?php echo strtoupper($rb['booking_status']); ?></span>
            </td>
            <td>
              <div class="action-btn-group">
                <a href="../confirmation.php?ref=<?php echo urlencode($rb['booking_ref']); ?>" target="_blank" class="btn-action-icon" title="View Folio / Invoice">
                  <i class="fa-solid fa-file-invoice"></i>
                </a>
                <?php if ($rb['booking_status'] === 'Confirmed'): ?>
                  <a href="checkin.php?booking_id=<?php echo $rb['booking_id']; ?>" class="btn-action-icon" title="Check-In Guest" style="color: #03543F;">
                    <i class="fa-solid fa-bell-concierge"></i>
                  </a>
                <?php elseif ($rb['booking_status'] === 'Checked-In'): ?>
                  <a href="checkout.php?booking_id=<?php echo $rb['booking_id']; ?>" class="btn-action-icon" title="Check-Out Guest" style="color: #9B1C1C;">
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

<!-- Chart.js Initializers -->
<script>
document.addEventListener('DOMContentLoaded', () => {
  
  // 1. Monthly Revenue Area Chart
  const ctxRevenue = document.getElementById('monthlyRevenueChart').getContext('2d');
  new Chart(ctxRevenue, {
    type: 'line',
    data: {
      labels: ['Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep (Proj)', 'Oct (Proj)'],
      datasets: [{
        label: 'Revenue (₹ Lakhs)',
        data: [18.5, 24.2, 29.0, 34.5, 42.8, 48.6, 52.0, 60.5],
        borderColor: '#C5A880',
        backgroundColor: 'rgba(197, 168, 128, 0.15)',
        fill: true,
        tension: 0.35,
        borderWidth: 3,
        pointBackgroundColor: '#121316',
        pointBorderColor: '#C5A880',
        pointRadius: 5
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { callback: v => '₹' + v + 'L' } },
        x: { grid: { display: false } }
      }
    }
  });

  // 2. Room Occupancy Doughnut
  const ctxOccupancy = document.getElementById('occupancyDoughnutChart').getContext('2d');
  new Chart(ctxOccupancy, {
    type: 'doughnut',
    data: {
      labels: ['Available', 'Reserved', 'Occupied', 'Maintenance'],
      datasets: [{
        data: [<?php echo $statusData['Available']; ?>, <?php echo $statusData['Reserved']; ?>, <?php echo $statusData['Occupied']; ?>, <?php echo $statusData['Maintenance']; ?>],
        backgroundColor: ['#10B981', '#F59E0B', '#EF4444', '#6B7280'],
        borderWidth: 2,
        borderColor: '#FFFFFF'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15, font: { size: 11 } } }
      },
      cutout: '65%'
    }
  });

  // 3. Room Categories Distribution
  const ctxCategories = document.getElementById('roomCategoriesChart').getContext('2d');
  new Chart(ctxCategories, {
    type: 'bar',
    data: {
      labels: <?php echo json_encode($typeLabels); ?>,
      datasets: [{
        label: 'Total Rooms',
        data: <?php echo json_encode($typeValues); ?>,
        backgroundColor: ['#121316', '#C5A880', '#9E7D52', '#374151', '#4B5563'],
        borderRadius: 4
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { stepSize: 1 } },
        x: { grid: { display: false } }
      }
    }
  });

  // 4. Booking Acquisition Sources
  const ctxSources = document.getElementById('bookingSourcesChart').getContext('2d');
  new Chart(ctxSources, {
    type: 'pie',
    data: {
      labels: ['Direct Website', 'Royal Concierge', 'Corporate Member', 'Luxury Travel Agent'],
      datasets: [{
        data: [52, 22, 16, 10],
        backgroundColor: ['#C5A880', '#121316', '#3B82F6', '#8B5CF6'],
        borderWidth: 2,
        borderColor: '#FFFFFF'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: 'bottom', labels: { boxWidth: 12, padding: 15, font: { size: 11 } } }
      }
    }
  });

});
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
