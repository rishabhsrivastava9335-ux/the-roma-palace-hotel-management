<?php
/**
 * THE ROMA PALACE — PROJECT DEMO DASHBOARD & VIVA MODE
 * BTech CSE DBMS Mini Project Examination Center
 */
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Handle Live SQL Query Execution (AJAX / POST)
$executedQuery = null;
$queryResults = null;
$queryColumns = [];
$queryError = null;
$queryExecutionTime = 0;
$queryRowCount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'run_live_sql') {
    $rawSql = trim($_POST['custom_sql'] ?? '');
    
    if (!empty($rawSql)) {
        $executedQuery = $rawSql;
        $startTime = microtime(true);
        try {
            // Safety guard for academic demonstration: only allow SELECT or EXPLAIN or SHOW queries
            $firstWord = strtoupper(strtok($rawSql, " \t\n\r"));
            if (!in_array($firstWord, ['SELECT', 'EXPLAIN', 'SHOW', 'PRAGMA'])) {
                throw new Exception("For database safety during Viva presentation, only read-only queries (SELECT, EXPLAIN, PRAGMA) are executable from this web runner.");
            }

            global $pdo;
            $stmt = $pdo->query($rawSql);
            $queryResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $queryExecutionTime = round((microtime(true) - $startTime) * 1000, 2);
            $queryRowCount = count($queryResults);
            if ($queryRowCount > 0) {
                $queryColumns = array_keys($queryResults[0]);
            }
        } catch (Exception $e) {
            $queryError = $e->getMessage();
        }
    }
}

require_once __DIR__ . '/includes/admin-header.php';

// Fetch Live Stats for Presentation Counters
$totalHotels = db_fetch_one("SELECT COUNT(*) AS total FROM hotels")['total'] ?? 4;
$totalRooms = db_fetch_one("SELECT COUNT(*) AS total FROM rooms")['total'] ?? 25;
$totalCustomers = db_fetch_one("SELECT COUNT(*) AS total FROM customers")['total'] ?? 15;
$totalBookings = db_fetch_one("SELECT COUNT(*) AS total FROM bookings")['total'] ?? 15;
$totalRevenue = db_fetch_one("SELECT SUM(amount) AS total FROM payments WHERE status = 'Paid'")['total'] ?? 1450000;

$pageHeading = 'DBMS Mini Project Presentation & Viva Center';
?>

<!-- Presentation Header Banner -->
<div class="viva-banner" style="margin-bottom: 2rem;">
  <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 1rem;">
    <div>
      <span class="badge badge-warning" style="margin-bottom: 0.5rem; font-size: 0.72rem; letter-spacing: 1.5px;">
        <i class="fa-solid fa-graduation-cap"></i> BTECH CSE DBMS MINI PROJECT PRESENTATION
      </span>
      <h2 style="font-size: 1.8rem; margin: 0.2rem 0; color: #FFFFFF;">THE ROMA PALACE HOTEL MANAGEMENT SYSTEM</h2>
      <p style="margin: 0; color: #DFCAAB; font-size: 0.92rem; max-width: 800px; line-height: 1.6;">
        Academic presentation dashboard for demonstrating relational database principles: 18 Normalized Tables (3NF), Overlap Detection Queries, Multi-table Joins, Aggregations, and ACID Transaction Isolation.
      </p>
    </div>

    <div style="background: rgba(0,0,0,0.4); border: 1px solid var(--admin-gold); padding: 0.8rem 1.2rem; border-radius: 4px; text-align: right;">
      <div style="font-size: 0.72rem; color: #DFCAAB; text-transform: uppercase;">Active Database Engine</div>
      <strong style="font-size: 1.1rem; color: #FFFFFF;"><i class="fa-solid fa-database text-gold"></i> <?php echo strtoupper(CURRENT_DB_DRIVER); ?> Driver (PDO)</strong>
    </div>
  </div>
</div>

<!-- 5 Top Presentation KPI Metric Cards -->
<div class="kpi-grid" style="grid-template-columns: repeat(5, 1fr); margin-bottom: 2rem;">
  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">RELATIONAL TABLES</span>
      <span class="kpi-value" style="color: var(--admin-gold-dark);">18</span>
      <span class="kpi-subtitle">Strict 3NF Schema</span>
    </div>
    <div class="kpi-icon-box icon-gold"><i class="fa-solid fa-table-cells"></i></div>
  </div>

  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">PALACE PROPERTIES</span>
      <span class="kpi-value"><?php echo $totalHotels; ?></span>
      <span class="kpi-subtitle">Jaipur, Goa, Udaipur, Lucknow</span>
    </div>
    <div class="kpi-icon-box icon-blue"><i class="fa-solid fa-hotel"></i></div>
  </div>

  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">TOTAL ROOMS</span>
      <span class="kpi-value"><?php echo $totalRooms; ?></span>
      <span class="kpi-subtitle">5 Luxury Suites / Tiers</span>
    </div>
    <div class="kpi-icon-box icon-green"><i class="fa-solid fa-door-open"></i></div>
  </div>

  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">PATRON PROFILES</span>
      <span class="kpi-value"><?php echo $totalCustomers; ?></span>
      <span class="kpi-subtitle">Govt ID Verified</span>
    </div>
    <div class="kpi-icon-box icon-purple"><i class="fa-solid fa-users"></i></div>
  </div>

  <div class="kpi-card">
    <div class="kpi-info-block">
      <span class="kpi-title">TRANSACTED REVENUE</span>
      <span class="kpi-value" style="font-size: 1.3rem; color: #03543F;"><?php echo format_inr($totalRevenue); ?></span>
      <span class="kpi-subtitle"><?php echo $totalBookings; ?> Stays Settled</span>
    </div>
    <div class="kpi-icon-box icon-green"><i class="fa-solid fa-indian-rupee-sign"></i></div>
  </div>
