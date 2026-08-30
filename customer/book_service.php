<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
if (isAdmin()) { header("Location: ../admin/dashboard.php"); exit; }

$userId = $_SESSION['user_id'];

// Fetch this customer's vehicles (they must add a vehicle first)
$myVehicles = $conn->query("
    SELECT v.id, v.registration_no, v.brand_id, v.model_id, m.segment, br.name AS brand_name, m.name AS model_name
    FROM vehicles v
    JOIN brands br ON v.brand_id = br.id
    JOIN models m ON v.model_id = m.id
    WHERE v.user_id = $userId
    ORDER BY v.created_at DESC
");
$vehiclesArr = [];
while ($v = $myVehicles->fetch_assoc()) { $vehiclesArr[] = $v; }

$serviceTypes = $conn->query("SELECT * FROM service_types ORDER BY id");
$serviceArr = [];
while ($s = $serviceTypes->fetch_assoc()) { $serviceArr[] = $s; }

// pricing lookup: model_id -> base_price
$pricingRes = $conn->query("SELECT model_id, base_price FROM model_pricing");
$pricingMap = [];
while ($p = $pricingRes->fetch_assoc()) { $pricingMap[$p['model_id']] = $p['base_price']; }

// Fixed segment-based pricing for Battery / Tyre
$segmentRes = $conn->query("SELECT segment, battery_price, tyre_price FROM segment_pricing");
$segmentPricing = [];
while ($sp = $segmentRes->fetch_assoc()) { $segmentPricing[$sp['segment']] = $sp; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehicleId = intval($_POST['vehicle_id']);
    $serviceTypeId = intval($_POST['service_type_id']);
    $bookingDate = $_POST['booking_date'];
    $notes = sanitize($conn, $_POST['notes']);
    $price = floatval($_POST['price_hidden']);

    if ($price <= 0) {
        flash('booking', 'Invalid price calculated. Please reselect vehicle and service.', 'danger');
        header("Location: book_service.php");
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO bookings (user_id, vehicle_id, service_type_id, booking_date, price, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisds", $userId, $vehicleId, $serviceTypeId, $bookingDate, $price, $notes);

    if ($stmt->execute()) {
        flash('history', 'Service booked successfully! Track its status in Service History.', 'success');
        header("Location: history.php");
        exit;
    } else {
        flash('booking', 'Failed to book service. Try again.', 'danger');
    }
    header("Location: book_service.php");
    exit;
}

$pageTitle = "Book Service";
include __DIR__ . '/../includes/header.php';
?>

<h3 class="mb-4">Book a Service</h3>
<?php flash('booking'); ?>

<?php if (empty($vehiclesArr)): ?>
  <div class="card p-5 text-center text-muted">
    <i class="bi bi-exclamation-circle fs-1 mb-2"></i>
    <p class="mb-3">You haven't added any vehicle yet. Add a vehicle first to book a service.</p>
    <a href="my_vehicles.php" class="btn btn-primary mx-auto" style="width:fit-content;">Add Vehicle</a>
  </div>
<?php else: ?>

<div class="row">
  <div class="col-lg-7">
    <div class="card p-4">
      <form method="POST" id="bookingForm">
        <div class="mb-3">
          <label class="form-label">Select Your Vehicle</label>
          <select name="vehicle_id" id="vehicle_id" class="form-select" required>
            <option value="">-- Select Vehicle --</option>
            <?php foreach ($vehiclesArr as $v): ?>
              <option value="<?php echo $v['id']; ?>"
                      data-base-price="<?php echo $pricingMap[$v['model_id']] ?? 0; ?>"
                      data-segment="<?php echo htmlspecialchars($v['segment']); ?>">
                <?php echo htmlspecialchars($v['brand_name'] . ' ' . $v['model_name'] . ' (' . $v['registration_no'] . ')'); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Service Type</label>
          <select name="service_type_id" id="service_type_id" class="form-select" required>
            <option value="">-- Select Service --</option>
            <?php foreach ($serviceArr as $s): ?>
              <option value="<?php echo $s['id']; ?>" data-multiplier="<?php echo $s['base_multiplier']; ?>">
                <?php echo htmlspecialchars($s['name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
          <div class="form-text" id="serviceDesc"></div>
        </div>

        <div class="mb-3 d-none" id="tyreQtyWrap">
          <label class="form-label">Number of Tyres</label>
          <select class="form-select" id="tyre_qty">
            <option value="1">1 Tyre</option>
            <option value="2">2 Tyres</option>
            <option value="3">3 Tyres</option>
            <option value="4" selected>4 Tyres (Full Set)</option>
          </select>
        </div>

        <div class="mb-3">
          <label class="form-label">Preferred Date</label>
          <input type="date" name="booking_date" class="form-control" min="<?php echo date('Y-m-d'); ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label">Additional Notes (optional)</label>
          <textarea name="notes" class="form-control" rows="2" placeholder="Any specific issue or request..."></textarea>
        </div>

        <input type="hidden" name="price_hidden" id="price_hidden" value="0">
        <button type="submit" class="btn btn-accent w-100">Confirm Booking</button>
      </form>
    </div>
  </div>

  <div class="col-lg-5">
    <div class="price-box">
      <div class="mb-1">Estimated Price</div>
      <div class="amount" id="priceDisplay">₹0</div>
      <hr style="border-color: rgba(255,255,255,.3)">
      <small>Price is calculated automatically based on your vehicle model and selected service type.</small>
    </div>
  </div>
</div>

<script>
const serviceDescMap = <?php echo json_encode(array_column($serviceArr, 'description', 'id')); ?>;
const segmentPricing = <?php echo json_encode($segmentPricing); ?>;

document.getElementById('vehicle_id').addEventListener('change', calcPrice);
document.getElementById('service_type_id').addEventListener('change', function() {
  document.getElementById('serviceDesc').textContent = serviceDescMap[this.value] || '';

  const selectedText = this.selectedOptions[0] ? this.selectedOptions[0].textContent : '';
  const tyreQtyWrap = document.getElementById('tyreQtyWrap');
  if (selectedText.toLowerCase().includes('tyre')) {
    tyreQtyWrap.classList.remove('d-none');
  } else {
    tyreQtyWrap.classList.add('d-none');
  }
  calcPrice();
});

document.getElementById('tyre_qty').addEventListener('change', calcPrice);

function calcPrice() {
  const vehicleOpt = document.getElementById('vehicle_id').selectedOptions[0];
  const serviceOpt = document.getElementById('service_type_id').selectedOptions[0];

  if (!vehicleOpt || !vehicleOpt.dataset.basePrice || !serviceOpt || !serviceOpt.dataset.multiplier) {
    document.getElementById('priceDisplay').textContent = '₹0';
    document.getElementById('price_hidden').value = 0;
    return;
  }

  const segment = vehicleOpt.dataset.segment;
  const serviceName = serviceOpt.textContent.toLowerCase();
  let total = 0;

  if (serviceName.includes('battery')) {
    total = segmentPricing[segment] ? parseFloat(segmentPricing[segment].battery_price) : 0;
  } else if (serviceName.includes('tyre')) {
    const qty = parseInt(document.getElementById('tyre_qty').value);
    total = segmentPricing[segment] ? parseFloat(segmentPricing[segment].tyre_price) * qty : 0;
  } else {
    const base = parseFloat(vehicleOpt.dataset.basePrice);
    const mult = parseFloat(serviceOpt.dataset.multiplier);
    total = base * mult;
  }

  total = total.toFixed(2);
  document.getElementById('priceDisplay').textContent = '₹' + Number(total).toLocaleString('en-IN');
  document.getElementById('price_hidden').value = total;
}
</script> 

<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>
