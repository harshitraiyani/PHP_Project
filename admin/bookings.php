<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Update booking status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $bookingId = intval($_POST['booking_id']);
    $newStatus = $_POST['status'];
    $extraCharge = floatval($_POST['extra_charge'] ?? 0);
    $extraChargeNote = sanitize($conn, $_POST['extra_charge_note'] ?? '');
    $allowed = ['Pending', 'In Progress', 'Completed', 'Cancelled'];

    if (in_array($newStatus, $allowed)) {
        $stmt = $conn->prepare("UPDATE bookings SET status = ?, extra_charge = ?, extra_charge_note = ? WHERE id = ?");
        $stmt->bind_param("sdsi", $newStatus, $extraCharge, $extraChargeNote, $bookingId);
        $stmt->execute();

        // Auto-generate invoice when marked Completed (if not already generated)
        if ($newStatus === 'Completed') {
            $check = $conn->prepare("SELECT id FROM invoices WHERE booking_id = ?");
            $check->bind_param("i", $bookingId);
            $check->execute();
            if ($check->get_result()->num_rows === 0) {
                $bookingRes = $conn->prepare("SELECT price, extra_charge FROM bookings WHERE id = ?");
                $bookingRes->bind_param("i", $bookingId);
                $bookingRes->execute();
                $bookingRow = $bookingRes->get_result()->fetch_assoc();
                $subtotal = $bookingRow['price'] + $bookingRow['extra_charge'];

                $tax = round($subtotal * 0.18, 2); // 18% GST example
                $total = $subtotal + $tax;
                $invoiceNo = generateInvoiceNo();

                $ins = $conn->prepare("INSERT INTO invoices (booking_id, invoice_no, amount, tax, total, payment_status) VALUES (?, ?, ?, ?, ?, 'Paid')");
                $ins->bind_param("isddd", $bookingId, $invoiceNo, $subtotal, $tax, $total);
                $ins->execute();
            }
        }
        flash('bookings', 'Booking status updated.', 'success');
    }
    header("Location: bookings.php");
    exit;
}

$statusFilter = $_GET['status'] ?? '';
$query = "
    SELECT b.*, u.name AS customer_name, u.email, br.name AS brand_name, m.name AS model_name,
           v.registration_no, st.name AS service_name, i.id AS invoice_id
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN vehicles v ON b.vehicle_id = v.id
    JOIN brands br ON v.brand_id = br.id
    JOIN models m ON v.model_id = m.id
    JOIN service_types st ON b.service_type_id = st.id
    LEFT JOIN invoices i ON i.booking_id = b.id
";
if ($statusFilter) {
    $query .= " WHERE b.status = '" . $conn->real_escape_string($statusFilter) . "'";
}
$query .= " ORDER BY b.created_at DESC";
$bookings = $conn->query($query);

$pageTitle = "Manage Bookings";
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h3 class="mb-0">Manage Bookings</h3>
  <div class="btn-group">
    <a href="bookings.php" class="btn btn-sm btn-outline-primary <?php echo !$statusFilter ? 'active' : ''; ?>">All</a>
    <a href="bookings.php?status=Pending" class="btn btn-sm btn-outline-primary <?php echo $statusFilter === 'Pending' ? 'active' : ''; ?>">Pending</a>
    <a href="bookings.php?status=In Progress" class="btn btn-sm btn-outline-primary <?php echo $statusFilter === 'In Progress' ? 'active' : ''; ?>">In Progress</a>
    <a href="bookings.php?status=Completed" class="btn btn-sm btn-outline-primary <?php echo $statusFilter === 'Completed' ? 'active' : ''; ?>">Completed</a>
  </div>
</div>

<?php flash('bookings'); ?>

<div class="card">
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>Customer</th>
          <th>Vehicle</th>
          <th>Service</th>
          <th>Date</th>
          <th>Note</th>
          <th>Price</th>
          <th>Status</th>
          <th>Invoice</th>
          <th>Update Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if ($bookings->num_rows === 0): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">No bookings found.</td></tr>
        <?php endif; ?>
        <?php while ($row = $bookings->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['customer_name']); ?><br><small class="text-muted"><?php echo htmlspecialchars($row['email']); ?></small></td>
            <td><?php echo htmlspecialchars($row['brand_name'] . ' ' . $row['model_name']); ?><br><small class="text-muted"><?php echo htmlspecialchars($row['registration_no']); ?></small></td>
            <td><?php echo htmlspecialchars($row['service_name']); ?></td>
            <td><?php echo date('d M Y', strtotime($row['booking_date'])); ?></td>
            <td style="max-width:180px;">
              <?php if ($row['notes']): ?>
                <span class="small"><?php echo htmlspecialchars($row['notes']); ?></span>
              <?php else: ?>
                <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
            <td>
              ₹<?php echo number_format($row['price'], 2); ?>
              <?php if ($row['extra_charge'] > 0): ?>
                <br><small class="text-muted">+ ₹<?php echo number_format($row['extra_charge'], 2); ?> extra</small>
              <?php endif; ?>
            </td>
            <td>
              <?php
                $badgeClass = [
                  'Pending' => 'badge-pending', 'In Progress' => 'badge-inprogress',
                  'Completed' => 'badge-completed', 'Cancelled' => 'badge-cancelled'
                ][$row['status']];
              ?>
              <span class="badge <?php echo $badgeClass; ?>"><?php echo $row['status']; ?></span>
            </td>
            <td>
              <?php if ($row['invoice_id']): ?>
                <a href="../customer/download_invoice.php?id=<?php echo $row['invoice_id']; ?>" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-file-earmark-pdf"></i>
                </a>
              <?php else: ?>
                <span class="text-muted small">—</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="POST" class="d-flex flex-column gap-1">
                <input type="hidden" name="booking_id" value="<?php echo $row['id']; ?>">
                <select name="status" class="form-select form-select-sm">
                  <?php foreach (['Pending','In Progress','Completed','Cancelled'] as $s): ?>
                    <option value="<?php echo $s; ?>" <?php echo $row['status'] === $s ? 'selected' : ''; ?>><?php echo $s; ?></option>
                  <?php endforeach; ?>
                </select>
                <input type="number" step="0.01" min="0" name="extra_charge" class="form-control form-control-sm"
                       placeholder="Extra charge (₹)" value="<?php echo $row['extra_charge'] > 0 ? $row['extra_charge'] : ''; ?>">
                <input type="text" name="extra_charge_note" class="form-control form-control-sm"
                       placeholder="Reason (e.g. headlight lamp)" value="<?php echo htmlspecialchars($row['extra_charge_note'] ?? ''); ?>">
                <button type="submit" name="update_status" class="btn btn-sm btn-primary mt-1">Update</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