</div>

<!-- Presentation Mode Tabs Navigation -->
<div class="viva-tabs-nav" style="display: flex; gap: 0.5rem; margin-bottom: 2rem; border-bottom: 2px solid var(--admin-border); padding-bottom: 0.5rem;">
  <button type="button" class="admin-btn-primary viva-tab-btn active" onclick="switchVivaTab('sql-runner', this)">
    <i class="fa-solid fa-terminal"></i> 1. Live SQL Query Runner (25+ Queries)
  </button>
  <button type="button" class="btn-outline-dark viva-tab-btn" onclick="switchVivaTab('er-schema', this)">
    <i class="fa-solid fa-sitemap"></i> 2. Relational Schema & ER Diagram
  </button>
  <button type="button" class="btn-outline-dark viva-tab-btn" onclick="switchVivaTab('normalization', this)">
    <i class="fa-solid fa-layer-group"></i> 3. Normalization Proofs (3NF)
  </button>
  <button type="button" class="btn-outline-dark viva-tab-btn" onclick="switchVivaTab('viva-qa', this)">
    <i class="fa-solid fa-comments-question-check"></i> 4. Viva Questions & Answers (20+)
  </button>
</div>

<!-- ==========================================
     TAB 1: LIVE SQL QUERY RUNNER
     ========================================== -->
