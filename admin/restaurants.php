<?php
/**
 * THE ROMA PALACE — Restaurant Management
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/admin-header.php';

// Handle Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_restaurant') {
        $hotelId = (int)$_POST['hotel_id'];
        $name = trim($_POST['name']);
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $_POST['name'])));
        $cuisine = trim($_POST['cuisine']);
        $hours = trim($_POST['opening_hours']);
        $loc = trim($_POST['location_desc']);
        $dress = trim($_POST['dress_code']);
        $img = trim($_POST['image_url']);
        $desc = trim($_POST['description']);

        db_execute("INSERT INTO restaurants (hotel_id, name, slug, cuisine, opening_hours, location_desc, dress_code, image_url, description, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)", [
            $hotelId, $name, $slug, $cuisine, $hours, $loc, $dress, $img, $desc
        ]);
        $_SESSION['flash_success'] = "Restaurant '{$name}' created!";
        header("Location: restaurants.php");
        exit;
    }

    if ($action === 'delete_restaurant') {
        $rId = (int)$_POST['restaurant_id'];
        db_execute("DELETE FROM restaurants WHERE restaurant_id = ?", [$rId]);
        $_SESSION['flash_success'] = "Restaurant deleted.";
        header("Location: restaurants.php");
        exit;
    }
}

$restaurants = db_fetch_all("SELECT r.*, h.name AS hotel_name, (SELECT COUNT(*) FROM menu_items m WHERE m.restaurant_id = r.restaurant_id) AS menu_items_count 
                             FROM restaurants r 
                             INNER JOIN hotels h ON r.hotel_id = h.hotel_id 
                             ORDER BY r.restaurant_id ASC");
$hotels = db_fetch_all("SELECT * FROM hotels ORDER BY name ASC");

$pageHeading = 'Restaurants & Culinary Venues Management';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-utensils text-gold"></i> Registered Fine Dining Outlets</h3>
    <button type="button" class="admin-btn-primary" onclick="openRestModal()">
      <i class="fa-solid fa-plus"></i> Add New Restaurant
    </button>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table">
      <thead>
        <tr>
          <th>Restaurant Name</th>
          <th>Palace Location</th>
          <th>Cuisine Type</th>
          <th>Opening Hours</th>
          <th>Dress Code</th>
          <th>Menu Items</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($restaurants as $r): ?>
          <tr>
            <td><strong><?php echo htmlspecialchars($r['name']); ?></strong></td>
            <td><?php echo htmlspecialchars($r['hotel_name']); ?></td>
            <td><span class="badge badge-info"><?php echo htmlspecialchars($r['cuisine']); ?></span></td>
            <td><small><?php echo htmlspecialchars($r['opening_hours']); ?></small></td>
            <td><small><?php echo htmlspecialchars($r['dress_code']); ?></small></td>
            <td>
              <a href="menu.php?rest_id=<?php echo $r['restaurant_id']; ?>" style="color: var(--admin-gold-dark); font-weight: 700; text-decoration: underline;">
                <?php echo $r['menu_items_count']; ?> Dishes (Manage)
              </a>
            </td>
            <td>
              <form method="POST" action="restaurants.php" onsubmit="return confirm('Delete restaurant?');" style="display: inline;">
                <input type="hidden" name="action" value="delete_restaurant">
                <input type="hidden" name="restaurant_id" value="<?php echo $r['restaurant_id']; ?>">
                <button type="submit" class="btn-action-icon" style="color: #EF4444;" title="Delete Restaurant">
                  <i class="fa-solid fa-trash"></i>
                </button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal: Restaurant Form -->
<div id="restModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.65); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: #FFFFFF; max-width: 600px; width: 90%; border-radius: 6px; padding: 2.5rem; border: 1px solid var(--admin-border); margin: 2rem auto; box-shadow: 0 20px 50px rgba(0,0,0,0.25); position: relative;">
    <button onclick="closeRestModal()" style="position: absolute; top: 1.2rem; right: 1.2rem; background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
    <h3 style="font-size: 1.3rem; margin-bottom: 1.5rem;">Add New Restaurant</h3>

    <form method="POST" action="restaurants.php">
      <input type="hidden" name="action" value="create_restaurant">

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group" style="grid-column: span 2;">
          <label>Restaurant Name</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. Royal Rajputana Dastarkhwan" required>
        </div>

        <div class="form-group">
          <label>Palace Property</label>
          <select name="hotel_id" class="form-control" required>
            <?php foreach ($hotels as $ht): ?>
              <option value="<?php echo $ht['hotel_id']; ?>"><?php echo htmlspecialchars($ht['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Cuisine</label>
          <input type="text" name="cuisine" class="form-control" placeholder="Royal Awadhi & Mughlai" required>
        </div>

        <div class="form-group">
          <label>Opening Hours</label>
          <input type="text" name="opening_hours" class="form-control" value="07:00 AM – 11:30 PM" required>
        </div>

        <div class="form-group">
          <label>Dress Code</label>
          <input type="text" name="dress_code" class="form-control" value="Smart Casual / Formal" required>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Location on Property</label>
          <input type="text" name="location_desc" class="form-control" placeholder="Grand Central Courtyard" required>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Image URL</label>
          <input type="url" name="image_url" class="form-control" value="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?auto=format&fit=crop&w=1200&q=85" required>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Description</label>
          <textarea name="description" class="form-control" rows="2" placeholder="Description..." required></textarea>
        </div>
      </div>

      <div style="text-align: right; margin-top: 1.5rem;">
        <button type="button" class="btn-outline-dark" onclick="closeRestModal()" style="padding: 0.6rem 1.2rem; margin-right: 0.5rem;">Cancel</button>
        <button type="submit" class="admin-btn-primary" style="padding: 0.6rem 1.5rem;">Save Restaurant</button>
      </div>
    </form>
  </div>
</div>

<script>
function openRestModal() { document.getElementById('restModal').style.display = 'flex'; }
function closeRestModal() { document.getElementById('restModal').style.display = 'none'; }
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
