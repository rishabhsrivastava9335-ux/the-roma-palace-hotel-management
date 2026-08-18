<?php
/**
 * THE ROMA PALACE — Hotel Properties Management (CRUD)
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/admin-header.php';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_hotel') {
        $name = trim($_POST['name']);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'])));
        $city = trim($_POST['city']);
        $state = trim($_POST['state']);
        $tagline = trim($_POST['tagline']);
        $address = trim($_POST['address']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $rating = (float)$_POST['star_rating'];
        $startingPrice = (float)$_POST['starting_price'];
        $totalRooms = (int)$_POST['total_rooms'];
        $imageUrl = trim($_POST['image_url']);
        $description = trim($_POST['description']);
        $highlights = trim($_POST['highlights']);

        try {
            db_execute("INSERT INTO hotels (name, slug, city, state, tagline, address, phone, email, star_rating, starting_price, total_rooms, image_url, description, highlights) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $name, $slug, $city, $state, $tagline, $address, $phone, $email, $rating, $startingPrice, $totalRooms, $imageUrl, $description, $highlights
            ]);
            $_SESSION['flash_success'] = "Palace property '{$name}' created successfully!";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Failed to add hotel: " . $e->getMessage();
        }
        header("Location: hotels.php");
        exit;
    }

    if ($action === 'update_hotel') {
        $hotelId = (int)$_POST['hotel_id'];
        $name = trim($_POST['name']);
        $city = trim($_POST['city']);
        $state = trim($_POST['state']);
        $tagline = trim($_POST['tagline']);
        $address = trim($_POST['address']);
        $phone = trim($_POST['phone']);
        $email = trim($_POST['email']);
        $rating = (float)$_POST['star_rating'];
        $startingPrice = (float)$_POST['starting_price'];
        $totalRooms = (int)$_POST['total_rooms'];
        $imageUrl = trim($_POST['image_url']);
        $description = trim($_POST['description']);
        $highlights = trim($_POST['highlights']);

        try {
            db_execute("UPDATE hotels SET name = ?, city = ?, state = ?, tagline = ?, address = ?, phone = ?, email = ?, star_rating = ?, starting_price = ?, total_rooms = ?, image_url = ?, description = ?, highlights = ? 
                        WHERE hotel_id = ?", [
                $name, $city, $state, $tagline, $address, $phone, $email, $rating, $startingPrice, $totalRooms, $imageUrl, $description, $highlights, $hotelId
            ]);
            $_SESSION['flash_success'] = "Palace property '{$name}' updated successfully!";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Failed to update hotel: " . $e->getMessage();
        }
        header("Location: hotels.php");
        exit;
    }

    if ($action === 'delete_hotel') {
        $hotelId = (int)$_POST['hotel_id'];
        try {
            db_execute("DELETE FROM hotels WHERE hotel_id = ?", [$hotelId]);
            $_SESSION['flash_success'] = "Hotel property deleted.";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Cannot delete hotel with existing inventory / bookings: " . $e->getMessage();
        }
        header("Location: hotels.php");
        exit;
    }
}

$hotels = db_fetch_all("SELECT h.*, (SELECT COUNT(*) FROM rooms r WHERE r.hotel_id = h.hotel_id) AS actual_rooms_count 
                        FROM hotels h ORDER BY h.hotel_id ASC");

$pageHeading = 'Palaces & Properties Management';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-hotel text-gold"></i> Registered Palace Properties</h3>
    <div class="admin-actions-bar">
      <button type="button" class="admin-btn-primary" onclick="openHotelModal()">
        <i class="fa-solid fa-plus"></i> Add New Palace
      </button>
    </div>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Palace Name</th>
          <th>Destination</th>
          <th>Contact Info</th>
          <th>Star Rating</th>
          <th>Starting Rate</th>
          <th>Rooms Count</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($hotels as $ht): ?>
          <tr>
            <td>
              <strong><?php echo htmlspecialchars($ht['name']); ?></strong><br>
              <small style="color: var(--color-gold-dark); font-style: italic;"><?php echo htmlspecialchars($ht['tagline']); ?></small>
            </td>
            <td><?php echo htmlspecialchars($ht['city'] . ', ' . $ht['state']); ?></td>
            <td>
              <?php echo htmlspecialchars($ht['phone']); ?><br>
              <small style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($ht['email']); ?></small>
            </td>
            <td><span class="badge badge-warning"><i class="fa-solid fa-star"></i> <?php echo number_format($ht['star_rating'], 1); ?></span></td>
            <td><strong><?php echo format_inr($ht['starting_price']); ?></strong></td>
            <td><?php echo $ht['actual_rooms_count']; ?> Registered Rooms</td>
            <td>
              <div class="action-btn-group">
                <button type="button" class="btn-action-icon" title="Edit Property" onclick='editHotelModal(<?php echo json_encode($ht); ?>)'>
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <form method="POST" action="hotels.php" onsubmit="return confirm('Delete hotel property?');" style="display: inline;">
                  <input type="hidden" name="action" value="delete_hotel">
                  <input type="hidden" name="hotel_id" value="<?php echo $ht['hotel_id']; ?>">
                  <button type="submit" class="btn-action-icon" style="color: #EF4444;" title="Delete Property">
                    <i class="fa-solid fa-trash"></i>
                  </button>
                </form>
              </div>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Hotel Form -->
<div id="hotelModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.65); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
  <div style="background: #FFFFFF; max-width: 650px; width: 90%; border-radius: 6px; padding: 2.5rem; border: 1px solid var(--admin-border); margin: 2rem auto; box-shadow: 0 20px 50px rgba(0,0,0,0.25); position: relative;">
    <button onclick="closeHotelModal()" style="position: absolute; top: 1.2rem; right: 1.2rem; background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
    
    <h3 style="font-size: 1.3rem; margin-bottom: 1.5rem;" id="modalHotelHeading">Add New Palace Property</h3>

    <form method="POST" action="hotels.php" id="hotelForm">
      <input type="hidden" name="action" id="hotelFormAction" value="create_hotel">
      <input type="hidden" name="hotel_id" id="modal_hotel_id" value="">

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group" style="grid-column: span 2;">
          <label>Palace Name</label>
          <input type="text" name="name" id="modal_ht_name" class="form-control" placeholder="e.g. The Roma Palace Varanasi" required>
        </div>

        <div class="form-group">
          <label>City</label>
          <input type="text" name="city" id="modal_ht_city" class="form-control" placeholder="Varanasi" required>
        </div>

        <div class="form-group">
          <label>State</label>
          <input type="text" name="state" id="modal_ht_state" class="form-control" placeholder="Uttar Pradesh" required>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Tagline</label>
          <input type="text" name="tagline" id="modal_ht_tagline" class="form-control" placeholder="Sacred Ghats & Timeless Spiritual Grandeur" required>
        </div>

        <div class="form-group">
          <label>Contact Phone</label>
          <input type="text" name="phone" id="modal_ht_phone" class="form-control" placeholder="+91 542 278 9000" required>
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" id="modal_ht_email" class="form-control" placeholder="varanasi@romapalace.com" required>
        </div>

        <div class="form-group">
          <label>Star Rating</label>
          <input type="number" step="0.1" min="1" max="5" name="star_rating" id="modal_ht_rating" value="5.0" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Starting Price (INR)</label>
          <input type="number" name="starting_price" id="modal_ht_price" value="16500" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Total Rooms Capacity</label>
          <input type="number" name="total_rooms" id="modal_ht_rooms" value="30" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Highlights (comma separated)</label>
          <input type="text" name="highlights" id="modal_ht_highlights" class="form-control" placeholder="Ghat View, Sunrise Aarti, Spa, Butler">
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Hero Image URL</label>
          <input type="url" name="image_url" id="modal_ht_image" class="form-control" value="https://images.unsplash.com/photo-1566073771259-6a8506099945?auto=format&fit=crop&w=1600&q=85" required>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Full Address</label>
          <textarea name="address" id="modal_ht_address" class="form-control" rows="2" placeholder="Ghat Road, Varanasi, UP 221001" required></textarea>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Editorial Description</label>
          <textarea name="description" id="modal_ht_desc" class="form-control" rows="3" placeholder="Description of the royal palace..." required></textarea>
        </div>
      </div>

      <div style="margin-top: 1.5rem; text-align: right;">
        <button type="button" class="btn-outline-dark" onclick="closeHotelModal()" style="padding: 0.6rem 1.2rem; margin-right: 0.5rem;">Cancel</button>
        <button type="submit" class="admin-btn-primary" style="padding: 0.6rem 1.5rem;">Save Property</button>
      </div>
    </form>
  </div>
</div>

<script>
function openHotelModal() {
  document.getElementById('modalHotelHeading').textContent = 'Add New Palace Property';
  document.getElementById('hotelFormAction').value = 'create_hotel';
  document.getElementById('modal_hotel_id').value = '';
  document.getElementById('modal_ht_name').value = '';
  document.getElementById('hotelModal').style.display = 'flex';
}
function editHotelModal(h) {
  document.getElementById('modalHotelHeading').textContent = 'Edit ' + h.name;
  document.getElementById('hotelFormAction').value = 'update_hotel';
  document.getElementById('modal_hotel_id').value = h.hotel_id;
  document.getElementById('modal_ht_name').value = h.name;
  document.getElementById('modal_ht_city').value = h.city;
  document.getElementById('modal_ht_state').value = h.state;
  document.getElementById('modal_ht_tagline').value = h.tagline;
  document.getElementById('modal_ht_phone').value = h.phone;
  document.getElementById('modal_ht_email').value = h.email;
  document.getElementById('modal_ht_rating').value = h.star_rating;
  document.getElementById('modal_ht_price').value = h.starting_price;
  document.getElementById('modal_ht_rooms').value = h.total_rooms;
  document.getElementById('modal_ht_highlights').value = h.highlights || '';
  document.getElementById('modal_ht_image').value = h.image_url;
  document.getElementById('modal_ht_address').value = h.address;
  document.getElementById('modal_ht_desc').value = h.description;
  document.getElementById('hotelModal').style.display = 'flex';
}
function closeHotelModal() {
  document.getElementById('hotelModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