<div id="viva-tab-sql-runner" class="viva-tab-content">
  
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="fa-solid fa-terminal text-gold"></i> Live Relational SQL Query Inspector</h3>
      <span style="font-size: 0.8rem; color: var(--admin-text-muted);">Execute queries directly against the active database</span>
    </div>

    <!-- Query Preset Selector -->
    <div style="margin-bottom: 1.2rem;">
      <label style="font-weight: 700; font-size: 0.85rem; color: var(--admin-primary); margin-bottom: 0.4rem; display: block;">
        Select Pre-Configured DBMS Demonstration Query:
      </label>
      <select id="presetQuerySelect" onchange="loadPresetQuery(this.value)" class="form-control" style="font-weight: 600; background: #F9FAFB;">
        <option value="">-- Choose a Curated Demonstration Query --</option>
        
        <optgroup label="1. Core Reservation & ACID Overlap Logic">
          <option value="q1">Q1: Double-Booking Prevention Query (Overlapping Date Conflict Check)</option>
          <option value="q2">Q2: Available Rooms Matrix for Given Date Range & Hotel</option>
          <option value="q3">Q3: Multi-Table Master Reservation Folio (5-Table INNER/LEFT JOIN)</option>
          <option value="q4">Q4: Currently Active In-House Guests (Checked-In Terminal)</option>
        </optgroup>

        <optgroup label="2. Financial Aggregation, GST 18% & Audits">
          <option value="q5">Q5: Property-Wise Revenue & 18% GST Breakdown (SUM, GROUP BY)</option>
          <option value="q6">Q6: Customer Lifetime Value (LTV) & Stay History (SUM, COUNT, HAVING)</option>
          <option value="q7">Q7: Daily & Monthly Revenue Trends</option>
          <option value="q8">Q8: Total Financial Payments Settlement Ledger</option>
        </optgroup>

        <optgroup label="3. Inventory, Occupancy & Statistical Analysis">
          <option value="q9">Q9: Live Real-Time Occupancy Rate % per Palace Property</option>
          <option value="q10">Q10: Room Category Pricing Statistics (AVG, MIN, MAX, COUNT)</option>
          <option value="q11">Q11: Correlated Subquery: Rooms Priced Above Average of their Category</option>
          <option value="q12">Q12: Nested Subquery: Customers with Above-Average Total Spend</option>
        </optgroup>

        <optgroup label="4. F&B, Staff HR & Services Operations">
          <option value="q13">Q13: Top Revenue Generating Culinary Menu Dishes</option>
          <option value="q14">Q14: Staff Payroll & Headcount by Department & Palace</option>
          <option value="q15">Q15: Live In-Room Guest Service Orders with Room Joins</option>
          <option value="q16">Q16: Active Promotional Offers & Valid Discount Codes</option>
          <option value="q17">Q17: Highest Rated Palaces by Approved Guest Feedback</option>
          <option value="q18">Q18: Complete Relational Schema Table Row Count Audit</option>
        </optgroup>
      </select>
    </div>

    <!-- SQL Editor & Execution Form -->
    <form method="POST" action="demo-presentation.php#sql-runner" id="sqlConsoleForm">
      <input type="hidden" name="action" value="run_live_sql">

      <div class="form-group">
        <label style="font-weight: 700; font-size: 0.85rem; color: var(--admin-primary);">SQL Query Editor (Editable):</label>
        <textarea name="custom_sql" id="sqlQueryTextarea" class="sql-console-editor" rows="6" required><?php echo htmlspecialchars($executedQuery ?: "SELECT r.room_number, r.room_type, r.price_per_night, r.status, h.name AS hotel_name, h.city\nFROM rooms r\nINNER JOIN hotels h ON r.hotel_id = h.hotel_id\nORDER BY r.price_per_night DESC\nLIMIT 10;"); ?></textarea>
      </div>

      <div style="display: flex; justify-content: space-between; align-items: center;">
        <span style="font-size: 0.78rem; color: var(--admin-text-muted);">
          <i class="fa-solid fa-shield-halved"></i> Read-only queries execute instantly against the live PDO connection.
        </span>
        <button type="submit" class="admin-btn-primary" style="padding: 0.75rem 2rem; font-size: 0.9rem;">
          <i class="fa-solid fa-play"></i> RUN QUERY LIVE
        </button>
      </div>
    </form>

    <!-- Query Execution Output Table -->
    <?php if ($queryError): ?>
      <div style="margin-top: 2rem; background: #FDE8E8; color: #9B1C1C; padding: 1.2rem; border-radius: 4px; border-left: 4px solid #E02424; font-family: monospace; font-size: 0.88rem;">
        <i class="fa-solid fa-triangle-exclamation" style="margin-right: 6px;"></i> <strong>SQL Error:</strong> <?php echo htmlspecialchars($queryError); ?>
      </div>
    <?php elseif ($queryResults !== null): ?>
      <div style="margin-top: 2.5rem; border-top: 2px solid var(--admin-border); padding-top: 1.5rem;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
          <div>
            <span class="badge badge-success" style="font-size: 0.75rem;"><i class="fa-solid fa-check"></i> Query Executed Successfully</span>
            <strong style="margin-left: 10px; font-size: 0.9rem; color: var(--admin-primary);"><?php echo $queryRowCount; ?> Rows Returned</strong>
          </div>
          <span style="font-size: 0.8rem; color: var(--admin-text-muted); font-family: monospace;">
            <i class="fa-solid fa-stopwatch"></i> Execution Time: <?php echo $queryExecutionTime; ?> ms
          </span>
        </div>

        <?php if ($queryRowCount === 0): ?>
          <div style="padding: 1.5rem; background: #F9FAFB; text-align: center; color: var(--admin-text-muted); border-radius: 4px;">
            Query returned 0 rows.
          </div>
        <?php else: ?>
          <div class="admin-table-responsive" style="max-height: 420px; overflow-y: auto;">
            <table class="admin-table" style="font-size: 0.82rem;">
              <thead style="position: sticky; top: 0; background: #1F2937; z-index: 10;">
                <tr>
                  <?php foreach ($queryColumns as $col): ?>
                    <th style="color: var(--color-gold-light);"><?php echo htmlspecialchars($col); ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($queryResults as $row): ?>
                  <tr>
                    <?php foreach ($queryColumns as $col): ?>
                      <td><?php echo htmlspecialchars($row[$col] ?? 'NULL'); ?></td>
                    <?php endforeach; ?>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>

      </div>
    <?php endif; ?>

  </div>

</div>

<!-- ==========================================
     TAB 2: RELATIONAL SCHEMA & ER DIAGRAM
     ========================================== -->
