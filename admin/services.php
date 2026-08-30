<?php
require_once __DIR__ . '/../config/functions.php';
requireAdmin();

// Add new service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_service'])) {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $price = (float)$_POST['base_price'];
    if ($name && $price > 0) {
        $stmt = mysqli_prepare($conn, "INSERT INTO services (name, description, base_price) VALUES (?,?,?)");
        mysqli_stmt_bind_param($stmt, 'ssd', $name, $desc, $price);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
        setFlash('success', 'Service added.');
    }
    redirect('/admin/services.php');
}

// Update base price
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_service'])) {
    $id = (int)$_POST['service_id'];
    $price = (float)$_POST['base_price'];
    $stmt = mysqli_prepare($conn, "UPDATE services SET base_price=? WHERE id=?");
    mysqli_stmt_bind_param($stmt, 'di', $price, $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    setFlash('success', 'Base price updated.');
    redirect('/admin/services.php');
}

if (isset($_GET['toggle_active'])) {
    $id = (int)$_GET['toggle_active'];
    mysqli_query($conn, "UPDATE services SET is_active = 1 - is_active WHERE id=$id");
    redirect('/admin/services.php');
}

$services = mysqli_query($conn, "SELECT * FROM services ORDER BY name");

// Sample price preview across a few representative vehicle segments
$sampleVehicles = mysqli_query($conn, "
  SELECT company, model, segment, price_multiplier FROM vehicle_models
  WHERE (company='Maruti Suzuki' AND model='Alto')
     OR (company='Honda' AND model='City')
     OR (company='Tata Motors' AND model='Nexon')
     OR (company='BMW' AND model='5 Series')
");
$samples = mysqli_fetch_all($sampleVehicles, MYSQLI_ASSOC);

$pageTitle = 'Services & Pricing';
include __DIR__ . '/../includes/header.php';
?>
<div class="app-shell">
  <?php include __DIR__ . '/../includes/sidebar_admin.php'; ?>
  <div class="main-content">
    <?php include __DIR__ . '/../includes/topbar.php'; ?>
    <?php include __DIR__ . '/../includes/flash.php'; ?>
    <div class="page-body">
      <div class="alert alert-info">
        <i class="bi bi-info-circle"></i>
        Every car model has its own <strong>price multiplier</strong> (set in the vehicle catalogue —
        <code>vehicle_models</code> table). A service's final price for a customer =
        <strong>Base Price × their vehicle's multiplier</strong>. This is why an Alto and a BMW pay
        different amounts for the same "Basic Service".
      </div>
      <div class="row g-4">
        <div class="col-lg-7">
          <div class="section-title">Services &amp; Base Pricing</div>
          <div class="section-sub">Set the base price once — every vehicle automatically gets its own scaled price.</div>
          <div class="card p-3">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead><tr><th>Service</th><th>Base Price (₹)</th><th>Active</th></tr></thead>
                <tbody>
                <?php while ($s = mysqli_fetch_assoc($services)): ?>
                  <tr>
                    <td><?php echo e($s['name']); ?><br><small class="text-muted"><?php echo e($s['description']); ?></small></td>
                    <td style="width:160px;">
                      <form method="POST" class="d-flex gap-1">
                        <input type="hidden" name="service_id" value="<?php echo $s['id']; ?>">
                        <input type="number" step="0.01" name="base_price" value="<?php echo e($s['base_price']); ?>" class="form-control form-control-sm">
                        <button type="submit" name="update_service" value="1" class="btn btn-sm btn-outline-secondary"><i class="bi bi-check-lg"></i></button>
                      </form>
                    </td>
                    <td>
                      <a href="/admin/services.php?toggle_active=<?php echo $s['id']; ?>" class="badge bg-<?php echo $s['is_active']?'success':'secondary'; ?> text-decoration-none">
                        <?php echo $s['is_active'] ? 'Active' : 'Inactive'; ?>
                      </a>
                    </td>
                  </tr>
                <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="section-title mt-4">Price Preview by Vehicle</div>
          <div class="section-sub">Example: how "Basic Service" price differs across cars.</div>
          <div class="card p-3">
            <div class="table-responsive">
              <table class="table align-middle mb-0">
                <thead><tr><th>Vehicle</th><th>Segment</th><th>Multiplier</th><th>Basic Service Price</th></tr></thead>
                <tbody>
                <?php
                $basicPrice = mysqli_fetch_assoc(mysqli_query($conn, "SELECT base_price FROM services WHERE name='Basic Service' LIMIT 1"))['base_price'] ?? 0;
                foreach ($samples as $sv):
                    $p = round($basicPrice * $sv['price_multiplier'] / 10) * 10;
                ?>
                  <tr>
                    <td><?php echo e($sv['company'].' '.$sv['model']); ?></td>
                    <td><span class="badge text-bg-light border"><?php echo e($sv['segment']); ?></span></td>
                    <td>×<?php echo e($sv['price_multiplier']); ?></td>
                    <td class="fw-semibold"><?php echo money($p); ?></td>
                  </tr>
                <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-lg-5">
          <div class="section-title">Add New Service</div>
          <div class="section-sub">Add a service type available to all vehicles.</div>
          <div class="card p-4">
            <form method="POST">
              <div class="mb-3">
                <label class="form-label">Service Name</label>
                <input type="text" name="name" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Description</label>
                <input type="text" name="description" class="form-control">
              </div>
              <div class="mb-3">
                <label class="form-label">Base Price (₹)</label>
                <input type="number" step="0.01" name="base_price" class="form-control" required>
                <div class="form-text">This is the price for a multiplier of ×1.00 (e.g. Swift, Baleno tier).</div>
              </div>
              <button type="submit" name="add_service" value="1" class="btn btn-amber w-100"><i class="bi bi-plus-lg"></i> Add Service</button>
            </form>
          </div>

          <div class="section-title mt-4">Vehicle Catalogue</div>
          <div class="section-sub">Manage companies, models &amp; their price multiplier.</div>
          <div class="card p-4 text-center">
            <a href="/admin/vehicle_catalogue.php" class="btn btn-outline-secondary w-100"><i class="bi bi-list-ul"></i> View / Edit Catalogue</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
