<?php
/**
 * THE ROMA PALACE — Admin Layout Header & Navigation
 * BTech CSE DBMS Mini Project
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/auth.php';

require_admin('admin-login.php');

$currentAdmin = current_admin() ?: ['full_name' => 'Ranvijay Singh Rathore', 'role_title' => 'General Manager'];
$currentAdminPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | The Roma Palace Admin' : 'Admin Control Center | The Roma Palace'; ?></title>
  
  <!-- FontAwesome 6 Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Chart.js CDN -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

  <!-- Admin Design System -->
  <link rel="stylesheet" href="../assets/css/style.css">
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body class="admin-body">

  <!-- Left Executive Sidebar -->
  <aside class="admin-sidebar">
    
    <!-- Brand -->
    <a href="dashboard.php" class="admin-brand" style="text-decoration: none;">
      <div class="rp-monogram">
        <span>RP</span>
      </div>
      <div class="admin-brand-text">
        <h2>THE ROMA PALACE</h2>
        <span>ADMINISTRATION</span>
      </div>
    </a>

    <!-- Nav List -->
    <div class="admin-nav-section">
      
      <div class="admin-nav-title">CORE OPERATIONS</div>
      <ul class="admin-nav-list">
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'dashboard.php') ? 'active' : ''; ?>">
          <a href="dashboard.php"><i class="fa-solid fa-gauge-high"></i> <span>Dashboard</span></a>
        </li>
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'checkin.php') ? 'active' : ''; ?>">
          <a href="checkin.php"><i class="fa-solid fa-bell-concierge"></i> <span>Front Desk Check-In</span></a>
        </li>
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'checkout.php') ? 'active' : ''; ?>">
          <a href="checkout.php"><i class="fa-solid fa-receipt"></i> <span>Check-Out & Folio</span></a>
        </li>
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'bookings.php') ? 'active' : ''; ?>">
          <a href="bookings.php"><i class="fa-solid fa-calendar-check"></i> <span>Bookings Master</span></a>
        </li>
      </ul>

      <div class="admin-nav-title" style="margin-top: 1rem;">PROPERTY & INVENTORY</div>
      <ul class="admin-nav-list">
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'hotels.php') ? 'active' : ''; ?>">
          <a href="hotels.php"><i class="fa-solid fa-hotel"></i> <span>Hotels & Palaces</span></a>
        </li>
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'rooms.php') ? 'active' : ''; ?>">
          <a href="rooms.php"><i class="fa-solid fa-door-open"></i> <span>Rooms Management</span></a>
        </li>
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'services.php') ? 'active' : ''; ?>">
          <a href="services.php"><i class="fa-solid fa-spa"></i> <span>Services & Orders</span></a>
        </li>
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'restaurants.php') ? 'active' : ''; ?>">
          <a href="restaurants.php"><i class="fa-solid fa-utensils"></i> <span>Restaurants</span></a>
        </li>
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'menu.php') ? 'active' : ''; ?>">
          <a href="menu.php"><i class="fa-solid fa-book-open"></i> <span>Menu Items</span></a>
        </li>
      </ul>

      <div class="admin-nav-title" style="margin-top: 1rem;">ACCOUNTS & GUESTS</div>
      <ul class="admin-nav-list">
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'customers.php') ? 'active' : ''; ?>">
          <a href="customers.php"><i class="fa-solid fa-users"></i> <span>Customer CRM</span></a>
        </li>
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'payments.php') ? 'active' : ''; ?>">
          <a href="payments.php"><i class="fa-solid fa-credit-card"></i> <span>Payments Ledger</span></a>
        </li>
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'staff.php') ? 'active' : ''; ?>">
          <a href="staff.php"><i class="fa-solid fa-user-tie"></i> <span>Staff Directory</span></a>
        </li>
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'offers.php') ? 'active' : ''; ?>">
          <a href="offers.php"><i class="fa-solid fa-tags"></i> <span>Offers & Packages</span></a>
        </li>
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'reviews.php') ? 'active' : ''; ?>">
          <a href="reviews.php"><i class="fa-solid fa-star"></i> <span>Guest Reviews</span></a>
        </li>
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'reports.php') ? 'active' : ''; ?>">
          <a href="reports.php"><i class="fa-solid fa-chart-line"></i> <span>Reports & Exports</span></a>
        </li>
      </ul>

      <div class="admin-nav-title" style="margin-top: 1rem;">COLLEGE VIVA & SYSTEM</div>
      <ul class="admin-nav-list">
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'demo-presentation.php') ? 'active' : ''; ?>">
          <a href="demo-presentation.php">
            <i class="fa-solid fa-graduation-cap text-gold"></i> 
            <span>Demo Dashboard</span>
            <span class="admin-badge-viva">Viva</span>
          </a>
        </li>
        <li class="admin-nav-item <?php echo ($currentAdminPage === 'settings.php') ? 'active' : ''; ?>">
          <a href="settings.php"><i class="fa-solid fa-sliders"></i> <span>Settings & Reset</span></a>
        </li>
        <li class="admin-nav-item">
          <a href="logout.php"><i class="fa-solid fa-right-from-bracket"></i> <span>Sign Out</span></a>
        </li>
      </ul>

    </div>

  </aside>

  <!-- Main Administrative Container -->
  <div class="admin-main-wrapper">
    
    <!-- Top Administrative Header -->
    <header class="admin-top-header">
      <div class="admin-header-left">
        <h1 class="admin-page-title"><?php echo isset($pageHeading) ? htmlspecialchars($pageHeading) : 'Administration Control Center'; ?></h1>
        <span class="badge badge-info" style="font-size: 0.7rem;">
          <i class="fa-solid fa-database"></i> DB: <?php echo strtoupper(CURRENT_DB_DRIVER); ?>
        </span>
      </div>

      <div class="admin-header-right">
        <div class="admin-time-badge">
          <i class="fa-regular fa-clock text-gold"></i>
          <span id="adminLiveClock">--:--:--</span>
        </div>

        <a href="../index.php" target="_blank" class="btn-action-icon" title="Preview Public Luxury Website">
          <i class="fa-solid fa-arrow-up-right-from-square"></i>
        </a>

        <div class="admin-user-menu">
          <div class="admin-avatar">
            <?php echo strtoupper(substr($currentAdmin['full_name'], 0, 1)); ?>
          </div>
          <div class="admin-user-info">
            <span class="admin-user-name"><?php echo htmlspecialchars($currentAdmin['full_name']); ?></span>
            <span class="admin-user-role"><?php echo htmlspecialchars($currentAdmin['role_title']); ?></span>
          </div>
        </div>
      </div>
    </header>

    <!-- Flash Alerts -->
    <?php if (isset($_SESSION['flash_success'])): ?>
      <div style="margin: 1.5rem 2rem 0 2rem; background: #DEF7EC; color: #03543F; padding: 1rem 1.5rem; border-radius: 4px; border-left: 4px solid #31C48D; font-weight: 500; font-size: 0.9rem;">
        <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i>
        <?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
      </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_error'])): ?>
      <div style="margin: 1.5rem 2rem 0 2rem; background: #FDE8E8; color: #9B1C1C; padding: 1rem 1.5rem; border-radius: 4px; border-left: 4px solid #F05252; font-weight: 500; font-size: 0.9rem;">
        <i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i>
        <?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
      </div>
    <?php endif; ?>

    <!-- Body Container -->
    <main class="admin-content-body">