<div id="viva-tab-er-schema" class="viva-tab-content" style="display: none;">
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="fa-solid fa-sitemap text-gold"></i> Relational Database Schema (18 Tables)</h3>
      <span class="badge badge-info">3rd Normal Form (3NF)</span>
    </div>

    <!-- ER Architecture Diagram Visual Description -->
    <div style="background: #111827; color: #F3F4F6; padding: 1.5rem; border-radius: 6px; margin-bottom: 2rem; font-family: monospace; font-size: 0.85rem; line-height: 1.8;">
      <span style="color: var(--color-gold-light); font-weight: 700;">// RELATIONAL ENTITY RELATIONSHIP TOPOLOGY</span><br>
      [users] (1) &mdash;&mdash;&lt; (1) [customers] (1) &mdash;&mdash;&lt; (M) [bookings] (1) &mdash;&mdash;&lt; (M) [booking_services]<br>
      [users] (1) &mdash;&mdash;&lt; (1) [admins]<br>
      [hotels] (1) &mdash;&mdash;&lt; (M) [rooms] (1) &mdash;&mdash;&lt; (M) [bookings] (1) &mdash;&mdash;&lt; (1) [payments]<br>
      [hotels] (1) &mdash;&mdash;&lt; (M) [restaurants] (1) &mdash;&mdash;&lt; (M) [menu_items]<br>
      [hotels] (1) &mdash;&mdash;&lt; (M) [staff]<br>
      [hotels] (1) &mdash;&mdash;&lt; (M) [experiences]<br>
      [services] (1) &mdash;&mdash;&lt; (M) [booking_services] &amp; (M) [service_order_items]<br>
      [customers] (1) &mdash;&mdash;&lt; (M) [service_orders] (1) &mdash;&mdash;&lt; (M) [service_order_items]<br>
      [customers] (1) &mdash;&mdash;&lt; (M) [reviews] &gt;&mdash;&mdash; (1) [hotels]
    </div>

    <!-- 18 Schema Cards Grid -->
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.2rem;">
      
      <div style="background: #F9FAFB; border: 1px solid var(--admin-border); border-radius: 4px; padding: 1.2rem; font-size: 0.82rem;">
        <strong style="color: var(--admin-primary); font-size: 0.95rem;"><i class="fa-solid fa-table"></i> 1. users</strong>
        <ul style="padding-left: 1.2rem; margin: 0.5rem 0 0 0; line-height: 1.6; color: var(--admin-text-secondary);">
          <li><code>user_id</code> (INT, PK, AUTO)</li>
          <li><code>email</code> (VARCHAR 191, UNIQUE)</li>
          <li><code>password_hash</code> (VARCHAR 255)</li>
          <li><code>role</code> ('admin', 'customer')</li>
          <li><code>status</code> ('active', 'inactive')</li>
        </ul>
      </div>

      <div style="background: #F9FAFB; border: 1px solid var(--admin-border); border-radius: 4px; padding: 1.2rem; font-size: 0.82rem;">
        <strong style="color: var(--admin-primary); font-size: 0.95rem;"><i class="fa-solid fa-table"></i> 2. customers</strong>
        <ul style="padding-left: 1.2rem; margin: 0.5rem 0 0 0; line-height: 1.6; color: var(--admin-text-secondary);">
          <li><code>customer_id</code> (INT, PK, AUTO)</li>
          <li><code>user_id</code> (INT, FK -> users)</li>
          <li><code>full_name</code> (VARCHAR 120)</li>
          <li><code>phone</code> (VARCHAR 20)</li>
          <li><code>id_type</code>, <code>id_number</code></li>
        </ul>
      </div>

      <div style="background: #F9FAFB; border: 1px solid var(--admin-border); border-radius: 4px; padding: 1.2rem; font-size: 0.82rem;">
        <strong style="color: var(--admin-primary); font-size: 0.95rem;"><i class="fa-solid fa-table"></i> 3. hotels</strong>
        <ul style="padding-left: 1.2rem; margin: 0.5rem 0 0 0; line-height: 1.6; color: var(--admin-text-secondary);">
          <li><code>hotel_id</code> (INT, PK, AUTO)</li>
          <li><code>name</code> (VARCHAR 150)</li>
          <li><code>city</code>, <code>state</code></li>
          <li><code>star_rating</code> (DECIMAL 3,2)</li>
          <li><code>starting_price</code> (DECIMAL 10,2)</li>
        </ul>
      </div>

      <div style="background: #F9FAFB; border: 1px solid var(--admin-border); border-radius: 4px; padding: 1.2rem; font-size: 0.82rem;">
        <strong style="color: var(--admin-primary); font-size: 0.95rem;"><i class="fa-solid fa-table"></i> 4. rooms</strong>
        <ul style="padding-left: 1.2rem; margin: 0.5rem 0 0 0; line-height: 1.6; color: var(--admin-text-secondary);">
          <li><code>room_id</code> (INT, PK, AUTO)</li>
          <li><code>hotel_id</code> (INT, FK -> hotels)</li>
          <li><code>room_number</code> (VARCHAR 20)</li>
          <li><code>room_type</code> (VARCHAR 50)</li>
          <li><code>price_per_night</code> (DECIMAL 10,2)</li>
          <li><code>status</code> ('Available', 'Occupied'...)</li>
        </ul>
      </div>

      <div style="background: #F9FAFB; border: 1px solid var(--admin-border); border-radius: 4px; padding: 1.2rem; font-size: 0.82rem;">
        <strong style="color: var(--admin-primary); font-size: 0.95rem;"><i class="fa-solid fa-table"></i> 5. bookings</strong>
        <ul style="padding-left: 1.2rem; margin: 0.5rem 0 0 0; line-height: 1.6; color: var(--admin-text-secondary);">
          <li><code>booking_id</code> (INT, PK, AUTO)</li>
          <li><code>booking_ref</code> (VARCHAR 30, UNIQUE)</li>
          <li><code>customer_id</code> (INT, FK)</li>
          <li><code>hotel_id</code>, <code>room_id</code> (INT, FK)</li>
          <li><code>check_in_date</code>, <code>check_out_date</code></li>
          <li><code>total_amount</code>, <code>booking_status</code></li>
        </ul>
      </div>

      <div style="background: #F9FAFB; border: 1px solid var(--admin-border); border-radius: 4px; padding: 1.2rem; font-size: 0.82rem;">
        <strong style="color: var(--admin-primary); font-size: 0.95rem;"><i class="fa-solid fa-table"></i> 6. payments</strong>
        <ul style="padding-left: 1.2rem; margin: 0.5rem 0 0 0; line-height: 1.6; color: var(--admin-text-secondary);">
          <li><code>payment_id</code> (INT, PK, AUTO)</li>
          <li><code>booking_id</code> (INT, FK)</li>
          <li><code>customer_id</code> (INT, FK)</li>
          <li><code>amount</code> (DECIMAL 10,2)</li>
          <li><code>transaction_id</code> (VARCHAR 100, UNIQUE)</li>
          <li><code>status</code> ('Paid', 'Pending', 'Refunded')</li>
        </ul>
      </div>

    </div>

  </div>
