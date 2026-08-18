<?php
/**
 * THE ROMA PALACE — Homepage & Main Luxury Gateway
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'A Legacy of Luxury, A Stay to Remember';

// Fetch database records for homepage
$hotels = db_fetch_all("SELECT * FROM hotels WHERE status = 'active' ORDER BY star_rating DESC LIMIT 4");
$featuredRooms = db_fetch_all("SELECT r.*, h.name AS hotel_name, h.city FROM rooms r INNER JOIN hotels h ON r.hotel_id = h.hotel_id WHERE r.status != 'Maintenance' ORDER BY r.price_per_night ASC LIMIT 6");
$offers = db_fetch_all("SELECT * FROM offers WHERE is_active = 1 LIMIT 4");
$experiences = db_fetch_all("SELECT * FROM experiences WHERE is_active = 1 LIMIT 4");
$reviews = db_fetch_all("SELECT rv.*, c.full_name, c.city, h.name AS hotel_name FROM reviews rv INNER JOIN customers c ON rv.customer_id = c.customer_id INNER JOIN hotels h ON rv.hotel_id = h.hotel_id WHERE rv.is_approved = 1 ORDER BY rv.rating DESC LIMIT 3");

require_once __DIR__ . '/includes/header.php';
?>

<!-- 1. CINEMATIC HERO SECTION -->
<section class="hero-section" style="background-image: url('https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=2000&q=90');">
  <div class="hero-overlay"></div>
  
  <div class="hero-content">
    <div class="hero-brand-emblem">
      <div class="rp-monogram">
        <span>RP</span>
      </div>
    </div>
    
    <h1 class="hero-title">THE ROMA PALACE</h1>
    <p class="hero-tagline">“A Legacy of Luxury, A Stay to Remember.”</p>
    <p class="hero-description">
      Discover timeless hospitality, refined comfort and unforgettable experiences across India’s most iconic palatial sanctuaries.
    </p>

    <div class="hero-buttons">
      <a href="#palaces" class="btn-outline-light">
        <i class="fa-solid fa-compass"></i>
        <span>EXPLORE THE PALACE</span>
      </a>
      <a href="booking.php" class="btn-primary">
        <i class="fa-solid fa-calendar-check"></i>
        <span>BOOK YOUR STAY</span>
      </a>
    </div>
  </div>

  <div class="scroll-indicator">
    <span>Scroll to Discover</span>
    <i class="fa-solid fa-chevron-down"></i>
  </div>
</section>

<!-- 2. OVERLAPPING PREMIUM BOOKING ENGINE -->
<section class="booking-engine-wrapper">
  <div class="container">
    <div class="booking-panel">
      <div class="booking-panel-title">
        <i class="fa-solid fa-magnifying-glass-location"></i>
        <span>FIND YOUR PERFECT STAY</span>
      </div>

      <form action="rooms.php" method="GET" class="booking-form-grid">
        
        <!-- Destination / Hotel -->
        <div class="booking-field">
          <label for="search_hotel">Destination / Hotel</label>
          <div class="booking-input-wrapper">
            <i class="fa-solid fa-hotel"></i>
            <select name="hotel_id" id="search_hotel">
              <option value="">All Roma Palaces & Destinations</option>
              <?php foreach ($hotels as $h): ?>
                <option value="<?php echo $h['hotel_id']; ?>">
                  <?php echo htmlspecialchars($h['name'] . ' (' . $h['city'] . ')'); ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <!-- Check-in -->
        <div class="booking-field">
          <label for="checkin_date">Check-in</label>
          <div class="booking-input-wrapper">
            <i class="fa-solid fa-calendar-days"></i>
            <input type="date" name="check_in" id="checkin_date" required>
          </div>
        </div>

        <!-- Check-out -->
        <div class="booking-field">
          <label for="checkout_date">Check-out</label>
          <div class="booking-input-wrapper">
            <i class="fa-solid fa-calendar-check"></i>
            <input type="date" name="check_out" id="checkout_date" required>
          </div>
        </div>

        <!-- Guests -->
        <div class="booking-field">
          <label for="search_guests">Guests</label>
          <div class="booking-input-wrapper">
            <i class="fa-solid fa-user-group"></i>
            <select name="guests" id="search_guests">
              <option value="1">1 Guest</option>
              <option value="2" selected>2 Guests</option>
              <option value="3">3 Guests</option>
              <option value="4">4+ Guests</option>
            </select>
          </div>
        </div>

        <!-- Rooms -->
        <div class="booking-field">
          <label for="search_rooms">Rooms</label>
          <div class="booking-input-wrapper">
            <i class="fa-solid fa-door-open"></i>
            <select name="rooms_count" id="search_rooms">
              <option value="1" selected>1 Room</option>
              <option value="2">2 Rooms</option>
              <option value="3">3 Rooms</option>
            </select>
          </div>
        </div>

        <!-- Promo Code -->
        <div class="booking-field">
          <label for="search_promo">Promo Code</label>
          <div class="booking-input-wrapper">
            <i class="fa-solid fa-tag"></i>
            <input type="text" name="promo" id="search_promo" placeholder="e.g. WELCOME10">
          </div>
        </div>

        <!-- Submit Button -->
        <div class="booking-field">
          <button type="submit" class="booking-submit-btn">
            <i class="fa-solid fa-magnifying-glass"></i>
            <span>SEARCH</span>
          </button>
        </div>

      </form>
    </div>
  </div>
</section>

<!-- 3. EDITORIAL WELCOME SECTION -->
<section class="section-spacing bg-ivory" id="welcome">
  <div class="container">
    <div class="welcome-grid">
      
      <!-- Left Image Column -->
      <div class="welcome-image-wrapper">
        <img src="https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?auto=format&fit=crop&w=1200&q=85" alt="The Roma Palace Architecture" loading="lazy">
        <div class="welcome-image-badge">
          <span>SINCE 2001</span>
          <small>Uncompromising Excellence</small>
        </div>
      </div>

      <!-- Right Editorial Story -->
      <div class="welcome-text">
        <span class="section-tag">HERITAGE & DISTINCTION</span>
        <h2>WELCOME TO THE ROMA PALACE</h2>
        
        <p class="lead-quote">
          “Where timeless architecture meets contemporary comfort, The Roma Palace creates experiences designed to be remembered.”
        </p>

        <p>
          Born from a deep reverence for India’s grand royal estates, The Roma Palace offers an intimate glimpse into aristocratic leisure. Each sanctuary is crafted with meticulous attention to heritage masonry, tranquil courtyards, hand-carved jharokhas, and genuine personalized butler service.
        </p>

        <!-- Live Statistics Counter -->
        <div class="stats-grid">
          <div class="stat-item">
            <div class="stat-number" data-target="25" data-suffix="+">0</div>
            <div class="stat-label">Years of Hospitality</div>
          </div>
          <div class="stat-item">
            <div class="stat-number" data-target="120" data-suffix="+">0</div>
            <div class="stat-label">Luxury Rooms</div>
          </div>
          <div class="stat-item">
            <div class="stat-number" data-target="8" data-suffix="">0</div>
            <div class="stat-label">Signature Experiences</div>
          </div>
          <div class="stat-item">
            <div class="stat-number" data-target="4" data-suffix="">0</div>
            <div class="stat-label">Palace Destinations</div>
          </div>
        </div>

        <a href="hotels.php" class="btn-outline-dark">
          <span>DISCOVER OUR STORY</span>
          <i class="fa-solid fa-arrow-right"></i>
        </a>
      </div>

    </div>
  </div>
</section>

<!-- 4. DESTINATIONS SECTION -->
<section class="section-spacing bg-cream">
  <div class="container">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 3rem auto;">
      <span class="section-tag">EXPERIENCE INDIA</span>
      <h2>DISCOVER INDIA, THE ROMA WAY</h2>
      <p>From the arid dunes and palaces of Rajputana to serene Goan shorelines and Awadhi cultural capitals.</p>
    </div>

    <div class="destinations-grid">
      
      <!-- Jaipur -->
      <div class="destination-card" onclick="window.location.href='hotels.php#jaipur'">
        <img src="https://images.unsplash.com/photo-1599661046289-e31897846e41?auto=format&fit=crop&w=800&q=80" alt="Jaipur">
        <div class="destination-overlay">
          <span class="destination-tag">RAJASTHAN</span>
          <h3 class="destination-name">Jaipur</h3>
          <p class="destination-desc">Royal heritage retreats amidst manicured Mughal courtyards and amber ramparts.</p>
          <span class="destination-link">EXPLORE <i class="fa-solid fa-arrow-right"></i></span>
        </div>
      </div>

      <!-- Goa -->
      <div class="destination-card" onclick="window.location.href='hotels.php#goa'">
        <img src="https://images.unsplash.com/photo-1512343879784-a960bf40e7f2?auto=format&fit=crop&w=800&q=80" alt="Goa">
        <div class="destination-overlay">
          <span class="destination-tag">COASTAL RETREAT</span>
          <h3 class="destination-name">Goa</h3>
          <p class="destination-desc">Contemporary oceanfront bliss with private sands and Portuguese colonial charm.</p>
          <span class="destination-link">EXPLORE <i class="fa-solid fa-arrow-right"></i></span>
        </div>
      </div>

      <!-- Udaipur -->
      <div class="destination-card" onclick="window.location.href='hotels.php#udaipur'">
        <img src="https://images.unsplash.com/photo-1542314831-068cd1dbfeeb?auto=format&fit=crop&w=800&q=80" alt="Udaipur">
        <div class="destination-overlay">
          <span class="destination-tag">CITY OF LAKES</span>
          <h3 class="destination-name">Udaipur</h3>
          <p class="destination-desc">Fairy-tale palace living rising majestically over tranquil Lake Pichola.</p>
          <span class="destination-link">EXPLORE <i class="fa-solid fa-arrow-right"></i></span>
        </div>
      </div>

      <!-- Kerala -->
      <div class="destination-card" onclick="window.location.href='hotels.php'">
        <img src="https://images.unsplash.com/photo-1602216056096-3b40cc0c9944?auto=format&fit=crop&w=800&q=80" alt="Kerala">
        <div class="destination-overlay">
          <span class="destination-tag">GOD'S OWN COUNTRY</span>
          <h3 class="destination-name">Kerala</h3>
          <p class="destination-desc">Lush emerald backwaters, spice plantations, and Ayurvedic rejuvenation.</p>
          <span class="destination-link">EXPLORE <i class="fa-solid fa-arrow-right"></i></span>
        </div>
      </div>

      <!-- Lucknow -->
      <div class="destination-card" onclick="window.location.href='hotels.php#lucknow'">
        <img src="https://images.unsplash.com/photo-1571896349842-33c89424de2d?auto=format&fit=crop&w=800&q=80" alt="Lucknow">
        <div class="destination-overlay">
          <span class="destination-tag">AWADHI MAJESTY</span>
          <h3 class="destination-name">Lucknow</h3>
          <p class="destination-desc">Nawabi courtesies, legendary dum-pukht cuisine, and grand riverfront estates.</p>
          <span class="destination-link">EXPLORE <i class="fa-solid fa-arrow-right"></i></span>
        </div>
      </div>

      <!-- Varanasi -->
      <div class="destination-card" onclick="window.location.href='hotels.php'">
        <img src="https://images.unsplash.com/photo-1561361513-2d000a50f0dc?auto=format&fit=crop&w=800&q=80" alt="Varanasi">
        <div class="destination-overlay">
          <span class="destination-tag">SPIRITUAL CAPITAL</span>
          <h3 class="destination-name">Varanasi</h3>
          <p class="destination-desc">Ancient spiritual riverbanks, sacred evening aartis, and timeless silk heritage.</p>
          <span class="destination-link">EXPLORE <i class="fa-solid fa-arrow-right"></i></span>
        </div>
      </div>

    </div>
  </div>
</section>

<!-- 5. OUR PALACES & HOTELS -->
<section class="section-spacing bg-ivory" id="palaces">
  <div class="container">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem; flex-wrap: wrap; gap: 1rem;">
      <div>
        <span class="section-tag">ICONIC PROPERTIES</span>
        <h2>ICONIC STAYS. TIMELESS EXPERIENCES.</h2>
      </div>
      <a href="hotels.php" class="btn-outline-dark">
        <span>VIEW ALL PROPERTIES</span>
        <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

    <div class="palaces-grid">
      <?php foreach ($hotels as $hotel): ?>
        <div class="hotel-card" id="<?php echo strtolower($hotel['city']); ?>">
          <div class="hotel-card-media">
            <img src="<?php echo htmlspecialchars($hotel['image_url']); ?>" alt="<?php echo htmlspecialchars($hotel['name']); ?>" loading="lazy">
            <div class="hotel-location-badge">
              <i class="fa-solid fa-location-dot"></i> <?php echo htmlspecialchars($hotel['city'] . ', ' . $hotel['state']); ?>
            </div>
            <div class="hotel-rating-badge">
              <i class="fa-solid fa-star"></i> <?php echo number_format($hotel['star_rating'], 1); ?>
            </div>
          </div>

          <div class="hotel-card-body">
            <h3><?php echo htmlspecialchars($hotel['name']); ?></h3>
            <p class="hotel-tagline"><?php echo htmlspecialchars($hotel['tagline']); ?></p>
            <p class="hotel-desc"><?php echo htmlspecialchars($hotel['description']); ?></p>

            <div class="hotel-highlights-tags">
              <?php 
                $highlights = explode(',', $hotel['highlights'] ?? 'Heritage,Pool,Dining,Spa');
                foreach (array_slice($highlights, 0, 4) as $hItem): 
              ?>
                <span class="highlight-tag"><?php echo htmlspecialchars(trim($hItem)); ?></span>
              <?php endforeach; ?>
            </div>

            <div class="hotel-card-footer">
              <div class="hotel-price-block">
                <span class="price-label">Starting from</span>
                <span class="price-value"><?php echo format_inr($hotel['starting_price']); ?> <small>/ night</small></span>
              </div>
              
              <div class="hotel-card-actions">
                <a href="rooms.php?hotel_id=<?php echo $hotel['hotel_id']; ?>" class="btn-outline-dark" style="padding: 0.65rem 1.2rem; font-size: 0.75rem;">
                  VIEW ROOMS
                </a>
                <a href="booking.php?hotel_id=<?php echo $hotel['hotel_id']; ?>" class="btn-primary" style="padding: 0.65rem 1.2rem; font-size: 0.75rem;">
                  BOOK NOW
                </a>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 6. ROOMS & SUITES PREVIEW -->
<section class="section-spacing bg-cream">
  <div class="container">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 3rem auto;">
      <span class="section-tag">ACCOMMODATION</span>
      <h2>STAY IN YOUR OWN WORLD</h2>
      <p>Bespoke sanctuaries designed with majestic craftsmanship, Italian marble baths, and panoramic courtyard views.</p>
    </div>

    <div class="rooms-grid">
      <?php foreach ($featuredRooms as $room): ?>
        <div class="room-card" data-type="<?php echo htmlspecialchars($room['room_type']); ?>">
          <div class="room-card-media">
            <img src="<?php echo htmlspecialchars($room['image_url']); ?>" alt="<?php echo htmlspecialchars($room['room_type']); ?>" loading="lazy">
            <span class="room-type-tag"><?php echo htmlspecialchars($room['room_type']); ?></span>
            <span class="room-status-badge status-<?php echo strtolower($room['status']); ?>">
              <?php echo htmlspecialchars($room['status']); ?>
            </span>
          </div>

          <div class="room-card-body">
            <h3><?php echo htmlspecialchars($room['room_type']); ?> (<?php echo htmlspecialchars($room['room_number']); ?>)</h3>
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
              <a href="room-details.php?id=<?php echo $room['room_id']; ?>" class="btn-primary" style="padding: 0.6rem 1.1rem; font-size: 0.75rem;">
                DETAILS & BOOK
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div style="text-align: center; margin-top: 3rem;">
      <a href="rooms.php" class="btn-outline-dark">
        <span>VIEW ALL 25+ ROOMS & SUITES</span>
        <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
  </div>
</section>

<!-- 7. SIGNATURE OFFERS -->
<section class="section-spacing bg-ivory">
  <div class="container">
    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 3rem; flex-wrap: wrap; gap: 1rem;">
      <div>
        <span class="section-tag">CURATED PRIVILEGES</span>
        <h2>SIGNATURE OFFERS</h2>
      </div>
      <a href="offers.php" class="btn-outline-dark">
        <span>VIEW ALL OFFERS</span>
        <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>

    <div class="offers-grid">
      <?php foreach ($offers as $offer): ?>
        <div class="offer-card">
          <div class="offer-card-media">
            <img src="<?php echo htmlspecialchars($offer['image_url']); ?>" alt="<?php echo htmlspecialchars($offer['title']); ?>" loading="lazy">
            <span class="offer-tag"><?php echo htmlspecialchars($offer['tag']); ?></span>
            <span class="offer-code-badge">USE CODE: <?php echo htmlspecialchars($offer['code']); ?></span>
          </div>

          <div class="offer-card-body">
            <h3><?php echo htmlspecialchars($offer['title']); ?></h3>
            <p><?php echo htmlspecialchars($offer['description']); ?></p>

            <ul class="offer-benefits-list">
              <?php 
                $bList = explode(',', $offer['benefits']);
                foreach ($bList as $ben):
              ?>
                <li><i class="fa-solid fa-crown"></i> <?php echo htmlspecialchars(trim($ben)); ?></li>
              <?php endforeach; ?>
            </ul>

            <div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; padding-top: 1.2rem; border-top: 1px solid var(--border-light);">
              <div>
                <span class="price-label">Validity</span>
                <strong style="font-size: 0.85rem; color: var(--color-charcoal);"><?php echo format_stay_date($offer['validity_date']); ?></strong>
              </div>
              <a href="booking.php?promo=<?php echo urlencode($offer['code']); ?>" class="btn-primary" style="padding: 0.6rem 1.2rem; font-size: 0.75rem;">
                BOOK OFFER
              </a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- 8. EXPERIENCES BEYOND THE ROOM -->
<section class="section-spacing bg-dark">
  <div class="container">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 3rem auto;">
      <span class="section-tag" style="color: var(--color-gold-light);">REGAL INDULGENCE</span>
      <h2 style="color: var(--text-light);">EXPERIENCES BEYOND THE ROOM</h2>
      <p style="color: var(--text-light-secondary);">Immerse yourself in centuries of living culture, twilight boat serenades, and culinary artistry.</p>
    </div>

    <div class="experiences-grid">
      <?php foreach ($experiences as $exp): ?>
        <div class="experience-card">
          <div class="experience-media">
            <img src="<?php echo htmlspecialchars($exp['image_url']); ?>" alt="<?php echo htmlspecialchars($exp['title']); ?>" loading="lazy">
          </div>
          <div class="experience-body">
            <div class="experience-category"><?php echo htmlspecialchars($exp['category']); ?></div>
            <h4><?php echo htmlspecialchars($exp['title']); ?></h4>
            <p style="font-size: 0.84rem; color: var(--text-dark-secondary);"><?php echo htmlspecialchars($exp['short_desc']); ?></p>

            <div class="experience-meta">
              <span><i class="fa-regular fa-clock"></i> <?php echo htmlspecialchars($exp['duration']); ?></span>
              <strong style="color: var(--color-gold-dark); font-family: var(--font-serif-brand);"><?php echo format_inr($exp['price_per_person']); ?></strong>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>

    <div style="text-align: center; margin-top: 3rem;">
      <a href="experiences.php" class="btn-outline-light">
        <span>EXPLORE ALL 10 EXPERIENCES</span>
        <i class="fa-solid fa-arrow-right"></i>
      </a>
    </div>
  </div>
</section>

<!-- 9. CULINARY EXCELLENCE & DINING PREVIEW -->
<section class="section-spacing bg-ivory">
  <div class="container">
    <div class="welcome-grid">
      
      <div class="welcome-text">
        <span class="section-tag">GASTRONOMY</span>
        <h2>CULINARY EXCELLENCE AT THE ROMA PALACE</h2>
        <p class="lead-quote">
          “An exquisite exploration of royal Indian royal court recipes, authentic Awadhi dum pukht, and world-class oceanfront dining.”
        </p>
        <p>
          From the legendary charcoal-fired Laal Maas at <em>The Roma Table</em> to sunset cocktails over Lake Pichola at <em>Azure Lounge</em>, our master chefs curate unforgettable gastronomic memories.
        </p>
        
        <div style="margin: 2rem 0; display: flex; gap: 1rem; flex-wrap: wrap;">
          <a href="dining.php" class="btn-primary">
            <i class="fa-solid fa-utensils"></i>
            <span>EXPLORE RESTAURANTS & MENU</span>
          </a>
          <a href="dining.php#reserve" class="btn-outline-dark">
            <span>RESERVE A TABLE</span>
          </a>
        </div>
      </div>

      <div class="welcome-image-wrapper">
        <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=85" alt="Fine Dining at The Roma Palace" loading="lazy">
      </div>

    </div>
  </div>
</section>

<!-- 10. REVIEWS & TESTIMONIALS -->
<section class="section-spacing bg-cream">
  <div class="container">
    <div style="text-align: center; max-width: 700px; margin: 0 auto 3rem auto;">
      <span class="section-tag">TESTIMONIALS</span>
      <h2>VOICES OF OUR ESTEEMED GUESTS</h2>
      <p>Memories cherished by patrons who have experienced the timeless magic of The Roma Palace.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem;">
      <?php foreach ($reviews as $rev): ?>
        <div style="background: var(--color-white); border-radius: 4px; padding: 2.2rem; border: 1px solid var(--border-light); box-shadow: var(--shadow-soft); display: flex; flex-direction: column;">
          <div style="color: var(--color-gold); font-size: 0.95rem; margin-bottom: 1rem;">
            <?php for ($i = 0; $i < $rev['rating']; $i++): ?>
              <i class="fa-solid fa-star"></i>
            <?php endfor; ?>
          </div>
          <h4 style="font-size: 1.15rem; margin-bottom: 0.8rem; color: var(--color-charcoal);">“<?php echo htmlspecialchars($rev['review_title']); ?>”</h4>
          <p style="font-size: 0.88rem; color: var(--text-dark-secondary); line-height: 1.6; margin-bottom: 1.5rem; flex-grow: 1;">
            <?php echo htmlspecialchars($rev['comments']); ?>
          </p>
          <div style="border-top: 1px solid var(--border-light); padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
            <div>
              <strong style="font-size: 0.9rem; color: var(--color-charcoal); display: block;"><?php echo htmlspecialchars($rev['full_name']); ?></strong>
              <small style="color: var(--text-muted); font-size: 0.75rem;"><?php echo htmlspecialchars($rev['city']); ?></small>
            </div>
            <span style="font-size: 0.75rem; color: var(--color-gold-dark); font-weight: 600;"><?php echo htmlspecialchars($rev['stay_date'] ?? 'Recent Stay'); ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
