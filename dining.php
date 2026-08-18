<?php
/**
 * THE ROMA PALACE — Fine Dining & Gastronomy
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Fine Dining & Royal Gastronomy';

$restaurants = db_fetch_all("SELECT r.*, h.name AS hotel_name, h.city FROM restaurants r INNER JOIN hotels h ON r.hotel_id = h.hotel_id WHERE r.is_active = 1");
$menuItems = db_fetch_all("SELECT m.*, r.name AS restaurant_name FROM menu_items m INNER JOIN restaurants r ON m.restaurant_id = r.restaurant_id WHERE m.is_available = 1 ORDER BY m.price ASC");

$reservationSuccess = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'reserve_table') {
    $reservationSuccess = true;
}

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Banner -->
<section style="background: linear-gradient(rgba(18,19,22,0.7), rgba(18,19,22,0.85)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1600&q=85') center/cover no-repeat; padding: 7rem 2rem 4rem 2rem; text-align: center; color: #FFFFFF;">
  <div class="container">
    <span class="section-tag" style="color: var(--color-gold-light);">ROYAL FEASTS</span>
    <h1 style="color: #FFFFFF; font-size: clamp(2rem, 4vw, 3.2rem); margin-bottom: 0.8rem;">CULINARY EXCELLENCE</h1>
    <p style="color: var(--text-light-secondary); max-width: 650px; margin: 0 auto;">
      A celebration of ancient court recipes, Awadhi slow-dum artistry, and coastal seafood delicacies served under grand chandeliers.
    </p>
  </div>
</section>

<!-- Restaurants Showcase -->
<section class="section-spacing bg-ivory">
  <div class="container">
    
    <div style="text-align: center; max-width: 700px; margin: 0 auto 3.5rem auto;">
      <span class="section-tag">DESTINATION DINING</span>
      <h2>OUR SIGNATURE RESTAURANTS</h2>
      <p>Explore distinct culinary destinations across our palaces in Jaipur, Goa, Udaipur, and Lucknow.</p>
    </div>

    <div style="display: flex; flex-direction: column; gap: 4.5rem;">
      <?php foreach ($restaurants as $idx => $rest): ?>
        <div class="dining-showcase" style="<?php echo ($idx % 2 === 1) ? 'direction: rtl;' : ''; ?>">
          
          <div class="dining-media">
            <img src="<?php echo htmlspecialchars($rest['image_url']); ?>" alt="<?php echo htmlspecialchars($rest['name']); ?>" loading="lazy">
          </div>

          <div class="dining-info" style="<?php echo ($idx % 2 === 1) ? 'direction: ltr;' : ''; ?>">
            <span class="dining-cuisine-tag"><?php echo htmlspecialchars($rest['cuisine']); ?></span>
            <h3><?php echo htmlspecialchars($rest['name']); ?></h3>
            <p style="font-size: 0.85rem; color: var(--color-gold-dark); margin-bottom: 0.8rem;">
              <i class="fa-solid fa-hotel"></i> <?php echo htmlspecialchars($rest['hotel_name'] . ' (' . $rest['city'] . ')'); ?>
            </p>
            
            <p><?php echo htmlspecialchars($rest['description']); ?></p>

            <div class="dining-meta-grid">
              <div class="dining-meta-item">
                <strong>Opening Hours</strong>
                <?php echo htmlspecialchars($rest['opening_hours']); ?>
              </div>
              <div class="dining-meta-item">
                <strong>Dress Code & Setting</strong>
                <?php echo htmlspecialchars($rest['dress_code']); ?>
              </div>
            </div>

            <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
              <a href="#menu" class="btn-outline-dark">VIEW MENU</a>
              <a href="#reserve" class="btn-primary" onclick="document.getElementById('rest_select').value='<?php echo $rest['restaurant_id']; ?>'">RESERVE TABLE</a>
            </div>

          </div>

        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- Database-Driven Interactive Menu -->
<section class="section-spacing bg-cream" id="menu">
  <div class="container">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 2rem auto;">
      <span class="section-tag">GASTRONOMIC SELECTION</span>
      <h2>DISCOVER OUR MENUS</h2>
      <p>Hand-crafted culinary creations prepared with heirloom spices, artisanal cheeses, and farm-to-table produce.</p>
    </div>

    <!-- Category Filters -->
    <div class="menu-category-nav">
      <button class="menu-tab-btn active" data-category="all">All Specialties</button>
      <button class="menu-tab-btn" data-category="Appetizers">Appetizers & Kebabs</button>
      <button class="menu-tab-btn" data-category="Main Course">Main Course</button>
      <button class="menu-tab-btn" data-category="Royal Thali">Royal Thalis</button>
      <button class="menu-tab-btn" data-category="Desserts">Royal Desserts</button>
      <button class="menu-tab-btn" data-category="Beverages & Wine">Signature Beverages</button>
    </div>

    <!-- Menu Items List -->
    <div class="menu-items-grid">
      <?php foreach ($menuItems as $item): ?>
        <div class="menu-item-row" data-category="<?php echo htmlspecialchars($item['category']); ?>">
          <div class="menu-item-info">
            <h4>
              <?php echo htmlspecialchars($item['name']); ?>
              <?php if ($item['is_chef_special']): ?>
                <span style="background: var(--color-gold); color: var(--color-charcoal); font-size: 0.65rem; padding: 0.15rem 0.45rem; border-radius: 2px; font-weight: 700;">CHEF'S SPECIAL</span>
              <?php endif; ?>
              <span class="diet-tag diet-<?php echo strtolower(str_replace(' ', '-', $item['dietary_flag'])); ?>">
                <?php echo htmlspecialchars($item['dietary_flag']); ?>
              </span>
            </h4>
            <p><?php echo htmlspecialchars($item['description']); ?></p>
            <small style="color: var(--text-muted); font-size: 0.75rem;"><i class="fa-solid fa-utensils"></i> Served at <?php echo htmlspecialchars($item['restaurant_name']); ?></small>
          </div>
          <div class="menu-item-price">
            <?php echo format_inr($item['price']); ?>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

  </div>
</section>

<!-- Table Reservation Modal / Form -->
<section class="section-spacing bg-ivory" id="reserve">
  <div class="container">
    <div style="max-width: 800px; margin: 0 auto; background: var(--color-white); padding: 3.5rem; border-radius: 4px; border: 1px solid var(--border-gold); box-shadow: var(--shadow-luxury);">
      
      <div style="text-align: center; margin-bottom: 2.5rem;">
        <span class="section-tag">TABLE RESERVATION</span>
        <h2>EXPERIENCE REGAL DINING</h2>
        <p>Book your table in advance for an exquisite fine dining experience.</p>
      </div>

      <?php if ($reservationSuccess): ?>
        <div style="background: #DEF7EC; color: #03543F; padding: 1.5rem; border-radius: 3px; text-align: center; margin-bottom: 2rem; border: 1px solid #BCF0DA;">
          <i class="fa-solid fa-circle-check" style="font-size: 1.8rem; margin-bottom: 0.5rem; display: block;"></i>
          <strong>Table Reservation Confirmed!</strong>
          <p style="font-size: 0.88rem; margin-top: 0.4rem;">Our Maitre D’ will welcome you with cold floral towels and your preferred table setting.</p>
        </div>
      <?php endif; ?>

      <form method="POST" action="dining.php#reserve" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem;">
        <input type="hidden" name="action" value="reserve_table">

        <div class="form-group">
          <label for="rest_select">Select Restaurant</label>
          <select name="restaurant_id" id="rest_select" class="form-control" required>
            <?php foreach ($restaurants as $r): ?>
              <option value="<?php echo $r['restaurant_id']; ?>">
                <?php echo htmlspecialchars($r['name'] . ' (' . $r['city'] . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label for="res_date">Date of Reservation</label>
          <input type="date" name="reservation_date" id="res_date" class="form-control" required>
        </div>

        <div class="form-group">
          <label for="res_time">Seating Time</label>
          <select name="reservation_time" id="res_time" class="form-control" required>
            <option value="12:30 PM">12:30 PM (Lunch)</option>
            <option value="01:30 PM">01:30 PM (Lunch)</option>
            <option value="07:30 PM" selected>07:30 PM (Dinner)</option>
            <option value="08:30 PM">08:30 PM (Dinner)</option>
            <option value="09:30 PM">09:30 PM (Late Dinner)</option>
          </select>
        </div>

        <div class="form-group">
          <label for="res_guests">Number of Guests</label>
          <select name="guests_count" id="res_guests" class="form-control" required>
            <option value="2">2 Guests (Couple)</option>
            <option value="4" selected>4 Guests</option>
            <option value="6">6 Guests</option>
            <option value="8">8+ Guests (Private Dining)</option>
          </select>
        </div>

        <div class="form-group">
          <label for="guest_name">Full Name</label>
          <input type="text" name="guest_name" id="guest_name" class="form-control" placeholder="Your name" required>
        </div>

        <div class="form-group">
          <label for="guest_phone">Phone Number</label>
          <input type="tel" name="guest_phone" id="guest_phone" class="form-control" placeholder="+91 98765 43210" required>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label for="special_pref">Dietary Preferences / Special Celebration</label>
          <textarea name="preferences" id="special_pref" class="form-control" rows="3" placeholder="Anniversary table setting, Jain food request, allergies..."></textarea>
        </div>

        <div style="grid-column: span 2; text-align: center; margin-top: 1rem;">
          <button type="submit" class="btn-primary" style="padding: 1rem 3rem;">
            <i class="fa-solid fa-champagne-glasses"></i>
            <span>CONFIRM TABLE RESERVATION</span>
          </button>
        </div>

      </form>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
