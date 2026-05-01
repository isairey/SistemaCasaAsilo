// Dropdown behavior: toggle, close on outside click and Escape, keyboard accessible
(function(){
  'use strict'

  function initDropdowns(){
    const dropdowns = document.querySelectorAll('.nav-item.dropdown');
    if(!dropdowns.length) return;

    // Close all open menus
    function closeAll(){
      dropdowns.forEach(d => {
        const menu = d.querySelector('.dropdown-menu');
        if(menu) menu.classList.remove('show');
      });
    }

    // Toggle single
    dropdowns.forEach(drop => {
      const toggle = drop.querySelector('.nav-link');
      const menu = drop.querySelector('.dropdown-menu');

      if(!toggle || !menu) return;

      // click to toggle (works on mobile and desktop)
      toggle.addEventListener('click', function(ev){
        ev.preventDefault();
        const isOpen = menu.classList.contains('show');
        closeAll();
        if(!isOpen) menu.classList.add('show');
      });

      // keyboard: Enter or Space toggles
      toggle.addEventListener('keydown', function(ev){
        if(ev.key === 'Enter' || ev.key === ' ') {
          ev.preventDefault();
          toggle.click();
        }
        if(ev.key === 'Escape'){
          menu.classList.remove('show');
          toggle.focus();
        }
      });

      // close when clicking a menu item (useful for mobile collapsed menu)
      menu.addEventListener('click', function(ev){
        const target = ev.target.closest('.dropdown-item');
        if(target) closeAll();
      });

    });

    // click outside closes
    document.addEventListener('click', function(ev){
      const inside = ev.target.closest('.nav-item.dropdown');
      if(!inside) closeAll();
    });

    // Escape closes
    document.addEventListener('keydown', function(ev){
      if(ev.key === 'Escape') closeAll();
    });

    // close on resize to avoid stuck open menus when switching between mobile/desktop
    window.addEventListener('resize', function(){
      closeAll();
    });
  }

  if(document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', initDropdowns);
  } else initDropdowns();
})();
