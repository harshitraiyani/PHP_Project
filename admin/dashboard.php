<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$totalCustomers = $conn->query("SELECT COUNT(*) c FROM users WHERE role='customer'")->fetch_assoc()['c'];
$totalVehicles = $conn->query("SELECT COUNT(*) c FROM vehicles")->fetch_assoc()['c'];
$totalBookings = $conn->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()['c'];
$totalIncome = $conn->query("SELECT COALESCE(SUM(total),0) s FROM invoices WHERE payment_status='Paid'")->fetch_assoc()['s'];
$pendingBookings = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='Pending'")->fetch_assoc()['c'];
$inProgressBookings = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='In Progress'")->fetch_assoc()['c'];
$completedBookings = $conn->query("SELECT COUNT(*) c FROM bookings WHERE status='Completed'")->fetch_assoc()['c'];

// Last 30 days data for charts
$chartLabels = [];
$chartBookings = [];
$chartRevenue = [];

for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('d M', strtotime($date));

    $bCount = $conn->query("SELECT COUNT(*) c FROM bookings WHERE DATE(created_at) = '$date'")->fetch_assoc()['c'];
    $chartBookings[] = (int) $bCount;

    $rev = $conn->query("SELECT COALESCE(SUM(total),0) s FROM invoices WHERE DATE(created_at) = '$date'")->fetch_assoc()['s'];
    $chartRevenue[] = (float) $rev;
}

$recentBookings = $conn->query("
    SELECT b.*, u.name AS customer_name, br.name AS brand_name, m.name AS model_name, st.name AS service_name
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN vehicles v ON b.vehicle_id = v.id
    JOIN brands br ON v.brand_id = br.id
    JOIN models m ON v.model_id = m.id
    JOIN service_types st ON b.service_type_id = st.id
    ORDER BY b.created_at DESC LIMIT 8
");

$pageTitle = "Admin Dashboard";
include __DIR__ . '/../includes/header.php';
?>

<h3 class="mb-4">Admin Dashboard</h3>

<div class="row g-3 mb-4">
  <div class="col-md-3">
    <div class="card stat-card p-3">
      <div class="stat-value"><?php echo $totalCustomers; ?></div>
      <div class="stat-label">Total Customers</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card p-3">
      <div class="stat-value"><?php echo $totalVehicles; ?></div>
      <div class="stat-label">Total Vehicles</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card p-3">
      <div class="stat-value"><?php echo $totalBookings; ?></div>
      <div class="stat-label">Total Bookings</div>
    </div>
  </div>
  <div class="col-md-3">
    <div class="card stat-card p-3">
      <div class="stat-value">₹<?php echo number_format($totalIncome, 0); ?></div>
      <div class="stat-label">Total Income</div>
    </div>
  </div>
</div>

<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card p-3 text-center">
      <span class="badge badge-pending mb-2 mx-auto" style="width:fit-content;">Pending</span>
      <div class="fs-4 fw-bold"><?php echo $pendingBookings; ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3 text-center">
      <span class="badge badge-inprogress mb-2 mx-auto" style="width:fit-content;">In Progress</span>
      <div class="fs-4 fw-bold"><?php echo $inProgressBookings; ?></div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card p-3 text-center">
      <span class="badge badge-completed mb-2 mx-auto" style="width:fit-content;">Completed</span>
      <div class="fs-4 fw-bold"><?php echo $completedBookings; ?></div>
    </div>
  </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h5 class="mb-0">Recent Bookings</h5>
  <a href="bookings.php" class="btn btn-sm btn-primary">Manage All Bookings</a>
</div>

<div class="card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Vehicle</th>
          <th>Service</th>
          <th>Date</th>
          <th>Price</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $recentBookings->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['customer_name']); ?></td>
            <td><?php echo htmlspecialchars($row['brand_name'] . ' ' . $row['model_name']); ?></td>
            <td><?php echo htmlspecialchars($row['service_name']); ?></td>
            <td><?php echo date('d M Y', strtotime($row['booking_date'])); ?></td>
            <td>₹<?php echo number_format($row['price'] + $row['extra_charge'], 2); ?></td>
            <td>
              <?php
                $badgeClass = [
                  'Pending' => 'badge-pending', 'In Progress' => 'badge-inprogress',
                  'Completed' => 'badge-completed', 'Cancelled' => 'badge-cancelled'
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

<div class="row g-3 mb-4">
  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="mb-3">Bookings — Last 30 Days</h6>
      <canvas id="bookingsChart" height="180"></canvas>
    </div>
  </div>
  <div class="col-md-6">
    <div class="card p-3">
      <h6 class="mb-3">Revenue — Last 30 Days</h6>
      <canvas id="revenueChart" height="180"></canvas>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
const chartLabels = <?php echo json_encode($chartLabels); ?>;
const chartBookings = <?php echo json_encode($chartBookings); ?>;
const chartRevenue = <?php echo json_encode($chartRevenue); ?>;

new Chart(document.getElementById('bookingsChart'), {
  type: 'line',
  data: {
    labels: chartLabels,
    datasets: [{
      label: 'Bookings',
      data: chartBookings,
      borderColor: '#14877C',
      backgroundColor: 'rgba(20,135,124,0.15)',
      tension: 0.3,
      fill: true
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
  }
});

new Chart(document.getElementById('revenueChart'), {
  type: 'bar',
  data: {
    labels: chartLabels,
    datasets: [{
      label: 'Revenue (₹)',
      data: chartRevenue,
      backgroundColor: '#FF6B54'
    }]
  },
  options: {
    plugins: { legend: { display: false } },
    scales: { y: { beginAtZero: true } }
  }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
