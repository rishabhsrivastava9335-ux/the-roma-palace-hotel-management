<?php
/**
 * THE ROMA PALACE — Palaces & Destinations
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Iconic Palaces & Heritage Destinations';
$hotels = db_fetch_all("SELECT * FROM hotels ORDER BY hotel_id ASC");

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Banner -->
<section style="background: linear-gradient(rgba(18,19,22,0.7), rgba(18,19,22,0.85)), url('https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1600&q=85') center/cover no-repeat; padding: 7rem 2rem 4rem 2rem; text-align: center; color: #FFFFFF;">
  <div class="container">
    <span class="section-tag" style="color: var(--color-gold-light);">HERITAGE PROPERTIES</span>
    <h1 style="color: #FFFFFF; font-size: clamp(2rem, 4vw, 3.2rem); margin-bottom: 0.8rem;">OUR ICONIC PALACES & DESTINATIONS</h1>
    <p style="color: var(--text-light-secondary); max-width: 650px; margin: 0 auto;">
      Each Roma Palace is an architectural marvel steeped in royal history, offering unparalleled regal luxury and legendary Indian hospitality.
    </p>
  </div>
</section>

<!-- Palaces Directory -->
<section class="section-spacing bg-ivory">
  <div class="container">
    
    <div style="display: flex; flex-direction: column; gap: 4.5rem;">
      <?php foreach ($hotels as $index => $h): ?>
        <div class="dining-showcase" id="<?php echo strtolower($h['city']); ?>" style="<?php echo ($index % 2 === 1) ? 'direction: rtl;' : ''; ?>">
          
          <div class="dining-media">
            <img src="<?php echo htmlspecialchars($h['image_url']); ?>" alt="<?php echo htmlspecialchars($h['name']); ?>" loading="lazy">
            <div class="hotel-rating-badge" style="position: absolute; top: 1.5rem; right: 1.5rem; <?php echo ($index % 2 === 1) ? 'right: auto; left: 1.5rem;' : ''; ?>">
              <i class="fa-solid fa-star"></i> <?php echo number_format($h['star_rating'], 1); ?> Star Luxury
            </div>
          </div>

          <div class="dining-info" style="<?php echo ($index % 2 === 1) ? 'direction: ltr;' : ''; ?>">
            <span class="section-tag"><?php echo htmlspecialchars($h['city'] . ', ' . $h['state']); ?></span>
            <h2><?php echo htmlspecialchars($h['name']); ?></h2>
            <p class="hotel-tagline"><?php echo htmlspecialchars($h['tagline']); ?></p>
            
            <p><?php echo htmlspecialchars($h['description']); ?></p>

            <div class="dining-meta-grid">
              <div class="dining-meta-item">
                <strong>Address & Location</strong>
                <?php echo htmlspecialchars($h['address']); ?>
              </div>
              <div class="dining-meta-item">
                <strong>Contact Concierge</strong>
                <?php echo htmlspecialchars($h['phone']); ?><br><?php echo htmlspecialchars($h['email']); ?>
              </div>
            </div>

            <div style="margin-bottom: 1.8rem;">
              <strong style="font-family: var(--font-accent); font-size: 0.72rem; letter-spacing: 1px; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">
                Property Highlights & Amenities
              </strong>
              <div class="hotel-highlights-tags">
                <?php 
                  $hl = explode(',', $h['highlights'] ?? 'Heritage,Pool,Spa,Butler');
                  foreach ($hl as $item):
                ?>
                  <span class="highlight-tag"><i class="fa-solid fa-check text-gold"></i> <?php echo htmlspecialchars(trim($item)); ?></span>
                <?php endforeach; ?>
              </div>
            </div>

            <div style="display: flex; align-items: center; justify-content: space-between; padding-top: 1.2rem; border-top: 1px solid var(--border-light); flex-wrap: wrap; gap: 1rem;">
              <div>
                <span class="price-label">Starting From</span>
                <span class="price-value"><?php echo format_inr($h['starting_price']); ?> <small>/ night</small></span>
              </div>

              <div style="display: flex; gap: 0.8rem;">
                <a href="rooms.php?hotel_id=<?php echo $h['hotel_id']; ?>" class="btn-outline-dark">
                  VIEW AVAILABLE ROOMS
                </a>
                <a href="booking.php?hotel_id=<?php echo $h['hotel_id']; ?>" class="btn-primary">
                  BOOK THIS PALACE
                </a>
              </div>
            </div>

          </div>

        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
