<?php
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    header("Location: " . (isAdmin() ? 'admin/dashboard.php' : 'customer/dashboard.php'));
    exit;
}

$brands = $conn->query("SELECT * FROM brands ORDER BY name");
$serviceTypes = $conn->query("SELECT * FROM service_types ORDER BY id");
$brandsArr = []; while ($b = $brands->fetch_assoc()) { $brandsArr[] = $b; }
$serviceArr = []; while ($s = $serviceTypes->fetch_assoc()) { $serviceArr[] = $s; }

$segmentRes = $conn->query("SELECT segment, battery_price, tyre_price FROM segment_pricing");
$segmentPricing = [];
while ($sp = $segmentRes->fetch_assoc()) { $segmentPricing[$sp['segment']] = $sp; }

$pageTitle = "Home";
include __DIR__ . '/includes/header.php';
?>

<!-- ===================== HERO ===================== -->
<section class="hero-section">
  <div class="row align-items-center g-4">
    <div class="col-lg-6">
      <span class="hero-eyebrow"><i class="bi bi-lightning-charge-fill"></i> Instant price, no phone calls</span>
      <h1 class="hero-title mb-3">Know your service<br>price <span class="highlight">before</span> you book.</h1>
      <p class="hero-sub mb-4">Tell us your car's brand and model. We'll calculate the exact service cost on the spot — then track every step from drop-off to done.</p>

      <div class="quick-book-widget">
        <label class="form-label small text-muted mb-2">Get an instant estimate</label>
        <div class="row g-2">
          <div class="col-6">
            <select class="form-select" id="homeBrand">
              <option value="">Brand</option>
              <?php foreach ($brandsArr as $b): ?>
                <option value="<?php echo $b['id']; ?>"><?php echo htmlspecialchars($b['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-6">
            <select class="form-select" id="homeModel"><option value="">Model</option></select>
          </div>
          <div class="col-12">
            <select class="form-select" id="homeService">
              <option value="">Service Type</option>
              <?php foreach ($serviceArr as $s): ?>
                <option value="<?php echo $s['id']; ?>" data-mult="<?php echo $s['base_multiplier']; ?>"><?php echo htmlspecialchars($s['name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <div class="d-flex align-items-center justify-content-between mt-3">
          <div>
            <div class="small text-muted mb-0">Estimated price</div>
            <div class="fs-4 fw-bold" id="homeEstimate" style="color: var(--vsp-primary-deep);">₹0</div>
          </div>
          <a href="register.php" class="btn btn-accent">Book This Service</a>
        </div>
      </div>
    </div>

    <div class="col-lg-6 d-none d-lg-block">
      <div class="dial-wrap">
        <svg class="dial-svg" width="320" height="320" viewBox="0 0 320 320">
          <circle class="dial-track" cx="160" cy="160" r="130" fill="none" stroke-width="18"/>
          <circle class="dial-fill" cx="160" cy="160" r="130" fill="none" stroke-width="18"
                  stroke-dasharray="620" stroke-dashoffset="180" stroke-linecap="round"
                  transform="rotate(-90 160 160)"/>
          <text x="160" y="150" text-anchor="middle" class="dial-center-text" font-size="46" font-weight="700">98%</text>
          <text x="160" y="182" text-anchor="middle" class="dial-center-text" font-size="14" opacity="0.7">On-time completion</text>
        </svg>
      </div>
    </div>
  </div>
</section>

<!-- ===================== WHAT'S INCLUDED (auto-rotating) ===================== -->
<section class="py-5">
  <h3 class="text-center mb-1">What's included in each service?</h3>
  <p class="text-center text-muted mb-4">Watch it cycle through automatically, or click a dot to jump.</p>

  <div class="card service-info-card p-4">
    <div id="serviceCarousel">

      <div class="service-slide active" data-index="0">
        <h5><i class="bi bi-droplet-fill"></i> Basic Service</h5>
        <ul>
          <li>Engine oil & oil filter replacement</li>
          <li>Air filter cleaning</li>
          <li>Fluid top-up (coolant, brake, wiper)</li>
          <li>General multi-point inspection</li>
        </ul>
      </div>

      <div class="service-slide" data-index="1">
        <h5><i class="bi bi-gear-fill"></i> Standard Service</h5>
        <ul>
          <li>Everything in Basic Service</li>
          <li>Brake pad inspection & adjustment</li>
          <li>Wheel alignment & balancing</li>
          <li>Battery health check</li>
        </ul>
      </div>

      <div class="service-slide" data-index="2">
        <h5><i class="bi bi-stars"></i> Premium Service</h5>
        <ul>
          <li>Everything in Standard Service</li>
          <li>AC gas check & performance service</li>
          <li>Interior deep cleaning</li>
          <li>Full engine diagnostics scan</li>
        </ul>
      </div>

      <div class="service-slide" data-index="3">
        <h5><i class="bi bi-brush-fill"></i> Denting & Painting</h5>
        <ul>
          <li>Dent removal & panel repair</li>
          <li>Primer coating & color matching</li>
          <li>Full-body paint job</li>
          <li>Polishing & buffing finish</li>
        </ul>
      </div>

      <div class="service-slide" data-index="4">
        <h5><i class="bi bi-battery-charging"></i> Battery / Tyre Replacement</h5>
        <ul>
          <li>Battery health check & replacement</li>
          <li>Tyre pressure check across all 4 tyres</li>
          <li>Tyre rotation or replacement</li>
          <li>Wheel balancing</li>
        </ul>
      </div>

    </div>

    <div class="service-dots" id="serviceDots">
      <span class="service-dot active" data-index="0"></span>
      <span class="service-dot" data-index="1"></span>
      <span class="service-dot" data-index="2"></span>
      <span class="service-dot" data-index="3"></span>
      <span class="service-dot" data-index="4"></span>
    </div>
  </div>
</section>

<script>
(function () {
  const slides = document.querySelectorAll('#serviceCarousel .service-slide');
  const dots = document.querySelectorAll('#serviceDots .service-dot');
  let current = 0;
  let timer;

  function showSlide(i) {
    slides.forEach(s => s.classList.remove('active'));
    dots.forEach(d => d.classList.remove('active'));
    slides[i].classList.add('active');
    dots[i].classList.add('active');
    current = i;
  }

  function nextSlide() {
    showSlide((current + 1) % slides.length);
  }

  function startAutoRotate() {
    timer = setInterval(nextSlide, 3500);
  }

  dots.forEach(dot => {
    dot.addEventListener('click', function () {
      clearInterval(timer);
      showSlide(parseInt(this.dataset.index));
      startAutoRotate(); // restart the cycle after manual click
    });
  });

  startAutoRotate();
})();
</script>

<!-- ===================== SERVICE CATEGORIES ===================== -->
<section class="py-5">
  <h3 class="text-center mb-1">What does your car need?</h3>
  <p class="text-center text-muted mb-4">Pick a service — pricing adjusts automatically to your car's model.</p>
  <div class="row g-3">
    <div class="col-6 col-md-4 col-lg-2">
      <div class="service-cat-card">
        <div class="icon-circle"><i class="bi bi-droplet-fill"></i></div>
        <h6 class="mb-0">Basic Service</h6>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="service-cat-card">
        <div class="icon-circle"><i class="bi bi-gear-fill"></i></div>
        <h6 class="mb-0">Standard Service</h6>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="service-cat-card">
        <div class="icon-circle"><i class="bi bi-stars"></i></div>
        <h6 class="mb-0">Premium Service</h6>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="service-cat-card">
        <div class="icon-circle"><i class="bi bi-brush-fill"></i></div>
        <h6 class="mb-0">Denting & Painting</h6>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="service-cat-card">
        <div class="icon-circle"><i class="bi bi-battery-charging"></i></div>
        <h6 class="mb-0">Battery / Tyre</h6>
      </div>
    </div>
    <div class="col-6 col-md-4 col-lg-2">
      <div class="service-cat-card">
        <div class="icon-circle"><i class="bi bi-clock-history"></i></div>
        <h6 class="mb-0">Track Status</h6>
      </div>
    </div>
  </div>
</section>

<!-- ===================== HOW IT WORKS ===================== -->
<section class="py-4">
  <h3 class="text-center mb-4">Three steps. That's it.</h3>
  <div class="row g-4">
    <div class="col-md-4">
      <div class="step-card">
        <div class="step-num">01</div>
        <h6 class="mt-2">Select your vehicle</h6>
        <p class="text-muted small mb-0">Add your car's brand, model and registration number once — reuse it for every future booking.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="step-card">
        <div class="step-num">02</div>
        <h6 class="mt-2">Choose a service & date</h6>
        <p class="text-muted small mb-0">Price is calculated instantly from your exact model — no surprise charges at drop-off.</p>
      </div>
    </div>
    <div class="col-md-4">
      <div class="step-card">
        <div class="step-num">03</div>
        <h6 class="mt-2">Track & download invoice</h6>
        <p class="text-muted small mb-0">Watch your service move from Pending to Completed, then download a PDF invoice instantly.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===================== STATS STRIP ===================== -->
<section class="py-4">
  <div class="stats-strip row text-center g-4">
    <div class="col-6 col-md-3">
      <div class="stat-num"><?php echo (int) $conn->query("SELECT COUNT(*) c FROM users WHERE role='customer'")->fetch_assoc()['c']; ?>+</div>
      <div class="stat-lbl">Customers Served</div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-num"><?php echo (int) $conn->query("SELECT COUNT(*) c FROM bookings")->fetch_assoc()['c']; ?>+</div>
      <div class="stat-lbl">Services Booked</div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-num"><?php echo count($brandsArr); ?></div>
      <div class="stat-lbl">Car Brands Supported</div>
    </div>
    <div class="col-6 col-md-3">
      <div class="stat-num">5</div>
      <div class="stat-lbl">Service Categories</div>
    </div>
  </div>
</section>

<!-- ===================== CTA ===================== -->
<section class="py-5">
  <div class="cta-banner text-center">
    <h3 class="mb-2">Ready to book your car's next service?</h3>
    <p class="mb-4" style="opacity:.9;">Create a free account and get an instant price for your exact model.</p>
    <a href="register.php" class="btn btn-accent btn-lg me-2">Create Free Account</a>
    <a href="login.php" class="btn btn-outline-light btn-lg">Login</a>
  </div>
</section>

<script>
const homeModels = <?php
  $modelsList = $conn->query("SELECT m.id, m.name, m.brand_id, m.segment, mp.base_price FROM models m LEFT JOIN model_pricing mp ON mp.model_id = m.id ORDER BY m.name");
  $arr = [];
  while ($m = $modelsList->fetch_assoc()) { $arr[] = $m; }
  echo json_encode($arr);
?>;
const segmentPricing = <?php echo json_encode($segmentPricing); ?>;

const hBrand = document.getElementById('homeBrand');
const hModel = document.getElementById('homeModel');
const hService = document.getElementById('homeService');
const hEstimate = document.getElementById('homeEstimate');

hBrand.addEventListener('change', function () {
  hModel.innerHTML = '<option value="">Model</option>';
  homeModels.filter(m => m.brand_id == this.value).forEach(m => {
    const opt = document.createElement('option');
    opt.value = m.id;
    opt.textContent = m.name;
    opt.dataset.basePrice = m.base_price || 0;
    opt.dataset.segment = m.segment || 'Hatchback';
    hModel.appendChild(opt);
  });
  calcHomePrice();
});
hModel.addEventListener('change', calcHomePrice);
hService.addEventListener('change', calcHomePrice);

function calcHomePrice() {
  const modelOpt = hModel.selectedOptions[0];
  const serviceOpt = hService.selectedOptions[0];
  if (!modelOpt || !modelOpt.dataset.basePrice || !serviceOpt || !serviceOpt.dataset.mult) {
    hEstimate.textContent = '₹0';
    return;
  }

  const segment = modelOpt.dataset.segment;
  const serviceName = serviceOpt.textContent.toLowerCase();
  let total = 0;

  if (serviceName.includes('battery')) {
    total = segmentPricing[segment] ? parseFloat(segmentPricing[segment].battery_price) : 0;
  } else if (serviceName.includes('tyre')) {
    // Homepage widget shows a quick single-tyre estimate; exact quantity is chosen at actual booking
    total = segmentPricing[segment] ? parseFloat(segmentPricing[segment].tyre_price) : 0;
  } else {
    total = parseFloat(modelOpt.dataset.basePrice) * parseFloat(serviceOpt.dataset.mult);
  }

  total = total.toFixed(0);
  hEstimate.textContent = '₹' + Number(total).toLocaleString('en-IN');
}
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
