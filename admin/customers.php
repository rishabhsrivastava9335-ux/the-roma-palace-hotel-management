<?php
/**
 * THE ROMA PALACE — Customer Management & CRM
 * BTech CSE DBMS Mini Project &bull; Founder: Rishabh Srivastava
 */
require_once __DIR__ . '/includes/admin-header.php';

// Handle CRUD
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_customer') {
        $fullName = trim($_POST['full_name']);
        $email = trim(strtolower($_POST['email']));
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $state = trim($_POST['state']);
        $idType = $_POST['id_type'];
        $idNumber = trim($_POST['id_number']);

        try {
            $passHash = password_hash('Guest@123', PASSWORD_BCRYPT);
            db_execute("INSERT INTO users (email, password_hash, role, status) VALUES (?, ?, 'customer', 'active')", [$email, $passHash]);
            $uId = db_insert_id();
            db_execute("INSERT INTO customers (user_id, full_name, phone, address, city, state, id_type, id_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", [
                $uId, $fullName, $phone, $address, $city, $state, $idType, $idNumber
            ]);
            $_SESSION['flash_success'] = "Customer profile for '{$fullName}' created!";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Failed to add customer: " . $e->getMessage();
        }
        header("Location: customers.php");
        exit;
    }

    if ($action === 'update_customer') {
        $custId = (int)$_POST['customer_id'];
        $fullName = trim($_POST['full_name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $state = trim($_POST['state']);
        $idType = $_POST['id_type'];
        $idNumber = trim($_POST['id_number']);

        try {
            db_execute("UPDATE customers SET full_name = ?, phone = ?, address = ?, city = ?, state = ?, id_type = ?, id_number = ? WHERE customer_id = ?", [
                $fullName, $phone, $address, $city, $state, $idType, $idNumber, $custId
            ]);
            $_SESSION['flash_success'] = "Customer record updated!";
        } catch (Exception $e) {
            $_SESSION['flash_error'] = "Update failed: " . $e->getMessage();
        }
        header("Location: customers.php");
        exit;
    }

    if ($action === 'delete_customer') {
        $custId = (int)$_POST['customer_id'];
        $cust = db_fetch_one("SELECT user_id FROM customers WHERE customer_id = ?", [$custId]);
        if ($cust) {
            db_execute("DELETE FROM users WHERE user_id = ?", [$cust['user_id']]);
            $_SESSION['flash_success'] = "Customer record and account removed.";
        }
        header("Location: customers.php");
        exit;
    }
}

// Fetch Customer Profiles with Aggregated Stays & Lifetime Spend (DBMS Join + Aggregation)
$customers = db_fetch_all("SELECT c.*, u.email, u.status AS user_status, 
                                  COUNT(b.booking_id) AS total_bookings,
                                  COALESCE(SUM(b.total_amount), 0) AS lifetime_spend
                           FROM customers c
                           INNER JOIN users u ON c.user_id = u.user_id
                           LEFT JOIN bookings b ON c.customer_id = b.customer_id AND b.payment_status = 'Paid'
                           GROUP BY c.customer_id, u.email, u.status
                           ORDER BY c.customer_id ASC");

$pageHeading = 'Customer Relations & Guest Database';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-users text-gold"></i> Registered Guest Profiles (<?php echo count($customers); ?> Customers)</h3>
    <div class="admin-actions-bar">
      <div class="search-input-box">
        <i class="fa-solid fa-magnifying-glass"></i>
        <input type="text" placeholder="Search by name, phone, email, ID..." data-table-search="customersTable">
      </div>
      <button type="button" class="admin-btn-primary" onclick="openCustomerModal()">
        <i class="fa-solid fa-user-plus"></i> Add New Guest
      </button>
    </div>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table" id="customersTable">
      <thead>
        <tr>
          <th>ID</th>
          <th>Full Name & Contact</th>
          <th>Location</th>
          <th>Govt ID Verification</th>
          <th>Total Stays</th>
          <th>Lifetime Spend</th>
          <th>Reg. Date</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($customers as $c): ?>
          <tr>
            <td><strong>#C-<?php echo str_pad($c['customer_id'], 4, '0', STR_PAD_LEFT); ?></strong></td>
            <td>
              <strong><?php echo htmlspecialchars($c['full_name']); ?></strong><br>
              <small style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($c['email']); ?></small><br>
              <small><i class="fa-solid fa-phone" style="font-size: 0.7rem;"></i> <?php echo htmlspecialchars($c['phone']); ?></small>
            </td>
            <td><?php echo htmlspecialchars($c['city'] ? $c['city'] . ', ' . $c['state'] : 'India'); ?></td>
            <td>
              <span class="badge badge-info"><?php echo htmlspecialchars($c['id_type']); ?></span><br>
              <code style="font-size: 0.78rem; font-weight: 700;"><?php echo htmlspecialchars($c['id_number']); ?></code>
            </td>
            <td><strong><?php echo $c['total_bookings']; ?> Stays</strong></td>
            <td><strong style="color: var(--admin-gold-dark);"><?php echo format_inr($c['lifetime_spend']); ?></strong></td>
            <td><?php echo date('d M Y', strtotime($c['reg_date'])); ?></td>
            <td>
              <div class="action-btn-group">
                <button type="button" class="btn-action-icon" title="Edit Profile" onclick='editCustomerModal(<?php echo json_encode($c); ?>)'>
                  <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <form method="POST" action="customers.php" onsubmit="return confirm('Delete customer <?php echo addslashes($c['full_name']); ?>?');" style="display: inline;">
                  <input type="hidden" name="action" value="delete_customer">
                  <input type="hidden" name="customer_id" value="<?php echo $c['customer_id']; ?>">
                  <button type="submit" class="btn-action-icon" style="color: #EF4444;" title="Delete Profile">
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

<!-- Modal: Customer Form -->
<div id="customerModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.65); z-index: 1000; align-items: center; justify-content: center;">
  <div style="background: #FFFFFF; max-width: 600px; width: 90%; border-radius: 6px; padding: 2.5rem; border: 1px solid var(--admin-border); margin: 2rem auto; box-shadow: 0 20px 50px rgba(0,0,0,0.25); position: relative;">
    <button onclick="closeCustomerModal()" style="position: absolute; top: 1.2rem; right: 1.2rem; background: none; border: none; font-size: 1.4rem; cursor: pointer;">&times;</button>
    
    <h3 style="font-size: 1.3rem; margin-bottom: 1.5rem;" id="modalCustHeading">Add New Guest Profile</h3>

    <form method="POST" action="customers.php" id="customerForm">
      <input type="hidden" name="action" id="custFormAction" value="create_customer">
      <input type="hidden" name="customer_id" id="modal_customer_id" value="">

      <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
        <div class="form-group" style="grid-column: span 2;">
          <label>Full Name</label>
          <input type="text" name="full_name" id="modal_cust_name" class="form-control" placeholder="Rohan Malhotra" required>
        </div>

        <div class="form-group" id="emailGroup">
          <label>Email Address</label>
          <input type="email" name="email" id="modal_cust_email" class="form-control" placeholder="guest@example.com" required>
        </div>

        <div class="form-group">
          <label>Phone Number</label>
          <input type="tel" name="phone" id="modal_cust_phone" class="form-control" placeholder="+91 98765 43210" required>
        </div>

        <div class="form-group">
          <label>Govt ID Type</label>
          <select name="id_type" id="modal_cust_id_type" class="form-control" required>
            <option value="Aadhaar Card">Aadhaar Card</option>
            <option value="Passport">Passport</option>
            <option value="Driving License">Driving License</option>
            <option value="Voter ID">Voter ID</option>
            <option value="PAN Card">PAN Card</option>
          </select>
        </div>

        <div class="form-group">
          <label>Govt ID Number</label>
          <input type="text" name="id_number" id="modal_cust_id_num" class="form-control" placeholder="4589 1234 9876" required>
        </div>

        <div class="form-group">
          <label>City</label>
          <input type="text" name="city" id="modal_cust_city" class="form-control" placeholder="New Delhi">
        </div>

        <div class="form-group">
          <label>State</label>
          <input type="text" name="state" id="modal_cust_state" class="form-control" placeholder="Delhi">
        </div>

        <div class="form-group" style="grid-column: span 2;">
          <label>Address</label>
          <textarea name="address" id="modal_cust_address" class="form-control" rows="2" placeholder="Residential address..."></textarea>
        </div>
      </div>

      <div style="margin-top: 1.5rem; text-align: right;">
        <button type="button" class="btn-outline-dark" onclick="closeCustomerModal()" style="padding: 0.6rem 1.2rem; margin-right: 0.5rem;">Cancel</button>
        <button type="submit" class="admin-btn-primary" style="padding: 0.6rem 1.5rem;">Save Guest Profile</button>
      </div>
    </form>
  </div>
</div>

<script>
function openCustomerModal() {
  document.getElementById('modalCustHeading').textContent = 'Add New Guest Profile';
  document.getElementById('custFormAction').value = 'create_customer';
  document.getElementById('modal_customer_id').value = '';
  document.getElementById('modal_cust_name').value = '';
  document.getElementById('modal_cust_email').value = '';
  document.getElementById('modal_cust_phone').value = '';
  document.getElementById('emailGroup').style.display = 'block';
  document.getElementById('customerModal').style.display = 'flex';
}
function editCustomerModal(c) {
  document.getElementById('modalCustHeading').textContent = 'Edit Profile: ' + c.full_name;
  document.getElementById('custFormAction').value = 'update_customer';
  document.getElementById('modal_customer_id').value = c.customer_id;
  document.getElementById('modal_cust_name').value = c.full_name;
  document.getElementById('modal_cust_phone').value = c.phone;
  document.getElementById('modal_cust_id_type').value = c.id_type;
  document.getElementById('modal_cust_id_num').value = c.id_number;
  document.getElementById('modal_cust_city').value = c.city || '';
  document.getElementById('modal_cust_state').value = c.state || '';
  document.getElementById('modal_cust_address').value = c.address || '';
  document.getElementById('emailGroup').style.display = 'none';
  document.getElementById('customerModal').style.display = 'flex';
}
function closeCustomerModal() {
  document.getElementById('customerModal').style.display = 'none';
}
</script>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
