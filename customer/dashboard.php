<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
if (isAdmin()) { header("Location: ../admin/dashboard.php"); exit; }

$userId = $_SESSION['user_id'];

$vehicleCount = $conn->query("SELECT COUNT(*) c FROM vehicles WHERE user_id = $userId")->fetch_assoc()['c'];
$bookingCount = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id = $userId")->fetch_assoc()['c'];
$pendingCount = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id = $userId AND status IN ('Pending','In Progress')")->fetch_assoc()['c'];
$completedCount = $conn->query("SELECT COUNT(*) c FROM bookings WHERE user_id = $userId AND status = 'Completed'")->fetch_assoc()['c'];

$recentBookings = $conn->query("
    SELECT b.*, br.name AS brand_name, m.name AS model_name, st.name AS service_name
    FROM bookings b
    JOIN vehicles v ON b.vehicle_id = v.id
    JOIN brands br ON v.brand_id = br.id
    JOIN models m ON v.model_id = m.id
    JOIN service_types st ON b.service_type_id = st.id
    WHERE b.user_id = $userId
    ORDER BY b.created_at DESC LIMIT 5
");

$pageTitle = "Dashboard";
include __DIR__ . '/../includes/header.php';
?>

<h3 class="mb-4">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?> 👋</h3>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card stat-card p-3">
      <div class="stat-value"><?php echo $vehicleCount; ?></div>
      <div class="stat-label">My Vehicles</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card p-3">
      <div class="stat-value"><?php echo $bookingCount; ?></div>
      <div class="stat-label">Total Bookings</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card p-3">
      <div class="stat-value"><?php echo $pendingCount; ?></div>
      <div class="stat-label">Active Services</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card p-3">
      <div class="stat-value"><?php echo $completedCount; ?></div>
      <div class="stat-label">Completed</div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Recent Bookings</h5>
  <a href="book_service.php" class="btn btn-accent btn-sm"><i class="bi bi-plus-circle me-1"></i>Book New Service</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>Vehicle</th>
          <th>Service</th>
          <th>Date</th>
          <th>Price</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($recentBookings->num_rows === 0): ?>
          <tr><td colspan="5" class="text-center text-muted py-4">No bookings yet. Book your first service!</td></tr>
        <?php endif; ?>
        <?php while ($row = $recentBookings->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['brand_name'] . ' ' . $row['model_name']); ?></td>
            <td><?php echo htmlspecialchars($row['service_name']); ?></td>
            <td><?php echo date('d M Y', strtotime($row['booking_date'])); ?></td>
            <td>₹<?php echo number_format($row['price'] + $row['extra_charge'], 2); ?></td>
            <td>
              <?php
                $badgeClass = [
                  'Pending' => 'badge-pending',
                  'In Progress' => 'badge-inprogress',
                  'Completed' => 'badge-completed',
                  'Cancelled' => 'badge-cancelled'
                ][$row['status']];
              ?>
              <span class="badge <?php echo $badgeClass; ?>"><?php echo $row['status']; ?></span>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
