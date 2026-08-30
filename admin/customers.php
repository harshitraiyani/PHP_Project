<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$customers = $conn->query("
    SELECT u.*,
        (SELECT COUNT(*) FROM vehicles v WHERE v.user_id = u.id) AS vehicle_count,
        (SELECT COUNT(*) FROM bookings b WHERE b.user_id = u.id) AS booking_count,
        (SELECT COALESCE(SUM(i.total),0) FROM bookings b JOIN invoices i ON i.booking_id = b.id WHERE b.user_id = u.id) AS total_spent
    FROM users u
    WHERE u.role = 'customer'
    ORDER BY u.created_at DESC
");

$pageTitle = "Customers";
include __DIR__ . '/../includes/header.php';
?>

<h3 class="mb-4">Customers</h3>

<div class="card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>Name</th>
          <th>Email</th>
          <th>Phone</th>
          <th>Vehicles</th>
          <th>Bookings</th>
          <th>Total Spent</th>
          <th>Joined</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($customers->num_rows === 0): ?>
          <tr><td colspan="7" class="text-center text-muted py-4">No customers yet.</td></tr>
        <?php endif; ?>
        <?php while ($c = $customers->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($c['name']); ?></td>
            <td><?php echo htmlspecialchars($c['email']); ?></td>
            <td><?php echo htmlspecialchars($c['phone']); ?></td>
            <td><span class="badge badge-inprogress"><?php echo $c['vehicle_count']; ?></span></td>
            <td><span class="badge badge-completed"><?php echo $c['booking_count']; ?></span></td>
            <td>₹<?php echo number_format($c['total_spent'], 2); ?></td>
            <td><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
