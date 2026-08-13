/* ============================================================
   Business Management System — Public Site JS
   ============================================================ */
document.addEventListener('DOMContentLoaded', function () {

  // ---- Scroll reveal animation ----
  const revealEls = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && revealEls.length) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add('visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.15 });

    revealEls.forEach((el) => observer.observe(el));
  } else {
    revealEls.forEach((el) => el.classList.add('visible'));
  }

  // ---- Navbar shadow on scroll ----
  const navbar = document.querySelector('.navbar-bms');
  if (navbar) {
    window.addEventListener('scroll', function () {
      if (window.scrollY > 20) {
        navbar.style.boxShadow = '0 4px 22px rgba(15,42,74,0.12)';
      } else {
        navbar.style.boxShadow = '0 2px 15px rgba(15,42,74,0.06)';
      }
    });
  }

  // ---- Active nav link highlighting based on current page ----
  const currentPage = window.location.pathname.split('/').pop() || 'index.php';
  document.querySelectorAll('.navbar-bms .nav-link').forEach((link) => {
    const href = link.getAttribute('href');
    if (href && href.indexOf(currentPage) !== -1 && currentPage !== '') {
      link.classList.add('active');
    }
  });

  // ---- Smooth scroll for in-page anchors ----
  document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
    anchor.addEventListener('click', function (e) {
      const targetId = this.getAttribute('href');
      if (targetId.length > 1) {
        const target = document.querySelector(targetId);
        if (target) {
          e.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }
    });
  });

  // ---- Animated stat counters on the landing page ----
  const counters = document.querySelectorAll('.stat-num[data-count]');
  if (counters.length && 'IntersectionObserver' in window) {
    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          animateCounter(entry.target);
          counterObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.6 });
    counters.forEach((c) => counterObserver.observe(c));
  }

  function animateCounter(el) {
    const target = parseInt(el.getAttribute('data-count'), 10) || 0;
    const suffix = el.getAttribute('data-suffix') || '';
    const duration = 1400;
    const start = performance.now();

    function step(now) {
      const progress = Math.min((now - start) / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      el.textContent = Math.floor(eased * target).toLocaleString() + suffix;
      if (progress < 1) {
        requestAnimationFrame(step);
      } else {
        el.textContent = target.toLocaleString() + suffix;
      }
    }
    requestAnimationFrame(step);
  }

  // ---- Basic client-side validation feedback (Bootstrap pattern) ----
  document.querySelectorAll('form.needs-validation').forEach((form) => {
    form.addEventListener('submit', function (e) {
      if (!form.checkValidity()) {
        e.preventDefault();
        e.stopPropagation();
      }
      form.classList.add('was-validated');
    });
  });

  // ---- Password visibility toggle (login/register) ----
  document.querySelectorAll('.toggle-password').forEach((btn) => {
    btn.addEventListener('click', function () {
      const input = document.querySelector(this.getAttribute('data-target'));
      if (!input) return;
      const isPassword = input.getAttribute('type') === 'password';
      input.setAttribute('type', isPassword ? 'text' : 'password');
      this.innerHTML = isPassword
        ? '<i class="bi bi-eye-slash"></i>'
        : '<i class="bi bi-eye"></i>';
    });
  });
});
