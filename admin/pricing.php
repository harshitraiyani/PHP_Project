<?php
require_once __DIR__ . '/../includes/functions.php';
requireAdmin();

// Update segment (battery/tyre) pricing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_segment'])) {
    $segment = sanitize($conn, $_POST['segment']);
    $batteryPrice = floatval($_POST['battery_price']);
    $tyrePrice = floatval($_POST['tyre_price']);

    $stmt = $conn->prepare("UPDATE segment_pricing SET battery_price=?, tyre_price=? WHERE segment=?");
    $stmt->bind_param("dds", $batteryPrice, $tyrePrice, $segment);
    $stmt->execute();
    flash('pricing', "Pricing updated for $segment.", 'success');
    header("Location: pricing.php");
    exit;
}

// Update (or create) a specific model's base price
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_model_price'])) {
    $modelId = intval($_POST['model_id']);
    $basePrice = floatval($_POST['base_price']);
    $searchTermPost = trim($_POST['search_term'] ?? '');

    $check = $conn->prepare("SELECT id FROM model_pricing WHERE model_id = ?");
    $check->bind_param("i", $modelId);
    $check->execute();

    if ($check->get_result()->num_rows > 0) {
        $stmt = $conn->prepare("UPDATE model_pricing SET base_price=? WHERE model_id=?");
        $stmt->bind_param("di", $basePrice, $modelId);
    } else {
        $stmt = $conn->prepare("INSERT INTO model_pricing (model_id, base_price) VALUES (?, ?)");
        $stmt->bind_param("id", $modelId, $basePrice);
    }
    $stmt->execute();
    flash('pricing', 'Model price updated.', 'success');
    header("Location: pricing.php" . ($searchTermPost !== '' ? '?search=' . urlencode($searchTermPost) : ''));
    exit;
}

$segments = $conn->query("SELECT * FROM segment_pricing ORDER BY FIELD(segment,'Hatchback','Sedan','SUV','Luxury')");

$searchTerm = trim($_GET['search'] ?? '');
$modelsResult = null;
if ($searchTerm !== '') {
    $likeTerm = '%' . $conn->real_escape_string($searchTerm) . '%';
    $modelsResult = $conn->query("
        SELECT m.id, m.name, m.segment, br.name AS brand_name, COALESCE(mp.base_price, 0) AS base_price
        FROM models m
        JOIN brands br ON m.brand_id = br.id
        LEFT JOIN model_pricing mp ON mp.model_id = m.id
        WHERE m.name LIKE '$likeTerm' OR br.name LIKE '$likeTerm'
        ORDER BY br.name, m.name
        LIMIT 50
    ");
}

$pageTitle = "Manage Pricing";
include __DIR__ . '/../includes/header.php';
?>

<h3 class="mb-4">Manage Pricing</h3>
<?php flash('pricing'); ?>

<div class="card p-4 mb-4">
  <h5 class="mb-3">Battery & Tyre Fixed Pricing (by Segment)</h5>
  <div class="table-responsive">
    <table class="table align-middle mb-0">
      <thead>
        <tr>
          <th>Segment</th>
          <th>Battery Price (₹)</th>
          <th>Tyre Price — per tyre (₹)</th>
          <th>Update</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($seg = $segments->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($seg['segment']); ?></td>
            <td colspan="3">
              <form method="POST" class="d-flex gap-2 align-items-center">
                <input type="hidden" name="segment" value="<?php echo htmlspecialchars($seg['segment']); ?>">
                <input type="number" step="0.01" min="0" name="battery_price" class="form-control form-control-sm" value="<?php echo $seg['battery_price']; ?>" placeholder="Battery Price" style="max-width:160px;">
                <input type="number" step="0.01" min="0" name="tyre_price" class="form-control form-control-sm" value="<?php echo $seg['tyre_price']; ?>" placeholder="Tyre Price" style="max-width:160px;">
                <button type="submit" name="update_segment" class="btn btn-sm btn-primary">Save</button>
              </form>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="card p-4">
  <h5 class="mb-3">Model Base Price (used for Basic / Standard / Premium / Denting-Painting)</h5>
  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-8">
      <input type="text" name="search" class="form-control" placeholder="Search by brand or model name (e.g. Swift, Hyundai)" value="<?php echo htmlspecialchars($searchTerm); ?>">
    </div>
    <div class="col-md-4">
      <button type="submit" class="btn btn-accent w-100">Search</button>
    </div>
  </form>

  <?php if ($modelsResult): ?>
    <?php if ($modelsResult->num_rows === 0): ?>
      <p class="text-muted">No models found matching "<?php echo htmlspecialchars($searchTerm); ?>".</p>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle mb-0">
          <thead>
            <tr>
              <th>Brand</th>
              <th>Model</th>
              <th>Segment</th>
              <th>Base Price (₹)</th>
              <th>Update</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($m = $modelsResult->fetch_assoc()): ?>
              <tr>
                <td><?php echo htmlspecialchars($m['brand_name']); ?></td>
                <td><?php echo htmlspecialchars($m['name']); ?></td>
                <td><?php echo htmlspecialchars($m['segment']); ?></td>
                <td colspan="2">
                  <form method="POST" class="d-flex gap-2 align-items-center">
                    <input type="hidden" name="model_id" value="<?php echo $m['id']; ?>">
                    <input type="hidden" name="search_term" value="<?php echo htmlspecialchars($searchTerm); ?>">
                    <input type="number" step="0.01" min="0" name="base_price" class="form-control form-control-sm" value="<?php echo $m['base_price']; ?>" style="max-width:160px;">
                    <button type="submit" name="update_model_price" class="btn btn-sm btn-primary">Save</button>
                  </form>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <p class="text-muted">Search for a brand or model above to edit its base price.</p>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