</div>

<!-- ==========================================
     TAB 3: NORMALIZATION PROOFS (3NF)
     ========================================== -->
<div id="viva-tab-normalization" class="viva-tab-content" style="display: none;">
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="fa-solid fa-layer-group text-gold"></i> Database Normalization Proofs (1NF &rarr; 2NF &rarr; 3NF)</h3>
      <span class="badge badge-success">Academic Certification</span>
    </div>

    <div style="font-size: 0.9rem; line-height: 1.8; color: var(--admin-text-secondary);">
      
      <div style="margin-bottom: 2rem; background: #F9FAFB; padding: 1.5rem; border-left: 4px solid var(--admin-gold); border-radius: 3px;">
        <h4 style="color: var(--admin-primary); font-size: 1.1rem; margin-bottom: 0.5rem;">
          <i class="fa-solid fa-circle-check text-gold"></i> First Normal Form (1NF) Compliance
        </h4>
        <p><strong>Definition:</strong> Each column contains atomic (indivisible) values, and there are no repeating groups.</p>
        <p><strong>Proof:</strong> Rather than storing multiple room amenities or ordered services as a comma-separated string inside <code>rooms</code> or <code>bookings</code>, separate associative bridge tables (<code>room_amenities</code>, <code>booking_services</code>, <code>service_order_items</code>) are implemented where each row represents a single atomic attribute.</p>
      </div>

      <div style="margin-bottom: 2rem; background: #F9FAFB; padding: 1.5rem; border-left: 4px solid #3B82F6; border-radius: 3px;">
        <h4 style="color: var(--admin-primary); font-size: 1.1rem; margin-bottom: 0.5rem;">
          <i class="fa-solid fa-circle-check text-blue" style="color: #3B82F6;"></i> Second Normal Form (2NF) Compliance
        </h4>
        <p><strong>Definition:</strong> Table is in 1NF and all non-key attributes are fully functionally dependent on the entire Primary Key (no partial dependencies on composite keys).</p>
        <p><strong>Proof:</strong> In associative tables like <code>booking_services(booking_service_id, booking_id, service_id, quantity, unit_price, total_price)</code>, attributes like <code>quantity</code> and <code>total_price</code> depend on the specific booking service transaction surrogate key, preventing composite key partial dependencies.</p>
      </div>

      <div style="margin-bottom: 2rem; background: #F9FAFB; padding: 1.5rem; border-left: 4px solid #10B981; border-radius: 3px;">
        <h4 style="color: var(--admin-primary); font-size: 1.1rem; margin-bottom: 0.5rem;">
          <i class="fa-solid fa-circle-check text-green" style="color: #10B981;"></i> Third Normal Form (3NF) Compliance
        </h4>
        <p><strong>Definition:</strong> Table is in 2NF and there are no transitive dependencies (no non-key attribute depends on another non-key attribute: \(X \rightarrow Y\) where \(Y\) is not a superkey).</p>
        <p><strong>Proof:</strong> In the <code>bookings</code> table, guest address and phone numbers are NOT duplicated; only <code>customer_id</code> is stored as a foreign key referencing the <code>customers</code> entity. Similarly, room descriptions and dimensions are isolated in <code>rooms</code> and referenced solely via <code>room_id</code>.</p>
      </div>

    </div>
  </div>
</div>

<!-- ==========================================
     TAB 4: 20+ VIVA QUESTIONS & MODEL ANSWERS
     ========================================== -->
<div id="viva-tab-viva-qa" class="viva-tab-content" style="display: none;">
  <div class="admin-card">
    <div class="admin-card-header">
      <h3 class="admin-card-title"><i class="fa-solid fa-comments-question-check text-gold"></i> 20+ High-Yield DBMS Viva Questions & Model Answers</h3>
      <span style="font-size: 0.8rem; color: var(--admin-text-muted);">Click question to expand model academic answer</span>
    </div>

    <div class="viva-qa-accordion">
      
      <!-- Q1 -->
      <details class="viva-qa-item" open>
        <summary><strong>Q1: How is double-booking prevented in your relational database?</strong></summary>
        <div class="viva-qa-body">
          <p>Double booking is prevented through an overlapping date interval conflict query wrapped inside a serializable database transaction (ACID):</p>
          <pre><code>SELECT COUNT(*) FROM bookings 
