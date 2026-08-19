<?php
/**
 * THE ROMA PALACE — Room Inventory Management (CRUD)
 * BTech CSE DBMS Mini Project &bull; Founder: Rishabh Srivastava
 */
require_once __DIR__ . '/includes/admin-header.php';

$hotelFilter = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : null;
$statusFilter = isset($_GET['status']) ? trim($_GET['status']) : null;

// Handle CRUD Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // CREATE ROOM
    if ($action === 'create_room') {
        $hotelId = (int)$_POST['hotel_id'];
        $roomNumber = trim($_POST['room_number']);
        $roomType = $_POST['room_type'];
        $floor = (int)$_POST['floor'];
        $capacity = (int)$_POST['capacity'];
        $bedType = trim($_POST['bed_type']);
        $sizeSqft = (int)$_POST['size_sqft'];
        $price = (float)$_POST['price_per_night'];
        $status = $_POST['status'] ?? 'Available';
        $imageUrl = trim($_POST['image_url']);
        $description = trim($_POST['description']);
        $viewType = trim($_POST['view_type']);

        try {
            db_execute("INSERT INTO rooms (hotel_id, room_number, room_type, floor, capacity, bed_type, size_sqft, price_per_night, status, image_url, description, view_type) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $hotelId, $roomNumber, $roomType, $floor, $capacity, $bedType, $sizeSqft, $price, $status, $imageUrl, $description, $viewType
            ]);
            $_SESSION['flash_success'] = "Room {$roomNumber} created successfully!";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Failed to create room: " . $e->getMessage();
        }
        header("Location: rooms.php");
        exit;
    }

    // UPDATE ROOM
    if ($action === 'update_room') {
        $roomId = (int)$_POST['room_id'];
        $hotelId = (int)$_POST['hotel_id'];
        $roomNumber = trim($_POST['room_number']);
        $roomType = $_POST['room_type'];
        $floor = (int)$_POST['floor'];
        $capacity = (int)$_POST['capacity'];
        $bedType = trim($_POST['bed_type']);
        $sizeSqft = (int)$_POST['size_sqft'];
        $price = (float)$_POST['price_per_night'];
        $status = $_POST['status'];
        $imageUrl = trim($_POST['image_url']);
        $description = trim($_POST['description']);
        $viewType = trim($_POST['view_type']);

        try {
            db_execute("UPDATE rooms SET hotel_id = ?, room_number = ?, room_type = ?, floor = ?, capacity = ?, bed_type = ?, size_sqft = ?, price_per_night = ?, status = ?, image_url = ?, description = ?, view_type = ? 
                        WHERE room_id = ?", [
                $hotelId, $roomNumber, $roomType, $floor, $capacity, $bedType, $sizeSqft, $price, $status, $imageUrl, $description, $viewType, $roomId
            ]);
            $_SESSION['flash_success'] = "Room {$roomNumber} updated successfully!";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Failed to update room: " . $e->getMessage();
        }
        header("Location: rooms.php");
        exit;
    }

    // DELETE ROOM
    if ($action === 'delete_room') {
        $roomId = (int)$_POST['room_id'];
        try {
            db_execute("DELETE FROM rooms WHERE room_id = ?", [$roomId]);
            $_SESSION['flash_success'] = "Room deleted successfully!";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Cannot delete room with active bookings: " . $e->getMessage();
        }
        header("Location: rooms.php");
        exit;
    }

    // QUICK STATUS TOGGLE
    if ($action === 'toggle_status') {
        $roomId = (int)$_POST['room_id'];
        $newStatus = $_POST['new_status'];
        db_execute("UPDATE rooms SET status = ? WHERE room_id = ?", [$newStatus, $roomId]);
        $_SESSION['flash_success'] = "Room status updated to {$newStatus}.";
        header("Location: rooms.php");
        exit;
    }
}

// Fetch Rooms List
$sql = "SELECT r.*, h.name AS hotel_name, h.city 
        FROM rooms r 
        INNER JOIN hotels h ON r.hotel_id = h.hotel_id 
        WHERE 1=1";
$params = [];

