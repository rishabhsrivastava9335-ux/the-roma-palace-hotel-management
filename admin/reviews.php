<?php
/**
 * THE ROMA PALACE — Guest Reviews Moderation
 * BTech CSE DBMS Mini Project
 */
require_once __DIR__ . '/includes/admin-header.php';

// Handle Moderation Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $revId = (int)$_POST['review_id'];

    if ($action === 'toggle_approval') {
        db_execute("UPDATE reviews SET is_approved = NOT is_approved WHERE review_id = ?", [$revId]);
        $_SESSION['flash_success'] = "Review moderation status updated.";
        header("Location: reviews.php");
        exit;
    }

    if ($action === 'delete_review') {
        db_execute("DELETE FROM reviews WHERE review_id = ?", [$revId]);
        $_SESSION['flash_success'] = "Review removed.";
        header("Location: reviews.php");
        exit;
    }
}

$reviews = db_fetch_all("SELECT rv.*, c.full_name, c.city, h.name AS hotel_name 
                         FROM reviews rv 
                         INNER JOIN customers c ON rv.customer_id = c.customer_id 
                         INNER JOIN hotels h ON rv.hotel_id = h.hotel_id 
                         ORDER BY rv.review_id DESC");

$pageHeading = 'Guest Reviews & Ratings Moderation';
?>

<div class="admin-card">
  <div class="admin-card-header">
    <h3 class="admin-card-title"><i class="fa-solid fa-star text-gold"></i> Guest Testimonials (<?php echo count($reviews); ?> Reviews)</h3>
    <div class="search-input-box">
      <i class="fa-solid fa-magnifying-glass"></i>
      <input type="text" placeholder="Search reviews, guest, property..." data-table-search="reviewsTable">
    </div>
  </div>

  <div class="admin-table-responsive">
    <table class="admin-table" id="reviewsTable">
      <thead>
        <tr>
          <th>Guest Details</th>
          <th>Palace Visited</th>
          <th>Rating</th>
          <th>Headline & Review Comments</th>
          <th>Date</th>
          <th>Status</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($reviews as $rev): ?>
          <tr>
            <td>
              <strong><?php echo htmlspecialchars($rev['full_name']); ?></strong><br>
              <small style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($rev['city']); ?></small>
            </td>
            <td><?php echo htmlspecialchars($rev['hotel_name']); ?></td>
            <td>
              <div style="color: #F59E0B; white-space: nowrap;">
                <?php for ($i = 0; $i < $rev['rating']; $i++): ?>
                  <i class="fa-solid fa-star"></i>
                <?php endfor; ?>
              </div>
            </td>
            <td>
              <strong style="color: var(--admin-primary);">“<?php echo htmlspecialchars($rev['review_title']); ?>”</strong><br>
              <small style="color: var(--admin-text-muted);"><?php echo htmlspecialchars($rev['comments']); ?></small>
            </td>
            <td><small><?php echo htmlspecialchars($rev['stay_date'] ?? date('M Y', strtotime($rev['created_at']))); ?></small></td>
            <td>
              <span class="badge badge-<?php echo ($rev['is_approved']) ? 'success' : 'warning'; ?>">
                <?php echo ($rev['is_approved']) ? 'PUBLISHED' : 'PENDING'; ?>
              </span>
            </td>
            <td>
              <div class="action-btn-group">
                <form method="POST" action="reviews.php" style="display: inline;">
                  <input type="hidden" name="action" value="toggle_approval">
                  <input type="hidden" name="review_id" value="<?php echo $rev['review_id']; ?>">
                  <button type="submit" class="btn-action-icon" title="Toggle Approval" style="color: <?php echo ($rev['is_approved']) ? '#EAB308' : '#10B981'; ?>;">
                    <i class="fa-solid fa-<?php echo ($rev['is_approved']) ? 'eye-slash' : 'check'; ?>"></i>
                  </button>
                </form>

                <form method="POST" action="reviews.php" onsubmit="return confirm('Delete review?');" style="display: inline;">
                  <input type="hidden" name="action" value="delete_review">
                  <input type="hidden" name="review_id" value="<?php echo $rev['review_id']; ?>">
                  <button type="submit" class="btn-action-icon" style="color: #EF4444;" title="Delete Review">
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

<?php require_once __DIR__ . '/includes/admin-footer.php'; ?>
