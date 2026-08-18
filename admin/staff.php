<?php
/**
 * THE ROMA PALACE — Staff & HR Management (CRUD)
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/admin-header.php';

// Handle Staff CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_staff') {
        $hotelId = (int)$_POST['hotel_id'];
        $name = trim($_POST['name']);
        $dept = $_POST['department'];
        $pos = trim($_POST['position']);
        $phone = trim($_POST['phone']);
        $email = trim(strtolower($_POST['email']));
        $joining = $_POST['joining_date'];
        $salary = (float)$_POST['salary'];
        $status = $_POST['status'] ?? 'Active';

        try {
            db_execute("INSERT INTO staff (hotel_id, name, department, position, phone, email, joining_date, salary, status) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)", [
                $hotelId, $name, $dept, $pos, $phone, $email, $joining, $salary, $status
            ]);
            $_SESSION['flash_success'] = "Staff member '{$name}' registered successfully!";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Failed to add staff: " . $e->getMessage();
        }
        header("Location: staff.php");
        exit;
    }

    if ($action === 'update_staff') {
        $staffId = (int)$_POST['staff_id'];
        $hotelId = (int)$_POST['hotel_id'];
        $name = trim($_POST['name']);
        $dept = $_POST['department'];
        $pos = trim($_POST['position']);
        $phone = trim($_POST['phone']);
        $email = trim(strtolower($_POST['email']));
        $joining = $_POST['joining_date'];
        $salary = (float)$_POST['salary'];
        $status = $_POST['status'];

        try {
            db_execute("UPDATE staff SET hotel_id = ?, name = ?, department = ?, position = ?, phone = ?, email = ?, joining_date = ?, salary = ?, status = ? WHERE staff_id = ?", [
                $hotelId, $name, $dept, $pos, $phone, $email, $joining, $salary, $status, $staffId
            ]);
            $_SESSION['flash_success'] = "Staff record updated!";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Update failed: " . $e->getMessage();
        }
        header("Location: staff.php");
        exit;
    }

    if ($action === 'delete_staff') {
        $staffId = (int)$_POST['staff_id'];
        db_execute("DELETE FROM staff WHERE staff_id = ?", [$staffId]);
        $_SESSION['flash_success'] = "Staff record removed.";
        header("Location: staff.php");
        exit;
    }
}

$deptFilter = $_GET['dept'] ?? null;
$sql = "SELECT s.*, h.name AS hotel_name, h.city FROM staff s INNER JOIN hotels h ON s.hotel_id = h.hotel_id WHERE 1=1";
$params = [];
if ($deptFilter) {
    $sql .= " AND s.department = ?";
    $params[] = $deptFilter;
}
$sql .= " ORDER BY s.staff_id ASC";

$staffList = db_fetch_all($sql, $params);
$hotels = db_fetch_all("SELECT * FROM hotels ORDER BY name ASC");

$pageHeading = 'Staff Directory & Human Resources';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
      <h3 class="admin-card-title"><i class="fa-solid fa-user-tie text-gold"></i> Staff Members (<?php echo count($staffList); ?> Personnel)</h3>
      <form method="GET" action="staff.php" style="display: flex; gap: 0.5rem;">
        <select name="dept" onchange="this.form.submit()" style="padding: 0.45rem 0.8rem; border-radius: 4px; border: 1px solid var(--admin-border); font-size: 0.82rem;">
          <option value="">All Departments</option>
          <option value="Management" <?php echo ($deptFilter === 'Management') ? 'selected' : ''; ?>>Management</option>
          <option value="Reception" <?php echo ($deptFilter === 'Reception') ? 'selected' : ''; ?>>Reception & Front Desk</option>
          <option value="Restaurant" <?php echo ($deptFilter === 'Restaurant') ? 'selected' : ''; ?>>Restaurant & F&B</option>
          <option value="Housekeeping" <?php echo ($deptFilter === 'Housekeeping') ? 'selected' : ''; ?>>Housekeeping</option>
          <option value="Wellness & Spa" <?php echo ($deptFilter === 'Wellness & Spa') ? 'selected' : ''; ?>>Wellness & Spa</option>
          <option value="Security" <?php echo ($deptFilter === 'Security') ? 'selected' : ''; ?>>Security</option>
          <option value="Maintenance" <?php echo ($deptFilter === 'Maintenance') ? 'selected' : ''; ?>>Maintenance</option>
        </select>
        <?php if ($deptFilter): ?>
          <a href="staff.php" style="font-size: 0.78rem; color: var(--admin-gold-dark); text-decoration: underline; align-self: center;">Reset</a>
        <?php endif; ?>
      </form>
    </div>

    <div class="admin-actions-bar">
      <div class="search-input-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search staff name, position..." data-table-search="staffTable">
      </div>
      <button type="button" class="admin-btn-primary" onclick="openStaffModal()">
        <i class="fa-solid fa-user-plus"></i> Add Staff Member
      </button>
    </div>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table" id="staffTable">
      <thead>
        <tr>
          <th>Name & Position</th>
          <th>Department</th>
          <th>Palace Location</th>
          <th>Contact</th>
          <th>Joining Date</th>
          <th>Monthly Payroll</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($staffList as $st): ?>
          <tr>
            <td>
              <strong><?php echo htmlspecialchars($st['name']); ?></strong><br>
              <small style="color: var(--color-gold-dark); font-weight: 600;"><?php echo htmlspecialchars($st['position']); ?></small>
            </td>
            <td><span class="badge badge-info"><?php echo htmlspecialchars($st['department']); ?></span></td>
            <td><?php echo htmlspecialchars($st['hotel_name']); ?></td>
            <td>
              <small><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($st['phone']); ?></small><br>
              <small style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($st['email']); ?></small>
            </td>
            <td><?php echo format_stay_date($st['joining_date']); ?></td>
            <td><strong><?php echo format_inr($st['salary']); ?></strong></td>
            <td>
              <span class="badge badge-<?php echo ($st['status'] === 'Active') ? 'success' : 'warning'; ?>">
                <?php echo htmlspecialchars($st['status']); ?>
              </span>
            </td>
            <td>
              <div class="action-btn-group">
                <button type="button" class="btn-action-icon" title="Edit Staff" onclick='editStaffModal(<?php echo json_encode($st); ?>)'>
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <form method="POST" action="staff.php" onsubmit="return confirm('Delete staff record <?php echo addslashes($st['name']); ?>?');" style="display: inline;">
                  <input type="hidden" name="action" value="delete_staff">
                  <input type="hidden" name="staff_id" value="<?php echo $st['staff_id']; ?>">
                  <button type="submit" class="btn-action-icon" style="color: #EF4444;" title="Delete Staff">
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

<!-- Modal: Staff Form -->
<div id="staffModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.65); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: #FFFFFF; max-width: 600px; width: 90%; border-radius: 6px; padding: 2.5rem; border: 1px solid var(--admin-border); margin: 2rem auto; box-shadow: 0 20px 50px rgba(0,0,0,0.25); position: relative;">
    <button onclick="closeStaffModal()" style="position: absolute; top: 1.2rem; right: 1.2rem; background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
    
    <h3 style="font-size: 1.3rem; margin-bottom: 1.5rem;" id="modalStaffHeading">Add Staff Member</h3>

    <form method="POST" action="staff.php" id="staffForm">
      <input type="hidden" name="action" id="staffFormAction" value="create_staff">
      <input type="hidden" name="staff_id" id="modal_staff_id" value="">

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group" style="grid-column: span 2;">
          <label>Full Name</label>
          <input type="text" name="name" id="modal_st_name" class="form-control" required placeholder="Bhawani Singh">
        </div>

        <div class="form-group">
          <label>Palace Assignment</label>
          <select name="hotel_id" id="modal_st_hotel" class="form-control" required>
            <?php foreach ($hotels as $ht): ?>
              <option value="<?php echo $ht['hotel_id']; ?>"><?php echo htmlspecialchars($ht['name']); ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="form-group">
          <label>Department</label>
          <select name="department" id="modal_st_dept" class="form-control" required>
            <option value="Reception">Reception</option>
            <option value="Housekeeping">Housekeeping</option>
            <option value="Restaurant">Restaurant</option>
            <option value="Security">Security</option>
            <option value="Management">Management</option>
            <option value="Maintenance">Maintenance</option>
          </select>
        </div>

        <div class="form-group">
          <label>Designation / Position</label>
          <input type="text" name="position" id="modal_st_pos" class="form-control" placeholder="Front Desk Supervisor" required>
        </div>

        <div class="form-group">
          <label>Monthly Salary (INR)</label>
          <input type="number" name="salary" id="modal_st_salary" class="form-control" placeholder="75000" required>
        </div>

        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" name="phone" id="modal_st_phone" class="form-control" placeholder="+91 98290 88771" required>
        </div>

        <div class="form-group">
          <label>Email Address</label>
          <input type="email" name="email" id="modal_st_email" class="form-control" placeholder="staff@romapalace.com" required>
        </div>

        <div class="form-group">
          <label>Joining Date</label>
          <input type="date" name="joining_date" id="modal_st_joining" value="<?php echo date('Y-m-d'); ?>" class="form-control" required>
        </div>

        <div class="form-group">
          <label>Employment Status</label>
          <select name="status" id="modal_st_status" class="form-control">
            <option value="Active">Active</option>
            <option value="On Leave">On Leave</option>
            <option value="Resigned">Resigned</option>
          </select>
        </div>
      </div>

      <div style="margin-top: 1.5rem; text-align: right;">
        <button type="button" class="btn-outline-dark" onclick="closeStaffModal()" style="padding: 0.6rem 1.2rem; margin-right: 0.5rem;">Cancel</button>
        <button type="submit" class="admin-btn-primary" style="padding: 0.6rem 1.5rem;">Save Staff Record</button>
      </div>
    </form>
  </div>
</div>

<script>
function openStaffModal() {
  document.getElementById('modalStaffHeading').textContent = 'Add Staff Member';
  document.getElementById('staffFormAction').value = 'create_staff';
  document.getElementById('modal_staff_id').value = '';
  document.getElementById('modal_st_name').value = '';
  document.getElementById('modal_st_pos').value = '';
  document.getElementById('modal_st_salary').value = '65000';
  document.getElementById('modal_st_phone').value = '';
  document.getElementById('modal_st_email').value = '';
  document.getElementById('staffModal').style.display = 'flex';
}
function editStaffModal(s) {
  document.getElementById('modalStaffHeading').textContent = 'Edit ' + s.name;
  document.getElementById('staffFormAction').value = 'update_staff';
  document.getElementById('modal_staff_id').value = s.staff_id;
  document.getElementById('modal_st_name').value = s.name;
  document.getElementById('modal_st_hotel').value = s.hotel_id;
  document.getElementById('modal_st_dept').value = s.department;
  document.getElementById('modal_st_pos').value = s.position;
  document.getElementById('modal_st_salary').value = s.salary;
  document.getElementById('modal_st_phone').value = s.phone;
  document.getElementById('modal_st_email').value = s.email;
  document.getElementById('modal_st_joining').value = s.joining_date;
  document.getElementById('modal_st_status').value = s.status;
  document.getElementById('staffModal').style.display = 'flex';
}
function closeStaffModal() {
  document.getElementById('staffModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
