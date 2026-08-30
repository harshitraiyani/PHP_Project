<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

$vehicles = $conn->query("
    SELECT v.*, u.name AS owner_name, u.phone, br.name AS brand_name, m.name AS model_name,
        (SELECT COUNT(*) FROM bookings b WHERE b.vehicle_id = v.id) AS service_count
    FROM vehicles v
    JOIN users u ON v.user_id = u.id
    JOIN brands br ON v.brand_id = br.id
    JOIN models m ON v.model_id = m.id
    ORDER BY v.created_at DESC
");

$pageTitle = "Vehicles";
include __DIR__ . '/../includes/header.php';
?>

<h3 class="mb-4">All Registered Vehicles</h3>

<div class="card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>Owner</th>
          <th>Phone</th>
          <th>Vehicle</th>
          <th>Reg No</th>
          <th>Year</th>
          <th>Services Taken</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($vehicles->num_rows === 0): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">No vehicles registered yet.</td></tr>
        <?php endif; ?>
        <?php while ($v = $vehicles->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($v['owner_name']); ?></td>
            <td><?php echo htmlspecialchars($v['phone']); ?></td>
            <td><?php echo htmlspecialchars($v['brand_name'] . ' ' . $v['model_name']); ?></td>
            <td><?php echo htmlspecialchars($v['registration_no']); ?></td>
            <td><?php echo htmlspecialchars($v['year']); ?></td>
            <td><span class="badge badge-completed"><?php echo $v['service_count']; ?></span></td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
