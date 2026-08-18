<?php
/**
 * THE ROMA PALACE — Room Details Deep-Dive
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$roomId = isset($_GET['id']) ? (int)$_GET['id'] : 1;

$room = db_fetch_one("SELECT r.*, h.name AS hotel_name, h.city, h.state, h.address, h.phone, h.email 
                      FROM rooms r 
                      INNER JOIN hotels h ON r.hotel_id = h.hotel_id 
                      WHERE r.room_id = ?", [$roomId]);

if (!$room) {
    header("Location: rooms.php");
    exit;
}

$amenities = db_fetch_all("SELECT * FROM room_amenities WHERE room_id = ?", [$roomId]);
if (empty($amenities)) {
    // Default amenities fallback
    $amenities = [
        ['amenity_name' => 'High-Speed Wi-Fi 6', 'icon_class' => 'fa-solid fa-wifi'],
        ['amenity_name' => 'Italian Marble Bath & Soaking Tub', 'icon_class' => 'fa-solid fa-bath'],
        ['amenity_name' => '24-Hour Butler & In-Room Dining', 'icon_class' => 'fa-solid fa-bell-concierge'],
        ['amenity_name' => '55" Ultra HD Smart TV & Streaming', 'icon_class' => 'fa-solid fa-tv'],
        ['amenity_name' => 'Nespresso Gourmet Coffee Bar', 'icon_class' => 'fa-solid fa-mug-hot'],
        ['amenity_name' => 'Digital Electronic Safe', 'icon_class' => 'fa-solid fa-shield-halved'],
        ['amenity_name' => 'Luxury Cotton Linens & Robes', 'icon_class' => 'fa-solid fa-shirt'],
        ['amenity_name' => 'Climate Control & Mood Lighting', 'icon_class' => 'fa-solid fa-sliders']
    ];
}

$services = db_fetch_all("SELECT * FROM services WHERE status = 'Available' LIMIT 4");
$pageTitle = $room['room_type'] . ' (' . $room['room_number'] . ') — ' . $room['hotel_name'];

require_once __DIR__ . '/includes/header.php';
?>

<!-- Room Hero Banner -->
<section style="background: linear-gradient(rgba(18,19,22,0.6), rgba(18,19,22,0.85)), url('<?php echo htmlspecialchars($room['image_url']); ?>') center/cover no-repeat; padding: 8rem 2rem 5rem 2rem; color: #FFFFFF;">
  <div class="container">
    <div style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 0.8rem;">
      <span class="room-type-tag" style="position: static;"><?php echo htmlspecialchars($room['room_type']); ?></span>
      <span class="room-status-badge status-<?php echo strtolower($room['status']); ?>" style="position: static;">
        <?php echo htmlspecialchars($room['status']); ?>
      </span>
    </div>
    
    <h1 style="color: #FFFFFF; font-size: clamp(2.2rem, 4.5vw, 3.6rem); margin-bottom: 0.5rem;">
      <?php echo htmlspecialchars($room['room_type']); ?> (Room <?php echo htmlspecialchars($room['room_number']); ?>)
    </h1>
    <p style="color: var(--color-gold-light); font-size: 1.1rem; font-family: var(--font-serif-title); font-style: italic;">
      <?php echo htmlspecialchars($room['hotel_name'] . ', ' . $room['city']); ?>
    </p>
  </div>
</section>

<!-- Detail Content Grid -->
<section class="section-spacing bg-ivory">
  <div class="container">
    <div style="display: grid; grid-template-columns: 2fr 1.1fr; gap: 3.5rem;">
      
      <!-- Left Editorial & Specifications -->
      <div>
        
        <!-- Large Room Photography -->
        <div style="margin-bottom: 2.5rem; border-radius: 4px; overflow: hidden; box-shadow: var(--shadow-elevated);">
          <img src="<?php echo htmlspecialchars($room['image_url']); ?>" alt="<?php echo htmlspecialchars($room['room_type']); ?>" style="width: 100%; height: 460px; object-fit: cover;">
        </div>

        <!-- Room Specs Quick Bar -->
        <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 1rem; background: var(--color-white); padding: 1.5rem; border-radius: 3px; border: 1px solid var(--border-light); margin-bottom: 2.5rem; box-shadow: var(--shadow-soft);">
          <div style="text-align: center; border-right: 1px solid var(--border-light);">
            <span class="price-label">Room Area</span>
            <strong style="font-size: 1.1rem; color: var(--color-charcoal); display: block; margin-top: 4px;"><?php echo $room['size_sqft']; ?> sq.ft</strong>
          </div>
          <div style="text-align: center; border-right: 1px solid var(--border-light);">
            <span class="price-label">Bed Type</span>
            <strong style="font-size: 1.1rem; color: var(--color-charcoal); display: block; margin-top: 4px;"><?php echo htmlspecialchars($room['bed_type']); ?></strong>
          </div>
          <div style="text-align: center; border-right: 1px solid var(--border-light);">
            <span class="price-label">Capacity</span>
            <strong style="font-size: 1.1rem; color: var(--color-charcoal); display: block; margin-top: 4px;"><?php echo $room['capacity']; ?> Guests</strong>
          </div>
          <div style="text-align: center;">
            <span class="price-label">Floor & View</span>
            <strong style="font-size: 1.1rem; color: var(--color-charcoal); display: block; margin-top: 4px;">Floor <?php echo $room['floor']; ?></strong>
          </div>
        </div>

        <!-- Description -->
        <div style="margin-bottom: 3rem;">
          <span class="section-tag">SANCTUARY OVERVIEW</span>
          <h2 style="font-size: 1.8rem; margin-bottom: 1rem;">Experience Unrivalled Refinement</h2>
          <p style="font-size: 1rem; line-height: 1.8; color: var(--text-dark-secondary); margin-bottom: 1.2rem;">
            <?php echo htmlspecialchars($room['description']); ?>
          </p>
          <p style="font-size: 1rem; line-height: 1.8; color: var(--text-dark-secondary);">
            Every corner is appointed with authentic hand-woven tapestries, Italian marble baths with rain-head showers, customized climate systems, and sweeping panoramic views of the <?php echo htmlspecialchars($room['view_type']); ?>.
          </p>
        </div>

        <!-- Luxury Amenities Grid -->
        <div style="margin-bottom: 3rem;">
          <span class="section-tag">INDULGENCES</span>
          <h2 style="font-size: 1.8rem; margin-bottom: 1.5rem;">Signature Room Amenities</h2>
          
          <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.2rem;">
            <?php foreach ($amenities as $am): ?>
              <div style="display: flex; align-items: center; gap: 0.9rem; padding: 1rem 1.2rem; background: var(--color-white); border-radius: 2px; border: 1px solid var(--border-light);">
                <i class="<?php echo htmlspecialchars($am['icon_class']); ?>" style="color: var(--color-gold-dark); font-size: 1.1rem;"></i>
                <span style="font-size: 0.9rem; font-weight: 500; color: var(--text-dark);"><?php echo htmlspecialchars($am['amenity_name']); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <!-- Enhance Stay Options -->
        <div>
          <span class="section-tag">CURATED ADD-ONS</span>
          <h2 style="font-size: 1.8rem; margin-bottom: 1.2rem;">Enhance Your Stay</h2>
          <div class="addons-grid">
            <?php foreach ($services as $srv): ?>
              <div class="addon-card">
                <div class="addon-info">
                  <h4><i class="<?php echo htmlspecialchars($srv['icon_class']); ?> text-gold" style="margin-right: 6px;"></i> <?php echo htmlspecialchars($srv['name']); ?></h4>
                  <p><?php echo htmlspecialchars($srv['description']); ?></p>
                  <span class="addon-price"><?php echo format_inr($srv['price']); ?> <small style="font-size: 0.75rem; color: var(--text-muted); font-weight: 400;">/ <?php echo htmlspecialchars($srv['unit']); ?></small></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

      </div>

      <!-- Right Floating Reservation Box -->
      <div>
        <div style="background: var(--color-white); border: 2px solid var(--color-gold); border-radius: 4px; padding: 2.5rem; box-shadow: var(--shadow-luxury); position: sticky; top: 100px;">
          
          <div style="display: flex; justify-content: space-between; align-items: flex-end; padding-bottom: 1.5rem; border-bottom: 1px solid var(--border-light); margin-bottom: 1.8rem;">
            <div>
              <span class="price-label">Price Per Night</span>
              <div style="font-family: var(--font-serif-brand); font-size: 2rem; font-weight: 700; color: var(--color-charcoal);">
                <?php echo format_inr($room['price_per_night']); ?>
              </div>
            </div>
            <small style="color: var(--text-muted); font-size: 0.78rem;">Excluding 18% GST</small>
          </div>

          <form action="booking.php" method="GET">
            <input type="hidden" name="room_id" value="<?php echo $room['room_id']; ?>">
            <input type="hidden" name="hotel_id" value="<?php echo $room['hotel_id']; ?>">

            <div class="form-group">
              <label for="checkin_date">Check-in Date</label>
              <input type="date" name="check_in" id="checkin_date" class="form-control" required>
            </div>

            <div class="form-group">
              <label for="checkout_date">Check-out Date</label>
              <input type="date" name="check_out" id="checkout_date" class="form-control" required>
            </div>

            <div class="form-group">
              <label for="num_guests">Guests Count</label>
              <select name="guests" id="num_guests" class="form-control">
                <?php for ($g = 1; $g <= $room['capacity']; $g++): ?>
                  <option value="<?php echo $g; ?>" <?php echo ($g == 2) ? 'selected' : ''; ?>>
                    <?php echo $g; ?> <?php echo ($g == 1) ? 'Guest' : 'Guests'; ?>
                  </option>
                <?php endfor; ?>
              </select>
            </div>

            <div style="background: var(--color-ivory); border-radius: 2px; padding: 1rem; margin: 1.5rem 0; font-size: 0.82rem; color: var(--text-dark-secondary);">
              <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.4rem;">
                <i class="fa-solid fa-shield-check text-gold"></i>
                <strong>Guaranteed Best Available Rate</strong>
              </div>
              <div>Free cancellation up to 48 hours prior to check-in.</div>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 1rem; font-size: 0.85rem;">
              <i class="fa-solid fa-calendar-check"></i>
              <span>BOOK THIS ROOM</span>
            </button>
          </form>

          <div style="margin-top: 1.5rem; text-align: center;">
            <a href="rooms.php" style="font-size: 0.8rem; color: var(--text-muted); text-decoration: underline;">
              &larr; Return to all rooms
            </a>
          </div>

        </div>
      </div>

    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
