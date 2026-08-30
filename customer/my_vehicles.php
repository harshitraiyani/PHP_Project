<?php
require_once __DIR__ . '/../includes/functions.php';
requireLogin();
if (isAdmin()) { header("Location: ../admin/dashboard.php"); exit; }

$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $brandId = intval($_POST['brand_id']);
    $modelId = intval($_POST['model_id']);
    $regNo = sanitize($conn, $_POST['registration_no']);
    $year = intval($_POST['year']);

    $stmt = $conn->prepare("INSERT INTO vehicles (user_id, brand_id, model_id, registration_no, year) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("iiisi", $userId, $brandId, $modelId, $regNo, $year);
    if ($stmt->execute()) {
        flash('vehicles', 'Vehicle added successfully!', 'success');
    } else {
        flash('vehicles', 'Failed to add vehicle.', 'danger');
    }
    header("Location: my_vehicles.php");
    exit;
}

$brands = $conn->query("SELECT * FROM brands ORDER BY name");
$models = $conn->query("SELECT * FROM models ORDER BY name");

$myVehicles = $conn->query("
    SELECT v.*, br.name AS brand_name, m.name AS model_name
    FROM vehicles v
    JOIN brands br ON v.brand_id = br.id
    JOIN models m ON v.model_id = m.id
    WHERE v.user_id = $userId
    ORDER BY v.created_at DESC
");

$pageTitle = "My Vehicles";
include __DIR__ . '/../includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <h3 class="mb-0">My Vehicles</h3>
  <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#addVehicleModal">
    <i class="bi bi-plus-circle me-1"></i> Add Vehicle
  </button>
</div>

<?php flash('vehicles'); ?>

<div class="row g-3">
  <?php if ($myVehicles->num_rows === 0): ?>
    <div class="col-12">
      <div class="card p-5 text-center text-muted">
        <i class="bi bi-car-front fs-1 mb-2"></i>
        <p class="mb-0">No vehicles added yet. Add your first vehicle to book a service.</p>
      </div>
    </div>
  <?php endif; ?>
  <?php while ($v = $myVehicles->fetch_assoc()): ?>
    <div class="col-md-4">
      <div class="card p-3 h-100">
        <div class="d-flex align-items-center mb-2">
          <i class="bi bi-car-front-fill fs-2 me-2" style="color: var(--vsp-primary);"></i>
          <div>
            <h6 class="mb-0"><?php echo htmlspecialchars($v['brand_name'] . ' ' . $v['model_name']); ?></h6>
            <small class="text-muted"><?php echo htmlspecialchars($v['year']); ?></small>
          </div>
        </div>
        <p class="mb-0"><strong>Reg No:</strong> <?php echo htmlspecialchars($v['registration_no']); ?></p>
      </div>
    </div>
  <?php endwhile; ?>
</div>

<!-- Add Vehicle Modal -->
<div class="modal fade" id="addVehicleModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add New Vehicle</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label">Brand</label>
            <select name="brand_id" id="mv_brand" class="form-select" required>
              <option value="">-- Select Brand --</option>
              <?php while ($b = $brands->fetch_assoc()): ?>
                <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Model</label>
            <select name="model_id" id="mv_model" class="form-select" required>
              <option value="">-- Select Brand First --</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Registration Number</label>
            <input type="text" name="registration_no" class="form-control" placeholder="e.g. GJ01AB1234" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Year</label>
            <input type="number" name="year" class="form-control" min="1990" max="<?php echo date('Y'); ?>" value="<?php echo date('Y'); ?>" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Add Vehicle</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
const allModels = <?php
  $modelsList = $conn->query("SELECT * FROM models ORDER BY name");
  $arr = [];
  while ($m = $modelsList->fetch_assoc()) { $arr[] = $m; }
  echo json_encode($arr);
?>;

document.getElementById('mv_brand').addEventListener('change', function () {
  const modelSelect = document.getElementById('mv_model');
  modelSelect.innerHTML = '<option value="">-- Select Model --</option>';
  allModels.filter(m => m.brand_id == this.value).forEach(m => {
    const opt = document.createElement('option');
    opt.value = m.id;
    opt.textContent = m.name;
    modelSelect.appendChild(opt);
  });
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>
