// nav-toggle.js - simple navbar hamburger toggle for projects that don't use Bootstrap JS

document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.navbar-toggler').forEach(function (btn) {
    const targetSelector = btn.getAttribute('data-bs-target') || btn.getAttribute('data-target') || '#navbarNav';
    const target = document.querySelector(targetSelector);
    // ensure accessibility defaults
    btn.setAttribute('aria-expanded', 'false');
    if (target) target.setAttribute('aria-hidden', 'true');

    btn.addEventListener('click', function (e) {
      e.preventDefault();
      const navbar = btn.closest('.navbar');
      if (!navbar) return;
      const isOpen = navbar.classList.toggle('open');
      btn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      if (target) target.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
      document.documentElement.classList.toggle('nav-open', isOpen);
    });
  });

  // close menu when clicking a nav link (mobile friendly)
  // but ignore links that open a dropdown (we want the submenu to open in-panel)
  document.querySelectorAll('.navbar .collapse a.nav-link').forEach(function (link) {
    link.addEventListener('click', function () {
      // if this link is a dropdown toggle or inside a dropdown, do not auto-close
      if (link.classList.contains('dropdown-toggle') || link.closest('.nav-item.dropdown')) return;
      const navbar = link.closest('.navbar');
      if (!navbar) return;
      navbar.classList.remove('open');
      const btn = navbar.querySelector('.navbar-toggler');
      if (btn) btn.setAttribute('aria-expanded', 'false');
      const targetSelector = btn && (btn.getAttribute('data-bs-target') || btn.getAttribute('data-target') || '#navbarNav');
      const target = document.querySelector(targetSelector);
      if (target) target.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('nav-open');
    });
  });

  // close panel when clicking a dropdown item inside the collapsed menu (mobile)
  document.querySelectorAll('.navbar .collapse .dropdown-item').forEach(function(item){
    item.addEventListener('click', function(){
      const navbar = item.closest('.navbar');
      if (!navbar) return;
      navbar.classList.remove('open');
      const btn = navbar.querySelector('.navbar-toggler');
      if (btn) btn.setAttribute('aria-expanded', 'false');
      const targetSelector = btn && (btn.getAttribute('data-bs-target') || btn.getAttribute('data-target') || '#navbarNav');
      const target = document.querySelector(targetSelector);
      if (target) target.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('nav-open');
    });
  });

  // close on Escape
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      document.querySelectorAll('.navbar.open').forEach(function (navbar) {
        navbar.classList.remove('open');
        const btn = navbar.querySelector('.navbar-toggler');
        if (btn) btn.setAttribute('aria-expanded', 'false');
        const targetSelector = btn && (btn.getAttribute('data-bs-target') || btn.getAttribute('data-target') || '#navbarNav');
        const target = document.querySelector(targetSelector);
        if (target) target.setAttribute('aria-hidden', 'true');
      });
      document.documentElement.classList.remove('nav-open');
    }
  });

  // close when clicking outside the collapse (overlay click behavior)
  document.addEventListener('click', function (e) {
    // if no nav open, nothing to do
    var openNavbars = document.querySelectorAll('.navbar.open');
    if (!openNavbars || openNavbars.length === 0) return;

    openNavbars.forEach(function (navbar) {
      var collapse = navbar.querySelector('.collapse');
      var toggler = navbar.querySelector('.navbar-toggler');
      // if click happened inside collapse or on toggler, ignore
      if (collapse && collapse.contains(e.target)) return;
      if (toggler && toggler.contains(e.target)) return;

      // otherwise close this navbar
      navbar.classList.remove('open');
      if (toggler) toggler.setAttribute('aria-expanded', 'false');
      var targetSelector = toggler && (toggler.getAttribute('data-bs-target') || toggler.getAttribute('data-target') || '#navbarNav');
      var target = document.querySelector(targetSelector);
      if (target) target.setAttribute('aria-hidden', 'true');
      document.documentElement.classList.remove('nav-open');
    });
  });

});
