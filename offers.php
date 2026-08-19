<?php
/**
 * THE ROMA PALACE — Signature Offers & Packages
 * BTech CSE DBMS Mini Project &bull; Founder: Rishabh Srivastava
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Signature Offers & Curated Privileges';
$offers = db_fetch_all("SELECT o.*, h.name AS hotel_name, h.city FROM offers o LEFT JOIN hotels h ON o.hotel_id = h.hotel_id WHERE o.is_active = 1 ORDER BY o.offer_id ASC");

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Banner -->
<section style="background: linear-gradient(rgba(18,19,22,0.7), rgba(18,19,22,0.85)), url('https://images.unsplash.com/photo-1578683010236-d716f9a3f461?auto=format&fit=crop&w=1600&q=85') center/cover no-repeat; padding: 7rem 2rem 4rem 2rem; text-align: center; color: #FFFFFF;">
  <div class="container">
    <span class="section-tag" style="color: var(--color-gold-light);">EXCLUSIVE PRIVILEGES</span>
    <h1 style="color: #FFFFFF; font-size: clamp(2rem, 4vw, 3.2rem); margin-bottom: 0.8rem;">SIGNATURE OFFERS</h1>
    <p style="color: var(--text-light-secondary); max-width: 650px; margin: 0 auto;">
      Experience more of what you love with curated dining inclusions, bespoke wellness rituals, and extended stay privileges.
    </p>
  </div>
</section>

<!-- Offers Grid -->
<section class="section-spacing bg-ivory">
  <div class="container">
    
    <div class="offers-grid">
      <?php foreach ($offers as $offer): ?>
        <div class="offer-card">
          <div class="offer-card-media">
            <img src="<?php echo htmlspecialchars($offer['image_url']); ?>" alt="<?php echo htmlspecialchars($offer['title']); ?>" loading="lazy">
            <span class="offer-tag"><?php echo htmlspecialchars($offer['tag']); ?></span>
            <span class="offer-code-badge">PROMO CODE: <?php echo htmlspecialchars($offer['code']); ?></span>
          </div>

          <div class="offer-card-body">
            <div style="font-size: 0.8rem; color: var(--color-gold-dark); margin-bottom: 0.4rem;">
              <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($offer['hotel_name'] ? $offer['hotel_name'] . ' (' . $offer['city'] . ')' : 'Valid at All Roma Palaces'); ?>
            </div>

            <h3><?php echo htmlspecialchars($offer['title']); ?></h3>
            <p><?php echo htmlspecialchars($offer['description']); ?></p>

            <div style="margin: 1rem 0;">
              <strong style="font-family: var(--font-accent); font-size: 0.72rem; letter-spacing: 1px; text-transform: uppercase; color: var(--text-muted); display: block; margin-bottom: 0.5rem;">
                Curated Benefits Included
              </strong>
              <ul class="offer-benefits-list">
                <?php 
                  $bList = explode(',', $offer['benefits']);
                  foreach ($bList as $ben):
                ?>
                  <li><i class="fa-solid fa-crown"></i> <?php echo htmlspecialchars(trim($ben)); ?></li>
                <?php endforeach; ?>
              </ul>
            </div>

            <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; padding-top: 1.2rem; border-top: 1px solid var(--border-light);">
              <div>
                <span class="price-label">Validity Period</span>
                <strong style="font-size: 0.88rem; color: var(--color-charcoal); display: block;"><?php echo format_stay_date($offer['validity_date']); ?></strong>
                <small style="color: var(--color-gold-dark); font-weight: 600;"><?php echo htmlspecialchars($offer['price_note']); ?></small>
              </div>

              <a href="booking.php?promo=<?php echo urlencode($offer['code']); ?>" class="btn-primary">
                BOOK THIS OFFER
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