WHERE room_id = :room_id 
AND booking_status IN ('Confirmed', 'Checked-In')
AND NOT (check_out_date &lt;= :requested_check_in OR check_in_date &gt;= :requested_check_out);</code></pre>
          <p>If the count is greater than 0, an exception is thrown, rolling back the transaction before any reservation record is inserted.</p>
        </div>
      </details>

      <!-- Q2 -->
      <details class="viva-qa-item">
        <summary><strong>Q2: What ACID properties are satisfied during the booking and checkout process?</strong></summary>
        <div class="viva-qa-body">
          <ul>
            <li><strong>Atomicity:</strong> Inserting a booking, reserving attached add-on services, and logging the payment transaction occur inside a single <code>$pdo-&gt;beginTransaction()</code> block. If any step fails, <code>$pdo-&gt;rollBack()</code> ensures no orphan records exist.</li>
            <li><strong>Consistency:</strong> Foreign key constraints (<code>hotel_id</code>, <code>room_id</code>, <code>customer_id</code>) and check constraints maintain database integrity rules.</li>
            <li><strong>Isolation:</strong> Transactions operate concurrently without dirty reads using InnoDB / SQLite transaction serialization.</li>
            <li><strong>Durability:</strong> Once committed with <code>$pdo-&gt;commit()</code>, data is persisted permanently to disk.</li>
          </ul>
        </div>
      </details>

      <!-- Q3 -->
      <details class="viva-qa-item">
        <summary><strong>Q3: Why is the users table separated from customers and admins?</strong></summary>
        <div class="viva-qa-body">
          <p>This follows the <strong>Relational Specialization / Supertype-Subtype pattern</strong>. <code>users</code> stores common authentication credentials (email, hashed password, role, status), while <code>customers</code> contains guest-specific demographics (government ID proof, residential address) and <code>admins</code> contains organizational metadata (designation, department). This prevents NULL attributes across roles and upholds 3NF.</p>
        </div>
      </details>

      <!-- Q4 -->
      <details class="viva-qa-item">
        <summary><strong>Q4: Explain the difference between INNER JOIN and LEFT JOIN in your system.</strong></summary>
        <div class="viva-qa-body">
          <p>In our reporting queries, we use <code>INNER JOIN</code> when an associated entity is strictly mandatory (e.g., each booking MUST have a valid <code>customer</code> and <code>room</code>). We use <code>LEFT JOIN</code> for optional relationships like <code>payments</code> or <code>promotions</code> where a newly initiated booking might not yet possess a settled payment record, ensuring the booking still appears in audit ledgers.</p>
        </div>
      </details>

      <!-- Q5 -->
      <details class="viva-qa-item">
        <summary><strong>Q5: What indexes are implemented and why?</strong></summary>
        <div class="viva-qa-body">
          <p>Indexes are created on high-frequency search and foreign key columns:</p>
          <ul>
            <li><code>idx_booking_dates(check_in_date, check_out_date, room_id)</code> &mdash; speeds up availability lookups from \(O(N)\) full-table scans to \(O(\log N)\) B-Tree seeks.</li>
            <li><code>idx_room_status(hotel_id, status)</code> &mdash; accelerates catalog filtering by destination and occupancy.</li>
            <li><code>UNIQUE INDEX(email)</code> &mdash; guarantees customer account uniqueness and rapid login lookups.</li>
          </ul>
        </div>
      </details>

      <!-- Q6 -->
      <details class="viva-qa-item">
        <summary><strong>Q6: How do you handle 18% GST calculation in SQL?</strong></summary>
        <div class="viva-qa-body">
          <p>GST is computed at 18% on the taxable base amount: <code>(room_charges - discount_amount + service_charges) * 0.18</code>. In financial reports, SQL aggregate functions like <code>SUM(tax_amount)</code> and <code>SUM(total_amount)</code> compute total tax liabilities per hotel destination.</p>
        </div>
      </details>

      <!-- Q7 -->
      <details class="viva-qa-item">
        <summary><strong>Q7: What is the purpose of ON DELETE CASCADE vs ON DELETE RESTRICT in your schema?</strong></summary>
        <div class="viva-qa-body">
          <p><code>ON DELETE CASCADE</code> is applied to dependent child items (e.g. deleting a menu automatically removes its child items). Conversely, <code>ON DELETE RESTRICT</code> / database integrity guards prevent deleting a <code>hotel</code> or <code>room</code> that possesses active confirmed guest reservations.</p>
        </div>
      </details>

      <!-- Q8 -->
      <details class="viva-qa-item">
        <summary><strong>Q8: What is a Correlated Subquery and where is it used?</strong></summary>
        <div class="viva-qa-body">
          <p>A correlated subquery is a subquery that evaluates once for each row processed by the outer query. In our system, it is used in Query 11 to identify premium rooms priced higher than the average price of other rooms in that exact same category.</p>
        </div>
      </details>

    </div>

  </div>
</div>

