<?php
/**
 * THE ROMA PALACE — Global Luxury Header & Navigation
 * BTech CSE DBMS Mini Project
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$currentUser = current_user();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' | The Roma Palace' : 'The Roma Palace | A Legacy of Luxury, A Stay to Remember'; ?></title>
  <meta name="description" content="Discover timeless hospitality, refined comfort and unforgettable experiences across India's most iconic luxury palace retreats.">
  
  <!-- FontAwesome 6 Icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  
  <!-- Custom Luxury Stylesheet -->
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

  <!-- Top Announcement / Demo Bar (College Viva Helper) -->
  <div style="background: #0C0D0F; color: #DFCAAB; font-size: 0.75rem; padding: 0.35rem 1rem; text-align: center; border-bottom: 1px solid rgba(197,168,128,0.25); letter-spacing: 0.5px; z-index: 1001; position: relative;">
    <span><i class="fa-solid fa-graduation-cap" style="color: #C5A880; margin-right: 5px;"></i> BTech CSE DBMS Mini Project — <strong>The Roma Palace</strong></span>
    <span style="margin: 0 10px; opacity: 0.4;">|</span>
    <a href="admin/admin-login.php" style="color: #FFFFFF; text-decoration: underline; font-weight: 600;"><i class="fa-solid fa-lock" style="font-size: 0.7rem;"></i> Admin Portal</a>
    <span style="margin: 0 10px; opacity: 0.4;">|</span>
    <a href="admin/demo-presentation.php" style="color: #C5A880; font-weight: 700;"><i class="fa-solid fa-chart-pie"></i> Professor Viva Dashboard</a>
  </div>

  <!-- Dynamic Sticky Navigation -->
  <header class="site-header <?php echo ($currentPage !== 'index.php') ? 'scrolled' : ''; ?>">
    <div class="nav-container">
      
      <!-- Brand Logo + RP Monogram -->
      <a href="index.php" class="brand-logo-link" title="The Roma Palace Homepage">
        <div class="rp-monogram">
          <span>RP</span>
        </div>
        <div class="brand-text">
          <span class="brand-title">THE ROMA PALACE</span>
          <span class="brand-subtitle">Luxury Hotels & Resorts</span>
        </div>
      </a>

      <!-- Main Navigation Menu -->
      <nav class="main-nav">
        <ul class="nav-links">
          <li><a href="hotels.php" class="nav-link <?php echo ($currentPage === 'hotels.php') ? 'active' : ''; ?>">PALACES</a></li>
          <li><a href="rooms.php" class="nav-link <?php echo ($currentPage === 'rooms.php' || $currentPage === 'room-details.php') ? 'active' : ''; ?>">ROOMS & SUITES</a></li>
          <li><a href="dining.php" class="nav-link <?php echo ($currentPage === 'dining.php') ? 'active' : ''; ?>">DINING</a></li>
          <li><a href="experiences.php" class="nav-link <?php echo ($currentPage === 'experiences.php') ? 'active' : ''; ?>">EXPERIENCES</a></li>
          <li><a href="offers.php" class="nav-link <?php echo ($currentPage === 'offers.php') ? 'active' : ''; ?>">OFFERS</a></li>
          <li><a href="wellness.php" class="nav-link <?php echo ($currentPage === 'wellness.php') ? 'active' : ''; ?>">WELLNESS</a></li>
          <li><a href="index.php#about" class="nav-link">ABOUT</a></li>
        </ul>
      </nav>

      <!-- Nav Actions (Login / Account + Book a Stay) -->
      <div class="nav-actions">
        <?php if ($currentUser): ?>
          <?php if ($currentUser['role'] === 'admin'): ?>
            <a href="admin/dashboard.php" class="btn-nav-login" title="Admin Control Center">
              <i class="fa-solid fa-gauge-high text-gold"></i>
              <span>Admin Panel</span>
            </a>
          <?php else: ?>
            <a href="customer-dashboard.php" class="btn-nav-login" title="My Roma Palace Account">
              <i class="fa-solid fa-user-circle text-gold"></i>
              <span><?php echo htmlspecialchars(explode(' ', $currentUser['name'])[0]); ?></span>
            </a>
          <?php endif; ?>
        <?php else: ?>
          <a href="login.php" class="btn-nav-login" title="Guest Sign In">
            <i class="fa-regular fa-user"></i>
            <span>LOGIN</span>
          </a>
        <?php endif; ?>

        <a href="booking.php" class="btn-book-nav">
          <i class="fa-solid fa-calendar-check"></i>
          <span>BOOK A STAY</span>
        </a>

        <button class="mobile-nav-toggle" aria-label="Toggle Navigation Menu">
          <i class="fa-solid fa-bars"></i>
        </button>
      </div>

    </div>
  </header>

  <!-- Flash Messages -->
  <?php if (isset($_SESSION['flash_success'])): ?>
    <div style="background: #DEF7EC; color: #03543F; padding: 1rem 2rem; text-align: center; font-weight: 600; font-size: 0.9rem; border-bottom: 1px solid #BCF0DA;">
      <i class="fa-solid fa-circle-check" style="margin-right: 6px;"></i>
      <?php echo htmlspecialchars($_SESSION['flash_success']); unset($_SESSION['flash_success']); ?>
    </div>
  <?php endif; ?>

  <?php if (isset($_SESSION['flash_error'])): ?>
    <div style="background: #FDE8E8; color: #9B1C1C; padding: 1rem 2rem; text-align: center; font-weight: 600; font-size: 0.9rem; border-bottom: 1px solid #FBD5D5;">
      <i class="fa-solid fa-circle-exclamation" style="margin-right: 6px;"></i>
      <?php echo htmlspecialchars($_SESSION['flash_error']); unset($_SESSION['flash_error']); ?>
    </div>
  <?php endif; ?>
