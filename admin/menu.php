<?php
/**
 * THE ROMA PALACE — Restaurant Menu Management (CRUD)
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/admin-header.php';

$restFilter = isset($_GET['rest_id']) ? (int)$_GET['rest_id'] : null;

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_item') {
        $restId = (int)$_POST['restaurant_id'];
        $name = trim($_POST['name']);
        $cat = $_POST['category'];
        $price = (float)$_POST['price'];
        $diet = $_POST['dietary_flag'];
        $desc = trim($_POST['description']);
        $isSpecial = isset($_POST['is_chef_special']) ? 1 : 0;

        db_execute("INSERT INTO menu_items (restaurant_id, name, category, price, dietary_flag, description, is_chef_special, is_available) VALUES (?, ?, ?, ?, ?, ?, ?, 1)", [
            $restId, $name, $cat, $price, $diet, $desc, $isSpecial
        ]);
        $_SESSION['flash_success'] = "Menu item '{$name}' added!";
        header("Location: menu.php" . ($restFilter ? "?rest_id={$restFilter}" : ""));
        exit;
    }

    if ($action === 'delete_item') {
        $itemId = (int)$_POST['item_id'];
        db_execute("DELETE FROM menu_items WHERE item_id = ?", [$itemId]);
        $_SESSION['flash_success'] = "Menu item deleted.";
        header("Location: menu.php" . ($restFilter ? "?rest_id={$restFilter}" : ""));
        exit;
    }
}

$sql = "SELECT m.*, r.name AS restaurant_name FROM menu_items m INNER JOIN restaurants r ON m.restaurant_id = r.restaurant_id WHERE 1=1";
$params = [];
if ($restFilter) {
    $sql .= " AND m.restaurant_id = ?";
    $params[] = $restFilter;
}
$sql .= " ORDER BY m.restaurant_id ASC, m.category ASC, m.price ASC";

$menuItems = db_fetch_all($sql, $params);
$restaurants = db_fetch_all("SELECT * FROM restaurants ORDER BY name ASC");

$pageHeading = 'Restaurant Menus & Culinary Catalog';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
      <h3 class="admin-card-title"><i class="fa-solid fa-book-open text-gold"></i> Menu Catalog (<?php echo count($menuItems); ?> Dishes)</h3>
      <form method="GET" action="menu.php" style="display: flex; gap: 0.5rem;">
        <select name="rest_id" onchange="this.form.submit()" style="padding: 0.45rem 0.8rem; border-radius: 4px; border: 1px solid var(--admin-border); font-size: 0.82rem;">
          <option value="">All Restaurants</option>
          <?php foreach ($restaurants as $r): ?>
            <option value="<?php echo $r['restaurant_id']; ?>" <?php echo ($restFilter == $r['restaurant_id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($r['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if ($restFilter): ?>
          <a href="menu.php" style="font-size: 0.78rem; color: var(--admin-gold-dark); text-decoration: underline; align-self: center;">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <div class="admin-actions-bar">
      <div class="search-input-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search dish, category..." data-table-search="menuTable">
      </div>
      <button type="button" class="admin-btn-primary" onclick="openMenuModal()">
        <i class="fa-solid fa-plus"></i> Add Menu Item
      </button>
    </div>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table" id="menuTable">
      <thead>
        <tr>
          <th>Dish Name</th>
          <th>Restaurant Outlet</th>
          <th>Category</th>
          <th>Price (INR)</th>
          <th>Dietary</th>
          <th>Chef Special</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($menuItems as $m): ?>
          <tr>
            <td>
              <strong><?php echo htmlspecialchars($m['name']); ?></strong><br>
              <small style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($m['description']); ?></small>
            </td>
            <td><?php echo htmlspecialchars($m['restaurant_name']); ?></td>
            <td><span class="badge badge-secondary"><?php echo htmlspecialchars($m['category']); ?></span></td>
            <td><strong><?php echo format_inr($m['price']); ?></strong></td>
            <td>
              <span class="diet-tag diet-<?php echo strtolower(str_replace(' ', '-', $m['dietary_flag'])); ?>">
                <?php echo htmlspecialchars($m['dietary_flag']); ?>
              </span>
            </td>
            <td>
              <?php if ($m['is_chef_special']): ?>
                <span class="badge badge-warning"><i class="fa-solid fa-crown"></i> Chef Special</span>
              <?php else: ?>
                <span style="color: var(--admin-text-muted); font-size: 0.8rem;">Standard</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="POST" action="menu.php" onsubmit="return confirm('Delete dish?');" style="display: inline;">
                <input type="hidden" name="action" value="delete_item">
                <input type="hidden" name="item_id" value="<?php echo $m['item_id']; ?>">
                <button type="submit" class="btn-action-icon" style="color: #EF4444;" title="Delete Item">
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

<!-- Modal: Menu Item Form -->
<div id="menuModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.65); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: #FFFFFF; max-width: 550px; width: 90%; border-radius: 6px; padding: 2.5rem; border: 1px solid var(--admin-border); margin: 2rem auto; box-shadow: 0 20px 50px rgba(0,0,0,0.25); position: relative;">
    <button onclick="closeMenuModal()" style="position: absolute; top: 1.2rem; right: 1.2rem; background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
    <h3 style="font-size: 1.3rem; margin-bottom: 1.5rem;">Add Dish to Menu</h3>

    <form method="POST" action="menu.php">
      <input type="hidden" name="action" value="create_item">

      <div class="form-group">
        <label>Dish / Beverage Name</label>
        <input type="text" name="name" class="form-control" placeholder="e.g. Royal Rajputana Laal Maas" required>
      </div>

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group">
          <label>Restaurant</label>
          <select name="restaurant_id" class="form-control" required>
            <?php foreach ($restaurants as $r): ?>
              <option value="<?php echo $r['restaurant_id']; ?>"><?php echo htmlspecialchars($r['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Category</label>
          <select name="category" class="form-control" required>
            <option value="Appetizers">Appetizers</option>
            <option value="Main Course">Main Course</option>
            <option value="Royal Thali">Royal Thali</option>
            <option value="Desserts">Desserts</option>
            <option value="Beverages & Wine">Beverages & Wine</option>
            <option value="Breakfast">Breakfast</option>
          </select>
        </div>

        <div class="form-group">
          <label>Price (INR)</label>
          <input type="number" step="10" name="price" class="form-control" placeholder="1450" required>
        </div>

        <div class="form-group">
          <label>Dietary Tag</label>
          <select name="dietary_flag" class="form-control">
            <option value="Veg">Veg</option>
            <option value="Non-Veg">Non-Veg</option>
            <option value="Vegan">Vegan</option>
            <option value="Jain Option">Jain Option</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer; margin-top: 0.5rem;">
          <input type="checkbox" name="is_chef_special" value="1" style="width: 16px; height: 16px; accent-color: var(--admin-gold-dark);">
          <span>Highlight as Chef's Signature Special</span>
        </label>
      </div>

      <div class="form-group">
        <label>Culinary Description</label>
        <textarea name="description" class="form-control" rows="2" placeholder="Ingredients, heritage spices, preparation method..." required></textarea>
      </div>

      <div style="text-align: right; margin-top: 1.5rem;">
        <button type="button" class="btn-outline-dark" onclick="closeMenuModal()" style="padding: 0.6rem 1.2rem; margin-right: 0.5rem;">Cancel</button>
        <button type="submit" class="admin-btn-primary" style="padding: 0.6rem 1.5rem;">Save Menu Item</button>
      </div>
    </form>
  </div>
</div>

<script>
function openMenuModal() { document.getElementById('menuModal').style.display = 'flex'; }
function closeMenuModal() { document.getElementById('menuModal').style.display = 'none'; }
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
