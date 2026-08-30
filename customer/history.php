<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
if (isAdmin()) { header("Location: ../admin/dashboard.php"); exit; }

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_booking'])) {
    $bookingId = intval($_POST['booking_id']);
    $stmt = $conn->prepare("UPDATE bookings SET status = 'Cancelled' WHERE id = ? AND user_id = ? AND status = 'Pending'");
    $stmt->bind_param("ii", $bookingId, $userId);
    $stmt->execute();
    if ($stmt->affected_rows > 0) {
        flash('history', 'Booking cancelled successfully.', 'success');
    } else {
        flash('history', 'This booking can no longer be cancelled.', 'danger');
    }
    header("Location: history.php");
    exit;
}

$bookings = $conn->query("
    SELECT b.*, br.name AS brand_name, m.name AS model_name, v.registration_no, st.name AS service_name,
           i.id AS invoice_id
    FROM bookings b
    JOIN vehicles v ON b.vehicle_id = v.id
    JOIN brands br ON v.brand_id = br.id
    JOIN models m ON v.model_id = m.id
    JOIN service_types st ON b.service_type_id = st.id
    LEFT JOIN invoices i ON i.booking_id = b.id
    WHERE b.user_id = $userId
    ORDER BY b.created_at DESC
");

$pageTitle = "Service History";
include __DIR__ . '/../includes/header.php';
?>

<h3 class="mb-4">Service History & Tracking</h3>
<?php flash('history'); ?>

<?php if ($bookings->num_rows === 0): ?>
  <div class="card p-5 text-center text-muted">
    <i class="bi bi-clock-history fs-1 mb-2"></i>
    <p class="mb-0">No service history yet.</p>
  </div>
<?php endif; ?>

<?php while ($b = $bookings->fetch_assoc()):
    $statusSteps = ['Pending', 'In Progress', 'Completed'];
    $currentIndex = array_search($b['status'], $statusSteps);
    $isCancelled = $b['status'] === 'Cancelled';
?>
  <div class="card p-4 mb-3">
    <div class="row align-items-center">
      <div class="col-md-8">
        <h6 class="mb-1"><?php echo htmlspecialchars($b['brand_name'] . ' ' . $b['model_name']); ?>
          <small class="text-muted">(<?php echo htmlspecialchars($b['registration_no']); ?>)</small>
        </h6>
        <p class="mb-1 text-muted"><?php echo htmlspecialchars($b['service_name']); ?> &middot; Booked for <?php echo date('d M Y', strtotime($b['booking_date'])); ?></p>
        <?php if ($b['notes']): ?><p class="mb-1 small text-muted">Note: <?php echo htmlspecialchars($b['notes']); ?></p><?php endif; ?>
      </div>
      <div class="col-md-4 text-md-end">
        <div class="fs-5 fw-bold" style="color: var(--vsp-primary);">₹<?php echo number_format($b['price'] + $b['extra_charge'], 2); ?></div>
        <?php if ($b['extra_charge'] > 0): ?>
          <div class="small text-muted">
            includes ₹<?php echo number_format($b['extra_charge'], 2); ?> extra
            <?php if ($b['extra_charge_note']): ?> — <?php echo htmlspecialchars($b['extra_charge_note']); ?><?php endif; ?>
          </div>
        <?php endif; ?>
        <?php if ($b['status'] === 'Completed' && $b['invoice_id']): ?>
          <a href="download_invoice.php?id=<?php echo $b['invoice_id']; ?>" class="btn btn-sm btn-accent mt-2">
            <i class="bi bi-file-earmark-pdf me-1"></i>Download Invoice
          </a>
        <?php endif; ?>
        <?php if ($b['status'] === 'Pending'): ?>
          <form method="POST" onsubmit="return confirm('Cancel this booking?');" class="mt-2">
            <input type="hidden" name="booking_id" value="<?php echo $b['id']; ?>">
            <button type="submit" name="cancel_booking" class="btn btn-sm btn-outline-danger">
              <i class="bi bi-x-circle me-1"></i>Cancel Booking
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!$isCancelled): ?>
    <div class="tracker mt-3">
      <?php foreach ($statusSteps as $i => $step): ?>
        <div class="step <?php echo $i < $currentIndex ? 'done' : ($i === $currentIndex ? 'active' : ''); ?>">
          <div class="dot"><span><?php echo $i < $currentIndex ? '✓' : ($i + 1); ?></span></div>
          <small><?php echo $step; ?></small>
        </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
      <div class="mt-3"><span class="badge badge-cancelled">Cancelled</span></div>
    <?php endif; ?>
  </div>
<?php endwhile; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
