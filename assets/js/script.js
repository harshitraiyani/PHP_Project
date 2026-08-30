// ==========================================================
// Theme toggle (light/dark) with localStorage persistence
// ==========================================================
(function () {
  const htmlEl = document.documentElement;
  const toggleBtn = document.getElementById('themeToggle');
  const savedTheme = localStorage.getItem('vsp-theme') || 'light';
  htmlEl.setAttribute('data-bs-theme', savedTheme);
  updateIcon(savedTheme);

  if (toggleBtn) {
    toggleBtn.addEventListener('click', function () {
      const current = htmlEl.getAttribute('data-bs-theme');
      const next = current === 'light' ? 'dark' : 'light';
      htmlEl.setAttribute('data-bs-theme', next);
      localStorage.setItem('vsp-theme', next);
      updateIcon(next);
    });
  }

  function updateIcon(theme) {
    if (!toggleBtn) return;
    toggleBtn.innerHTML = theme === 'light'
      ? '<i class="bi bi-moon-stars-fill"></i>'
      : '<i class="bi bi-sun-fill"></i>';
  }
})();

// ==========================================================
// Booking page: dynamic price calculation
// brandSelect -> loads models -> modelSelect + serviceType -> price
// ==========================================================
function initBookingForm(pricingData, serviceTypes) {
  const brandSelect = document.getElementById('brand_id');
  const modelSelect = document.getElementById('model_id');
  const serviceSelect = document.getElementById('service_type_id');
  const priceDisplay = document.getElementById('priceDisplay');
  const priceInput = document.getElementById('price_hidden');

  if (!brandSelect) return;

  brandSelect.addEventListener('change', function () {
    const brandId = this.value;
    modelSelect.innerHTML = '<option value="">-- Select Model --</option>';
    if (!brandId) return;

    pricingData.forEach(function (m) {
      if (m.brand_id == brandId) {
        const opt = document.createElement('option');
        opt.value = m.model_id;
        opt.textContent = m.model_name;
        opt.dataset.basePrice = m.base_price;
        modelSelect.appendChild(opt);
      }
    });
    calculatePrice();
  });

  modelSelect.addEventListener('change', calculatePrice);
  serviceSelect.addEventListener('change', calculatePrice);

  function calculatePrice() {
    const modelOpt = modelSelect.options[modelSelect.selectedIndex];
    const serviceOpt = serviceSelect.options[serviceSelect.selectedIndex];

    if (!modelOpt || !modelOpt.dataset.basePrice || !serviceOpt || !serviceOpt.dataset.multiplier) {
      priceDisplay.textContent = '₹0';
      if (priceInput) priceInput.value = 0;
      return;
    }

    const base = parseFloat(modelOpt.dataset.basePrice);
    const mult = parseFloat(serviceOpt.dataset.multiplier);
    const total = (base * mult).toFixed(2);

    priceDisplay.textContent = '₹' + Number(total).toLocaleString('en-IN');
    if (priceInput) priceInput.value = total;
  }
}