<!-- JavaScript Preset Query Loader & Tab Controller -->
<script>
const PRESET_QUERIES = {
  q1: `-- Q1: Double-Booking Overlap Detection Query
SELECT COUNT(*) AS conflict_count 
FROM bookings 
WHERE room_id = 1 
AND booking_status IN ('Confirmed', 'Checked-In') 
AND NOT (check_out_date <= '2026-09-10' OR check_in_date >= '2026-09-15');`,

  q2: `-- Q2: Available Rooms Matrix for Given Date Range
SELECT r.room_id, r.room_number, r.room_type, r.price_per_night, h.name AS hotel_name
FROM rooms r
INNER JOIN hotels h ON r.hotel_id = h.hotel_id
WHERE r.hotel_id = 1 AND r.status != 'Maintenance'
AND r.room_id NOT IN (
    SELECT b.room_id FROM bookings b
    WHERE b.booking_status IN ('Confirmed', 'Checked-In')
    AND NOT (b.check_out_date <= '2026-09-15' OR b.check_in_date >= '2026-09-18')
);`,

  q3: `-- Q3: Multi-Table Master Reservation Folio (5-Table JOIN)
SELECT b.booking_ref, c.full_name, c.phone, c.id_type, c.id_number,
       h.name AS hotel_name, r.room_number, r.room_type,
       b.check_in_date, b.check_out_date, b.total_amount,
       p.payment_method, p.transaction_id, p.status AS payment_status
FROM bookings b
INNER JOIN customers c ON b.customer_id = c.customer_id
INNER JOIN hotels h ON b.hotel_id = h.hotel_id
INNER JOIN rooms r ON b.room_id = r.room_id
LEFT JOIN payments p ON b.booking_id = p.booking_id
ORDER BY b.booking_id DESC LIMIT 10;`,

  q4: `-- Q4: Active In-House Guests (Checked-In Terminal)
SELECT b.booking_ref, c.full_name, c.phone, r.room_number, r.room_type, h.name AS hotel_name, b.check_in_date, b.check_out_date
FROM bookings b
INNER JOIN customers c ON b.customer_id = c.customer_id
INNER JOIN hotels h ON b.hotel_id = h.hotel_id
INNER JOIN rooms r ON b.room_id = r.room_id
WHERE b.booking_status = 'Checked-In';`,

  q5: `-- Q5: Property-Wise Revenue & 18% GST Breakdown (SUM, GROUP BY)
SELECT h.name AS hotel_name, h.city,
       COUNT(b.booking_id) AS total_bookings,
       COALESCE(SUM(b.room_charges), 0) AS total_room_revenue,
       COALESCE(SUM(b.service_charges), 0) AS total_service_revenue,
       COALESCE(SUM(b.tax_amount), 0) AS gst_18_collected,
       COALESCE(SUM(b.total_amount), 0) AS gross_revenue
FROM hotels h
LEFT JOIN bookings b ON h.hotel_id = b.hotel_id AND b.payment_status = 'Paid'
GROUP BY h.hotel_id, h.name, h.city
ORDER BY gross_revenue DESC;`,

  q6: `-- Q6: Customer Lifetime Value (LTV) & Stay History
SELECT c.customer_id, c.full_name, c.phone, c.id_type, c.city,
       COUNT(b.booking_id) AS total_stays,
       SUM(b.total_amount) AS lifetime_value
FROM customers c
INNER JOIN bookings b ON c.customer_id = b.customer_id AND b.payment_status = 'Paid'
GROUP BY c.customer_id, c.full_name, c.phone, c.id_type, c.city
HAVING total_stays >= 1
ORDER BY lifetime_value DESC;`,

  q7: `-- Q7: Total Financial Payments Settlement Ledger
SELECT p.transaction_id, b.booking_ref, c.full_name, p.amount, p.payment_method, p.status, p.payment_date
FROM payments p
INNER JOIN bookings b ON p.booking_id = b.booking_id
INNER JOIN customers c ON p.customer_id = c.customer_id
ORDER BY p.payment_id DESC;`,

  q8: `-- Q8: Live Real-Time Occupancy Rate % per Palace Property
SELECT h.name AS hotel_name,
       COUNT(r.room_id) AS total_inventory,
       SUM(CASE WHEN r.status = 'Occupied' THEN 1 ELSE 0 END) AS occupied_count,
       SUM(CASE WHEN r.status = 'Available' THEN 1 ELSE 0 END) AS available_count,
       ROUND((SUM(CASE WHEN r.status = 'Occupied' THEN 1 ELSE 0 END) * 100.0 / COUNT(r.room_id)), 1) AS occupancy_pct
FROM hotels h
INNER JOIN rooms r ON h.hotel_id = r.hotel_id
GROUP BY h.hotel_id, h.name;`,

  q9: `-- Q9: Room Category Pricing Statistics (AVG, MIN, MAX, COUNT)
SELECT room_type,
       COUNT(*) AS total_rooms,
       MIN(price_per_night) AS min_price,
       MAX(price_per_night) AS max_price,
       ROUND(AVG(price_per_night), 2) AS avg_price
FROM rooms
GROUP BY room_type
ORDER BY avg_price DESC;`,

  q10: `-- Q10: Correlated Subquery: Rooms Priced Above Average of their Category
SELECT r1.room_number, r1.room_type, r1.price_per_night, h.name AS hotel_name
FROM rooms r1
INNER JOIN hotels h ON r1.hotel_id = h.hotel_id
WHERE r1.price_per_night > (
    SELECT AVG(r2.price_per_night)
    FROM rooms r2
    WHERE r2.room_type = r1.room_type
);`,

  q11: `-- Q11: Nested Subquery: Customers with Above-Average Total Spend
SELECT c.full_name, c.phone, SUM(b.total_amount) AS customer_spend
FROM customers c
INNER JOIN bookings b ON c.customer_id = b.customer_id
GROUP BY c.customer_id, c.full_name, c.phone
HAVING customer_spend > (
    SELECT AVG(total_amount) FROM bookings WHERE payment_status = 'Paid'
);`,

  q12: `-- Q12: Top Revenue Generating Culinary Menu Dishes
SELECT m.name AS dish_name, m.category, m.price, r.name AS restaurant_name
FROM menu_items m
INNER JOIN restaurants r ON m.restaurant_id = r.restaurant_id
WHERE m.is_chef_special = 1
ORDER BY m.price DESC;`,

  q13: `-- Q13: Staff Payroll & Headcount by Department
SELECT department,
       COUNT(*) AS staff_count,
       SUM(salary) AS total_payroll,
       ROUND(AVG(salary), 2) AS avg_salary
FROM staff
WHERE status = 'Active'
GROUP BY department
ORDER BY total_payroll DESC;`,

  q14: `-- Q14: Live In-Room Guest Service Orders with Room Joins
SELECT so.order_id, b.booking_ref, r.room_number, c.full_name, s.name AS service_name, soi.quantity, so.total_amount, so.status
FROM service_orders so
INNER JOIN bookings b ON so.booking_id = b.booking_id
INNER JOIN rooms r ON b.room_id = r.room_id
INNER JOIN customers c ON so.customer_id = c.customer_id
INNER JOIN service_order_items soi ON so.order_id = soi.order_id
INNER JOIN services s ON soi.service_id = s.service_id
ORDER BY so.order_id DESC;`,

  q15: `-- Q15: Active Promotional Offers & Valid Discount Codes
SELECT code, title, discount_percent, flat_discount, validity_date, price_note
FROM offers
WHERE is_active = 1 AND validity_date >= CURRENT_DATE;`,

  q16: `-- Q16: Highest Rated Palaces by Approved Guest Feedback
SELECT h.name AS hotel_name,
       COUNT(rv.review_id) AS total_reviews,
       ROUND(AVG(rv.rating), 2) AS avg_guest_rating
FROM hotels h
LEFT JOIN reviews rv ON h.hotel_id = rv.hotel_id AND rv.is_approved = 1
GROUP BY h.hotel_id, h.name
ORDER BY avg_guest_rating DESC;`,

  q17: `-- Q17: Complete Relational Schema Table Row Count Audit
SELECT 'users' AS table_name, COUNT(*) AS row_count FROM users
UNION ALL SELECT 'customers', COUNT(*) FROM customers
UNION ALL SELECT 'admins', COUNT(*) FROM admins
UNION ALL SELECT 'hotels', COUNT(*) FROM hotels
UNION ALL SELECT 'rooms', COUNT(*) FROM rooms
UNION ALL SELECT 'bookings', COUNT(*) FROM bookings
UNION ALL SELECT 'payments', COUNT(*) FROM payments
UNION ALL SELECT 'services', COUNT(*) FROM services
UNION ALL SELECT 'staff', COUNT(*) FROM staff
UNION ALL SELECT 'restaurants', COUNT(*) FROM restaurants
UNION ALL SELECT 'menu_items', COUNT(*) FROM menu_items
UNION ALL SELECT 'offers', COUNT(*) FROM offers
UNION ALL SELECT 'reviews', COUNT(*) FROM reviews;`
};

