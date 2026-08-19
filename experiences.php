<?php
/**
 * THE ROMA PALACE — Experiences Beyond The Room
 * BTech CSE DBMS Mini Project &bull; Founder: Rishabh Srivastava
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Experiences Beyond The Room';
$experiences = db_fetch_all("SELECT e.*, h.name AS hotel_name, h.city FROM experiences e LEFT JOIN hotels h ON e.hotel_id = h.hotel_id WHERE e.is_active = 1 ORDER BY e.experience_id ASC");

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Banner -->
<section style="background: linear-gradient(rgba(18,19,22,0.7), rgba(18,19,22,0.85)), url('https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=1600&q=85') center/cover no-repeat; padding: 7rem 2rem 4rem 2rem; text-align: center; color: #FFFFFF;">
  <div class="container">
    <span class="section-tag" style="color: var(--color-gold-light);">TIMELESS MOMENTS</span>
    <h1 style="color: #FFFFFF; font-size: clamp(2rem, 4vw, 3.2rem); margin-bottom: 0.8rem;">EXPERIENCES BEYOND THE ROOM</h1>
    <p style="color: var(--text-light-secondary); max-width: 650px; margin: 0 auto;">
      From private twilight boat cruises with champagne to centuries-old Awadhi masterclasses and sunrise yoga pavilions.
    </p>
  </div>
</section>

<!-- Experiences Grid -->
<section class="section-spacing bg-ivory">
  <div class="container">
    
    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 2.5rem;">
      <?php foreach ($experiences as $exp): ?>
        <div class="hotel-card">
          <div class="hotel-card-media" style="height: 320px;">
            <img src="<?php echo htmlspecialchars($exp['image_url']); ?>" alt="<?php echo htmlspecialchars($exp['title']); ?>" loading="lazy">
            <div class="hotel-location-badge">
              <?php echo htmlspecialchars($exp['category']); ?>
            </div>
          </div>

          <div class="hotel-card-body">
            <div style="font-size: 0.8rem; color: var(--color-gold-dark); margin-bottom: 0.3rem;">
              <i class="fa-solid fa-hotel"></i> <?php echo htmlspecialchars($exp['hotel_name'] ? $exp['hotel_name'] . ' (' . $exp['city'] . ')' : 'Available Across All Palaces'); ?>
            </div>
            
            <h3><?php echo htmlspecialchars($exp['title']); ?></h3>
            <p style="font-size: 0.92rem; line-height: 1.6; margin-bottom: 1.5rem;"><?php echo htmlspecialchars($exp['full_desc']); ?></p>

            <div class="room-specs">
              <div class="room-spec-item"><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($exp['duration']); ?></div>
              <div class="room-spec-item"><i class="fa-solid fa-sun"></i> <?php echo htmlspecialchars($exp['timing']); ?></div>
            </div>

            <div class="hotel-card-footer">
              <div class="hotel-price-block">
                <span class="price-label">Price per person</span>
                <span class="price-value"><?php echo format_inr($exp['price_per_person']); ?></span>
              </div>
              <a href="booking.php" class="btn-primary" style="padding: 0.65rem 1.4rem; font-size: 0.78rem;">
                RESERVE WITH STAY
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