if ($hotelFilter) {
    $sql .= " AND r.hotel_id = ?";
    $params[] = $hotelFilter;
}
if ($statusFilter) {
    $sql .= " AND r.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY r.hotel_id ASC, r.room_number ASC";
$rooms = db_fetch_all($sql, $params);
$hotels = db_fetch_all("SELECT * FROM hotels ORDER BY name ASC");

$pageHeading = 'Room Inventory & Categories Management';
?>

<!-- Action Bar & Filters -->
<div class="admin-card">
  <div class="admin-card-header">
    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
      <h3 class="admin-card-title"><i class="fa-solid fa-door-open text-gold"></i> Rooms Inventory (<?php echo count($rooms); ?> Rooms)</h3>
      
      <!-- Filters -->
      <form method="GET" action="rooms.php" style="display: flex; gap: 0.6rem; align-items: center;">
        <select name="hotel_id" onchange="this.form.submit()" style="padding: 0.45rem 0.8rem; border-radius: 4px; border: 1px solid var(--admin-border); font-size: 0.82rem;">
          <option value="">All Palaces</option>
          <?php foreach ($hotels as $ht): ?>
            <option value="<?php echo $ht['hotel_id']; ?>" <?php echo ($hotelFilter == $ht['hotel_id']) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($ht['name']); ?>
            </option>
          <?php endforeach; ?>
        </select>

        <select name="status" onchange="this.form.submit()" style="padding: 0.45rem 0.8rem; border-radius: 4px; border: 1px solid var(--admin-border); font-size: 0.82rem;">
          <option value="">All Statuses</option>
          <option value="Available" <?php echo ($statusFilter === 'Available') ? 'selected' : ''; ?>>Available</option>
          <option value="Occupied" <?php echo ($statusFilter === 'Occupied') ? 'selected' : ''; ?>>Occupied</option>
          <option value="Reserved" <?php echo ($statusFilter === 'Reserved') ? 'selected' : ''; ?>>Reserved</option>
          <option value="Maintenance" <?php echo ($statusFilter === 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
        </select>
        <?php if ($hotelFilter || $statusFilter): ?>
          <a href="rooms.php" style="font-size: 0.78rem; color: var(--admin-gold-dark); text-decoration: underline;">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <div class="admin-actions-bar">
      <div class="search-input-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search room no, type..." data-table-search="roomsMasterTable">
      </div>
      <button type="button" class="admin-btn-primary" onclick="openRoomModal()">
        <i class="fa-solid fa-plus"></i> Add New Room
      </button>
    </div>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table" id="roomsMasterTable">
      <thead>
        <tr>
          <th>Room No</th>
          <th>Palace Location</th>
          <th>Category</th>
          <th>Floor / Area</th>
          <th>Capacity & Bed</th>
          <th>Rate / Night</th>
          <th>Live Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rooms as $rm): ?>
          <tr>
            <td><strong style="font-size: 1rem;"><?php echo htmlspecialchars($rm['room_number']); ?></strong></td>
            <td><?php echo htmlspecialchars($rm['hotel_name']); ?></td>
            <td><strong style="color: var(--admin-primary);"><?php echo htmlspecialchars($rm['room_type']); ?></strong></td>
            <td>Floor <?php echo $rm['floor']; ?> &bull; <?php echo $rm['size_sqft']; ?> sq.ft</td>
            <td><?php echo $rm['capacity']; ?> Guests (<?php echo htmlspecialchars($rm['bed_type']); ?>)</td>
            <td><strong><?php echo format_inr($rm['price_per_night']); ?></strong></td>
            <td>
              <!-- Quick Status Switcher Form -->
              <form method="POST" action="rooms.php" style="display: inline;">
                <input type="hidden" name="action" value="toggle_status">
                <input type="hidden" name="room_id" value="<?php echo $rm['room_id']; ?>">
                <select name="new_status" onchange="this.form.submit()" class="badge badge-<?php 
                  if ($rm['status'] === 'Available') echo 'success';
                  elseif ($rm['status'] === 'Occupied') echo 'danger';
                  elseif ($rm['status'] === 'Reserved') echo 'warning';
                  else echo 'secondary';
                ?>" style="border: none; cursor: pointer; font-weight: 700;">
                  <option value="Available" <?php echo ($rm['status'] === 'Available') ? 'selected' : ''; ?>>Available</option>
                  <option value="Occupied" <?php echo ($rm['status'] === 'Occupied') ? 'selected' : ''; ?>>Occupied</option>
                  <option value="Reserved" <?php echo ($rm['status'] === 'Reserved') ? 'selected' : ''; ?>>Reserved</option>
                  <option value="Maintenance" <?php echo ($rm['status'] === 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
                </select>
              </form>
            </td>
            <td>
              <div class="action-btn-group">
                <button type="button" class="btn-action-icon" title="Edit Room" onclick='editRoomModal(<?php echo json_encode($rm); ?>)'>
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <form method="POST" action="rooms.php" onsubmit="return confirm('Delete room <?php echo $rm['room_number']; ?>?');" style="display: inline;">
                  <input type="hidden" name="action" value="delete_room">
                  <input type="hidden" name="room_id" value="<?php echo $rm['room_id']; ?>">
                  <button type="submit" class="btn-action-icon" title="Delete Room" style="color: #EF4444;">
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

<!-- Modal: Add / Edit Room -->
<div id="roomModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.65); z-index: 1000; align-items: center; justify-content: center; overflow-y: auto;">
  <div style="background: #FFFFFF; max-width: 650px; width: 90%; border-radius: 6px; padding: 2.5rem; border: 1px solid var(--admin-border); margin: 2rem auto; box-shadow: 0 20px 50px rgba(0,0,0,0.25); position: relative;">
    <button onclick="closeRoomModal()" style="position: absolute; top: 1.2rem; right: 1.2rem; background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
    
    <h3 style="font-size: 1.3rem; margin-bottom: 1.5rem;" id="modalRoomHeading">Add New Luxury Room</h3>

    <form method="POST" action="rooms.php" id="roomForm">
      <input type="hidden" name="action" id="roomFormAction" value="create_room">
      <input type="hidden" name="room_id" id="modal_room_id" value="">

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        
        <div class="form-group">
          <label>Palace Property</label>
          <select name="hotel_id" id="modal_hotel_id" class="form-control" required>
            <?php foreach ($hotels as $ht): ?>
              <option value="<?php echo $ht['hotel_id']; ?>"><?php echo htmlspecialchars($ht['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Room Number</label>
          <input type="text" name="room_number" id="modal_room_number" class="form-control" placeholder="e.g. JP-401" required>
        </div>

        <div class="form-group">
          <label>Category</label>
          <select name="room_type" id="modal_room_type" class="form-control" required>
            <option value="Deluxe Room">Deluxe Room</option>
            <option value="Premium Room">Premium Room</option>
            <option value="Executive Room">Executive Room</option>
            <option value="Luxury Suite">Luxury Suite</option>
            <option value="Royal Suite">Royal Suite</option>
          </select>
        </div>

        <div class="form-group">
          <label>Price Per Night (INR)</label>
          <input type="number" step="100" name="price_per_night" id="modal_price" class="form-control" placeholder="24000" required>
        </div>

        <div class="form-group">
          <label>Floor</label>
          <input type="number" name="floor" id="modal_floor" value="1" min="1" max="10" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Guest Capacity</label>
          <input type="number" name="capacity" id="modal_capacity" value="2" min="1" max="8" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Bed Configuration</label>
          <input type="text" name="bed_type" id="modal_bed_type" class="form-control" value="King Bed" required>
        </div>

        <div class="form-group">
          <label>Area (Sq. Ft)</label>
          <input type="number" name="size_sqft" id="modal_size_sqft" value="500" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Initial Status</label>
          <select name="status" id="modal_status" class="form-control">
            <option value="Available">Available</option>
            <option value="Reserved">Reserved</option>
            <option value="Occupied">Occupied</option>
            <option value="Maintenance">Maintenance</option>
          </select>
        </div>

        <div class="form-group">
          <label>View Perspective</label>
          <input type="text" name="view_type" id="modal_view_type" class="form-control" placeholder="Mughal Courtyard / Lake View">
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Image URL</label>
          <input type="url" name="image_url" id="modal_image_url" class="form-control" value="https://images.unsplash.com/photo-1618773928121-c32242e63f39?auto=format&fit=crop&w=1200&q=85" required>
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Editorial Description</label>
          <textarea name="description" id="modal_description" class="form-control" rows="3" placeholder="Handcrafted furnishings, marble bathtub..."></textarea>
        </div>

      </div>

      <div style="margin-top: 1.5rem; text-align: right;">
        <button type="button" class="btn-outline-dark" onclick="closeRoomModal()" style="padding: 0.6rem 1.2rem; margin-right: 0.5rem;">Cancel</button>
        <button type="submit" class="admin-btn-primary" style="padding: 0.6rem 1.5rem;">Save Room Details</button>
      </div>

    </form>

  </div>
</div>

<script>
function openRoomModal() {
  document.getElementById('modalRoomHeading').textContent = 'Add New Luxury Room';
  document.getElementById('roomFormAction').value = 'create_room';
  document.getElementById('modal_room_id').value = '';
  document.getElementById('modal_room_number').value = '';
  document.getElementById('modal_price').value = '24000';
  document.getElementById('modal_description').value = 'Elegantly appointed royal suite with handcrafted furnishings and palace courtyard perspectives.';
  document.getElementById('roomModal').style.display = 'flex';
}

function editRoomModal(room) {
  document.getElementById('modalRoomHeading').textContent = 'Edit Room ' + room.room_number;
  document.getElementById('roomFormAction').value = 'update_room';
  document.getElementById('modal_room_id').value = room.room_id;
  document.getElementById('modal_hotel_id').value = room.hotel_id;
  document.getElementById('modal_room_number').value = room.room_number;
  document.getElementById('modal_room_type').value = room.room_type;
  document.getElementById('modal_price').value = room.price_per_night;
  document.getElementById('modal_floor').value = room.floor;
  document.getElementById('modal_capacity').value = room.capacity;
  document.getElementById('modal_bed_type').value = room.bed_type;
  document.getElementById('modal_size_sqft').value = room.size_sqft;
  document.getElementById('modal_status').value = room.status;
  document.getElementById('modal_view_type').value = room.view_type || '';
  document.getElementById('modal_image_url').value = room.image_url;
  document.getElementById('modal_description').value = room.description;
  document.getElementById('roomModal').style.display = 'flex';
}

function closeRoomModal() {
  document.getElementById('roomModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
