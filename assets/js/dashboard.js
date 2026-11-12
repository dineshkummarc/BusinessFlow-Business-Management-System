/* ============================================================
   Business Management System — Dashboard JS
   ============================================================ */
document.addEventListener('DOMContentLoaded', function () {

  // ---- Sidebar toggle (mobile) ----
  const sidebar = document.querySelector('.dash-sidebar');
  const overlay = document.querySelector('.sidebar-overlay');
  const toggleBtn = document.querySelector('.sidebar-toggle-btn');

  function closeSidebar() {
    sidebar?.classList.remove('show');
    overlay?.classList.remove('show');
  }

  toggleBtn?.addEventListener('click', function () {
    sidebar?.classList.toggle('show');
    overlay?.classList.toggle('show');
  });
  overlay?.addEventListener('click', closeSidebar);

  // ---- Delete confirmation ----
  document.querySelectorAll('.confirm-delete').forEach((form) => {
    form.addEventListener('submit', function (e) {
      const label = this.getAttribute('data-label') || 'this record';
      if (!confirm('Are you sure you want to delete ' + label + '? This cannot be undone.')) {
        e.preventDefault();
      }
    });
  });

  // ---- Live client-side table search ----
  document.querySelectorAll('[data-table-search]').forEach((input) => {
    input.addEventListener('keyup', function () {
      const targetSelector = this.getAttribute('data-table-search');
      const table = document.querySelector(targetSelector);
      if (!table) return;
      const query = this.value.toLowerCase();
      table.querySelectorAll('tbody tr').forEach((row) => {
        row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
      });
    });
  });

  // ---- Auto-dismiss alerts ----
  document.querySelectorAll('.alert-auto-dismiss').forEach((alertEl) => {
    setTimeout(() => {
      alertEl.style.transition = 'opacity 0.5s ease';
      alertEl.style.opacity = '0';
      setTimeout(() => alertEl.remove(), 500);
    }, 4000);
  });

  // ---- Invoice item row calculator (add invoice form) ----
  const itemsWrap = document.getElementById('invoiceItems');
  if (itemsWrap) {
    function recalcRow(row) {
      const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
      const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
      const lineTotalEl = row.querySelector('.item-line-total');
      if (lineTotalEl) lineTotalEl.textContent = (qty * price).toFixed(2);
      recalcGrandTotal();
    }

    function recalcGrandTotal() {
      let subtotal = 0;
      itemsWrap.querySelectorAll('.invoice-item-row').forEach((row) => {
        const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
        const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
        subtotal += qty * price;
      });
      const taxPercent = parseFloat(document.getElementById('taxPercent')?.value) || 0;
      const tax = subtotal * (taxPercent / 100);
      const total = subtotal + tax;

      const subtotalEl = document.getElementById('invoiceSubtotal');
      const taxEl = document.getElementById('invoiceTax');
      const totalEl = document.getElementById('invoiceTotal');
      if (subtotalEl) subtotalEl.textContent = subtotal.toFixed(2);
      if (taxEl) taxEl.textContent = tax.toFixed(2);
      if (totalEl) totalEl.textContent = total.toFixed(2);
    }

    itemsWrap.addEventListener('input', function (e) {
      if (e.target.classList.contains('item-qty') || e.target.classList.contains('item-price')) {
        recalcRow(e.target.closest('.invoice-item-row'));
      }
    });

    document.getElementById('taxPercent')?.addEventListener('input', recalcGrandTotal);

    document.getElementById('addInvoiceItem')?.addEventListener('click', function () {
      const rows = itemsWrap.querySelectorAll('.invoice-item-row');
      const newRow = rows[rows.length - 1].cloneNode(true);
      newRow.querySelectorAll('input').forEach((input) => {
        if (input.classList.contains('item-desc')) input.value = '';
        if (input.classList.contains('item-qty')) input.value = 1;
        if (input.classList.contains('item-price')) input.value = 0;
      });
      newRow.querySelector('.item-line-total').textContent = '0.00';
      itemsWrap.insertBefore(newRow, document.getElementById('addInvoiceItem').parentElement);
    });

    itemsWrap.addEventListener('click', function (e) {
      if (e.target.closest('.remove-item-row')) {
        const rows = itemsWrap.querySelectorAll('.invoice-item-row');
        if (rows.length > 1) {
          e.target.closest('.invoice-item-row').remove();
          recalcGrandTotal();
        }
      }
    });
  }

  // ---- Progress range display (project form) ----
  const progressInput = document.getElementById('projectProgress');
  const progressLabel = document.getElementById('projectProgressLabel');
  if (progressInput && progressLabel) {
    progressInput.addEventListener('input', () => {
      progressLabel.textContent = progressInput.value + '%';
    });
  }

  // ---- Topbar quick-navigation search ----
  const quickNavInput = document.getElementById('quickNavInput');
  const quickNavResults = document.getElementById('quickNavResults');
  if (quickNavInput && quickNavResults && Array.isArray(window.BMS_NAV_INDEX)) {
    const basePath = window.BMS_BASE_PATH || '';
    let activeIndex = -1;

    function renderResults(matches) {
      quickNavResults.innerHTML = '';
      if (!matches.length) {
        quickNavResults.classList.remove('show');
        return;
      }
      matches.forEach((item, i) => {
        const row = document.createElement('a');
        row.href = basePath + '/' + item.url;
        row.className = 'quick-nav-item' + (i === activeIndex ? ' active' : '');
        row.innerHTML = '<i class="bi ' + item.icon + '"></i><span>' + item.label + '</span>';
        quickNavResults.appendChild(row);
      });
      quickNavResults.classList.add('show');
    }

    function search(query) {
      activeIndex = -1;
      if (!query.trim()) {
        quickNavResults.classList.remove('show');
        return;
      }
      const q = query.toLowerCase();
      const matches = window.BMS_NAV_INDEX.filter((item) => item.label.toLowerCase().includes(q));
      renderResults(matches);
    }

    quickNavInput.addEventListener('input', () => search(quickNavInput.value));
    quickNavInput.addEventListener('focus', () => { if (quickNavInput.value.trim()) search(quickNavInput.value); });

    quickNavInput.addEventListener('keydown', (e) => {
      const items = quickNavResults.querySelectorAll('.quick-nav-item');
      if (!items.length) return;
      if (e.key === 'ArrowDown') {
        e.preventDefault();
        activeIndex = Math.min(activeIndex + 1, items.length - 1);
        items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
      } else if (e.key === 'ArrowUp') {
        e.preventDefault();
        activeIndex = Math.max(activeIndex - 1, 0);
        items.forEach((el, i) => el.classList.toggle('active', i === activeIndex));
      } else if (e.key === 'Enter') {
        e.preventDefault();
        const target = items[activeIndex] || items[0];
        if (target) window.location.href = target.href;
      } else if (e.key === 'Escape') {
        quickNavResults.classList.remove('show');
      }
    });

    document.addEventListener('click', (e) => {
      if (!quickNavInput.contains(e.target) && !quickNavResults.contains(e.target)) {
        quickNavResults.classList.remove('show');
      }
    });
  }
});
