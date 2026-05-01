// Simple modal logic for productos.html
document.addEventListener('DOMContentLoaded', function(){
  const modal = document.getElementById('product-modal');
  const modalTitle = document.getElementById('modal-title');
  const modalProduct = document.getElementById('modal-product');
  const openBtns = document.querySelectorAll('.open-modal');
  const closeBtns = document.querySelectorAll('.modal-close');
  const form = document.getElementById('modal-form');

  function openModal(productName){
    modal.setAttribute('aria-hidden','false');
    modalTitle.textContent = 'Solicitar: ' + productName;
    modalProduct.value = productName;
  }

  function closeModal(){
    modal.setAttribute('aria-hidden','true');
  }

  openBtns.forEach(b => b.addEventListener('click', function(e){
    const p = b.dataset.product || 'Producto';
    openModal(p);
  }));

  closeBtns.forEach(b => b.addEventListener('click', closeModal));

  // click outside dialog to close
  modal.addEventListener('click', function(e){
    if(e.target === modal) closeModal();
  });

  form.addEventListener('submit', function(e){
    e.preventDefault();
    // Simulamos envío — aquí puedes integrar un portal de correo o API
    const data = new FormData(form);
    const summary = Array.from(data.entries()).map(([k,v])=>`${k}: ${v}`).join('\n');
    alert('Solicitud enviada (simulada):\n' + summary);
    form.reset();
    closeModal();
  });

  // Filtrado de productos por categoría (menu en productos.html)
  const filters = document.querySelectorAll('.product-filter');
  const productRows = document.querySelectorAll('.product-row');
  const searchInput = document.getElementById('product-search');

  // Debounce helper
  function debounce(fn, wait){
    let t;
    return function(...args){
      clearTimeout(t);
      t = setTimeout(()=>fn.apply(this,args), wait);
    };
  }

  function filterProducts(){
    const activeBtn = document.querySelector('.product-filter.active');
    const cat = (activeBtn && activeBtn.dataset.filter) ? activeBtn.dataset.filter : 'all';
    const q = (searchInput && searchInput.value) ? searchInput.value.trim().toLowerCase() : '';

    productRows.forEach(row => {
      const rowCat = row.getAttribute('data-category') || 'higiene';
      const text = (row.textContent || '').toLowerCase();
      const matchesCat = (cat === 'all') || (rowCat === cat);
      const matchesQuery = !q || text.indexOf(q) !== -1;
      row.style.display = (matchesCat && matchesQuery) ? '' : 'none';
    });
  }

  const debouncedFilter = debounce(filterProducts, 180);

  filters.forEach(btn => {
    btn.addEventListener('click', function(){
      filters.forEach(b => { b.classList.remove('active'); b.setAttribute('aria-pressed','false'); });
      btn.classList.add('active'); btn.setAttribute('aria-pressed','true');
      filterProducts();
    });
    // accesibilidad: permitir activar con teclado
    btn.addEventListener('keydown', function(e){
      if(e.key === 'Enter' || e.key === ' '){
        e.preventDefault(); btn.click();
      }
    });
  });

  if(searchInput){
    searchInput.addEventListener('input', debouncedFilter);
    searchInput.addEventListener('search', debouncedFilter); // para input type=search clear
  }

  // Aplicar filtro inicial (mostrar todos)
  filterProducts();

  // Carrito: manejar clicks en botones "add-cart" y enviar a endpoint
  const addButtons = document.querySelectorAll('.add-cart');
  addButtons.forEach(btn => btn.addEventListener('click', function(){
    const id = btn.dataset.id;
    const name = btn.dataset.name || btn.closest('.product-row')?.querySelector('h3')?.textContent || 'Producto';
    const price = btn.dataset.price || '0';
    const form = new FormData();
    form.append('action','add');
    form.append('id', id);
    form.append('name', name);
    form.append('price', price);
    fetch('scr/actions/cart.php', { method: 'POST', body: form })
      .then(r=>r.json())
      .then(data=>{
        if (data && data.ok){
          // feedback simple
          btn.textContent = 'Añadido';
          btn.disabled = true;
          setTimeout(()=>{ btn.textContent = 'Añadir al carrito'; btn.disabled = false; }, 1200);
        } else {
          alert('Error al añadir al carrito: ' + (data && data.error ? data.error : 'error desconocido'));
        }
      }).catch(err=>{ alert('Error de red al añadir al carrito'); console.error(err); });
  }));
});