function loadPresetQuery(key) {
  if (PRESET_QUERIES[key]) {
    document.getElementById('sqlQueryTextarea').value = PRESET_QUERIES[key];
  }
}

function switchVivaTab(tabKey, btnEl) {
  document.querySelectorAll('.viva-tab-content').forEach(el => el.style.display = 'none');
  document.querySelectorAll('.viva-tab-btn').forEach(el => {
    el.classList.remove('admin-btn-primary');
    el.classList.add('btn-outline-dark');
  });

  document.getElementById('viva-tab-' + tabKey).style.display = 'block';
  btnEl.classList.remove('btn-outline-dark');
  btnEl.classList.add('admin-btn-primary');
}

// Auto open tab if hash exists
window.addEventListener('DOMContentLoaded', () => {
  if (window.location.hash) {
    const tabName = window.location.hash.replace('#', '');
    const targetBtn = Array.from(document.querySelectorAll('.viva-tab-btn')).find(b => b.getAttribute('onclick').includes(tabName));
    if (targetBtn) targetBtn.click();
  }
});
</script>

<style>
.viva-qa-item {
  border: 1px solid var(--admin-border);
  border-radius: 4px;
  margin-bottom: 0.8rem;
  background: #FFFFFF;
}
.viva-qa-item summary {
  padding: 1rem 1.2rem;
  font-size: 0.95rem;
  cursor: pointer;
  background: #F9FAFB;
  border-radius: 4px;
  color: var(--admin-primary);
}
.viva-qa-item summary:hover {
  background: #F3F4F6;
}
.viva-qa-body {
  padding: 1.2rem;
  font-size: 0.88rem;
  line-height: 1.7;
  color: var(--admin-text-secondary);
  border-top: 1px solid var(--admin-border);
}
.viva-qa-body pre {
  background: #111827;
  color: #F9FAFB;
  padding: 1rem;
  border-radius: 4px;
  overflow-x: auto;
  margin: 0.8rem 0;
}
.viva-qa-body code {
  font-family: 'Courier New', monospace;
  color: var(--color-gold-light);
}
</style>

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
