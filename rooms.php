<?php
/**
 * THE ROMA PALACE — Rooms & Suites Catalog
 * BTech CSE DBMS Mini Project &bull; Founder: Rishabh Srivastava
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Luxury Rooms & Royal Suites';

// Filter parameters
$hotelId = isset($_GET['hotel_id']) && !empty($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : null;
$roomType = isset($_GET['type']) && !empty($_GET['type']) ? trim($_GET['type']) : null;
$guests = isset($_GET['guests']) && !empty($_GET['guests']) ? (int)$_GET['guests'] : null;
$checkIn = isset($_GET['check_in']) && !empty($_GET['check_in']) ? trim($_GET['check_in']) : null;
$checkOut = isset($_GET['check_out']) && !empty($_GET['check_out']) ? trim($_GET['check_out']) : null;

// Base query
$sql = "SELECT r.*, h.name AS hotel_name, h.city, h.state 
        FROM rooms r 
        INNER JOIN hotels h ON r.hotel_id = h.hotel_id 
        WHERE r.status != 'Maintenance'";

$params = [];

if ($hotelId) {
    $sql .= " AND r.hotel_id = ?";
    $params[] = $hotelId;
}

if ($roomType && $roomType !== 'all') {
    $sql .= " AND r.room_type = ?";
    $params[] = $roomType;
}

if ($guests) {
    $sql .= " AND r.capacity >= ?";
    $params[] = $guests;
}

// Check real-time date availability to prevent double-booking
if ($checkIn && $checkOut) {
    $sql .= " AND r.room_id NOT IN (
        SELECT b.room_id FROM bookings b 
        WHERE b.booking_status IN ('Confirmed', 'Checked-In') 
        AND NOT (b.check_out_date <= ? OR b.check_in_date >= ?)
    )";
    $params[] = $checkIn;
    $params[] = $checkOut;
}

$sql .= " ORDER BY r.price_per_night ASC";

$rooms = db_fetch_all($sql, $params);
$hotelsList = db_fetch_all("SELECT hotel_id, name, city FROM hotels ORDER BY name ASC");

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Banner -->
<section style="background: linear-gradient(rgba(18,19,22,0.7), rgba(18,19,22,0.85)), url('https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1600&q=85') center/cover no-repeat; padding: 7rem 2rem 4rem 2rem; text-align: center; color: #FFFFFF;">
  <div class="container">
    <span class="section-tag" style="color: var(--color-gold-light);">ROYAL LIVING</span>
    <h1 style="color: #FFFFFF; font-size: clamp(2rem, 4vw, 3.2rem); margin-bottom: 0.8rem;">ROOMS & SUITES</h1>
    <p style="color: var(--text-light-secondary); max-width: 650px; margin: 0 auto;">
      Immerse yourself in hand-crafted furniture, gold leaf accents, marble en-suites, and round-the-clock bespoke butler service.
    </p>
  </div>
</section>

<!-- Filter Section -->
<section class="bg-ivory" style="padding: 2.5rem 0 0 0;">
  <div class="container">
    
    <div class="filter-bar">
      <!-- Category Buttons -->
      <div class="filter-group">
        <a href="rooms.php" class="filter-btn <?php echo (!$roomType || $roomType === 'all') ? 'active' : ''; ?>">All Categories</a>
        <a href="rooms.php?type=Deluxe+Room" class="filter-btn <?php echo ($roomType === 'Deluxe Room') ? 'active' : ''; ?>">Deluxe Room</a>
        <a href="rooms.php?type=Premium+Room" class="filter-btn <?php echo ($roomType === 'Premium Room') ? 'active' : ''; ?>">Premium Room</a>
        <a href="rooms.php?type=Executive+Room" class="filter-btn <?php echo ($roomType === 'Executive Room') ? 'active' : ''; ?>">Executive Room</a>
        <a href="rooms.php?type=Luxury+Suite" class="filter-btn <?php echo ($roomType === 'Luxury Suite') ? 'active' : ''; ?>">Luxury Suite</a>
        <a href="rooms.php?type=Royal+Suite" class="filter-btn <?php echo ($roomType === 'Royal Suite') ? 'active' : ''; ?>">Royal Suite</a>
      </div>

      <!-- Quick Hotel Selector -->
      <form method="GET" action="rooms.php" style="display: flex; gap: 0.5rem; align-items: center;">
        <?php if ($roomType): ?>
          <input type="hidden" name="type" value="<?php echo htmlspecialchars($roomType); ?>">
        <?php endif; ?>
        <select name="hotel_id" onchange="this.form.submit()" style="padding: 0.55rem 0.8rem; border-radius: 2px; border: 1px solid var(--border-light); font-size: 0.82rem; background: var(--color-white);">
          <option value="">Filter by Hotel</option>
          <?php foreach ($hotelsList as $hl): ?>
            <option value="<?php echo $hl['hotel_id']; ?>" <?php echo ($hotelId == $hl['hotel_id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($hl['name'] . ' (' . $hl['city'] . ')'); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if ($hotelId || $roomType || $checkIn): ?>
          <a href="rooms.php" style="font-size: 0.75rem; color: var(--color-gold-dark); text-decoration: underline;">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <!-- Active Search Filter Badge -->
    <?php if ($checkIn && $checkOut): ?>
      <div style="background: var(--color-cream); border: 1px solid var(--color-gold); padding: 0.8rem 1.2rem; border-radius: 2px; margin-bottom: 2rem; font-size: 0.85rem; color: var(--color-charcoal); display: flex; align-items: center; justify-content: space-between;">
        <span><i class="fa-solid fa-calendar-check text-gold"></i> Showing available rooms from <strong><?php echo format_stay_date($checkIn); ?></strong> to <strong><?php echo format_stay_date($checkOut); ?></strong></span>
        <a href="rooms.php" style="color: var(--color-gold-dark); text-decoration: underline; font-weight: 600;">Clear Dates</a>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- Rooms Grid -->
<section class="section-spacing bg-ivory" style="padding-top: 1rem;">
  <div class="container">
    
    <?php if (empty($rooms)): ?>
      <div style="text-align: center; padding: 4rem 2rem; background: var(--color-white); border-radius: 4px; border: 1px solid var(--border-light);">
        <i class="fa-solid fa-hotel" style="font-size: 2.5rem; color: var(--color-gold-dark); margin-bottom: 1rem; display: block;"></i>
        <h3>No Rooms Match Your Selected Criteria</h3>
        <p style="margin: 0.5rem 0 1.5rem 0;">Please adjust your travel dates, guests count, or destination filter to view available inventory.</p>
        <a href="rooms.php" class="btn-primary">RESET ALL FILTERS</a>
      </div>
    <?php else: ?>
      
      <div class="rooms-grid">
        <?php foreach ($rooms as $room): ?>
          <div class="room-card">
            <div class="room-card-media">
              <img src="<?php echo htmlspecialchars($room['image_url']); ?>" alt="<?php echo htmlspecialchars($room['room_type']); ?>" loading="lazy">
              <span class="room-type-tag"><?php echo htmlspecialchars($room['room_type']); ?></span>
              <span class="room-status-badge status-<?php echo strtolower($room['status']); ?>">
                <?php echo htmlspecialchars($room['status']); ?>
              </span>
            </div>

            <div class="room-card-body">
              <h3><?php echo htmlspecialchars($room['room_type']); ?> (Room <?php echo htmlspecialchars($room['room_number']); ?>)</h3>
              <p style="font-size: 0.8rem; color: var(--color-gold-dark); margin-bottom: 0.5rem;">
                <i class="fa-solid fa-hotel"></i> <?php echo htmlspecialchars($room['hotel_name'] . ' (' . $room['city'] . ')'); ?>
              </p>

              <div class="room-specs">
                <div class="room-spec-item"><i class="fa-solid fa-vector-square"></i> <?php echo $room['size_sqft']; ?> sq.ft</div>
                <div class="room-spec-item"><i class="fa-solid fa-bed"></i> <?php echo htmlspecialchars($room['bed_type']); ?></div>
                <div class="room-spec-item"><i class="fa-solid fa-users"></i> Up to <?php echo $room['capacity']; ?></div>
              </div>

              <p class="room-desc"><?php echo htmlspecialchars($room['description']); ?></p>

              <div class="room-card-footer">
                <div class="hotel-price-block">
                  <span class="price-label">Per Night</span>
                  <span class="price-value"><?php echo format_inr($room['price_per_night']); ?></span>
                </div>
                <div style="display: flex; gap: 0.5rem;">
                  <a href="room-details.php?id=<?php echo $room['room_id']; ?>" class="btn-outline-dark" style="padding: 0.6rem 0.9rem; font-size: 0.75rem;">
                    DETAILS
                  </a>
                  <a href="booking.php?room_id=<?php echo $room['room_id']; ?>" class="btn-primary" style="padding: 0.6rem 1rem; font-size: 0.75rem;">
                    RESERVE
                  </a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

    <?php endif; ?>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
