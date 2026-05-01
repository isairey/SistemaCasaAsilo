document.addEventListener('DOMContentLoaded', function(){
  const navAuthWrap = document.getElementById('nav-auth-wrap');
  // Referencias a modales y formularios (asegurar que existen antes de usarlos)
  const loginModal = document.getElementById('login-modal');
  const registerModal = document.getElementById('register-modal');
  const loginForm = document.getElementById('login-form');
  const registerForm = document.getElementById('register-form');
  document.querySelectorAll('.open-login').forEach(function (btn) {
    btn.addEventListener('click', function (e) {
      e.preventDefault();
      try {
        // If we're in an admin area, redirect to the admin login page instead of opening the modal
        if ((window.location.pathname || '').indexOf('/admin') !== -1) {
          window.location.href = (window.location.origin || '') + '/casabonsai/admin/login.php';
          return;
        }
      } catch(err){}
      openModal(document.getElementById('login-modal'));
    });
  });
  document.querySelectorAll('.open-register').forEach(function (btn) {
    btn.addEventListener('click', function (e) { e.preventDefault(); openModal(document.getElementById('register-modal')); });
  });

  function openModal(modal){ modal.setAttribute('aria-hidden','false'); }
  function closeModal(modal){ modal.setAttribute('aria-hidden','true'); }

  // show/hide handlers
  document.addEventListener('click', function(e){
    if (e.target.matches('.open-login')) {
      try {
        if ((window.location.pathname || '').indexOf('/admin') !== -1) {
          window.location.href = (window.location.origin || '') + '/casabonsai/admin/login.php';
          return;
        }
      } catch(e){}
      openModal(loginModal);
    }
    if (e.target.matches('.open-register')) openModal(registerModal);
    // botones de cierre: el pequeño (×) usa .modal-close; los botones "Cancelar" dentro del formulario usan .form-cancel
    if (e.target.matches('.modal-close') || e.target.matches('.form-cancel')) closeModal(e.target.closest('.modal'));
  });

  // click outside to close
  [loginModal, registerModal].forEach(m => {
    if (!m) return;
    m.addEventListener('click', function(e){ if (e.target === m) closeModal(m); });
  });

  function updateNavUser(user){
    if (!navAuthWrap) return;
    // We'll populate both desktop `#nav-auth-wrap` and any mobile `.nav-auth-mobile` containers
    const mobileAuthNodes = document.querySelectorAll('.nav-auth-mobile');
    if (user){
      // HTML for authenticated user (desktop)
      const userHtml = `
        <li class="nav-item">
          <a class="nav-link nav-profile" href="scr/pages/perfil.php" title="Ver perfil" style="display:inline-block;">
            <span class="nav-user">Hola, Bienvenid@ ${escapeHtml(user.user || 'Usuario')}</span>
          </a>
        </li>
        <li class="nav-item"><a href="#" class="nav-link logout-link">Cerrar sesión</a></li>
      `;
      // Mobile variant: smaller layout (no inline flex styles to avoid layout issues inside collapse)
      const mobileHtml = `
        <li class="nav-item"><a class="nav-link nav-profile" href="scr/pages/perfil.php" title="Ver perfil"><span class="nav-user">${escapeHtml(user.user || 'Usuario')}</span></a></li>
        <li class="nav-item"><a href="#" class="nav-link logout-link">Cerrar sesión</a></li>
      `;


      navAuthWrap.innerHTML = userHtml;
      mobileAuthNodes.forEach(n => n.innerHTML = mobileHtml);

      // attach logout handlers for any rendered logout-link
      document.querySelectorAll('.logout-link').forEach(function(el){
        el.addEventListener('click', function(e){
          e.preventDefault();
          const payload = new URLSearchParams({action:'logout'});
          tryPostForm(payload).then(()=>updateNavUser(null)).catch(()=>{ alert('Error de red: no se pudo conectar al endpoint de autenticación'); });
        });
      });

    } else {
      const guestHtml = `<li class="nav-item"><a href="#" class="nav-link open-login">Ingresar</a></li><li class="nav-item"><a href="#" class="nav-link open-register">Registrarse</a></li>`;
      navAuthWrap.innerHTML = guestHtml;
      mobileAuthNodes.forEach(n => n.innerHTML = guestHtml);
    }
  }

  function escapeHtml(s){ return String(s).replace(/[&<>"']/g, c=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#39;"}[c])); }

  // Helper: intentar varios endpoints comunes hasta que uno responda
  // Construir candidatos dinámicamente: rutas relativas y absolutas comunes en tu hosting
  (function(){
  const bases = ['', '/casabonsai'];
    const set = new Set();
    // Añadir rutas absolutas conocidas primero (evita problemas de resolución relativa en subcarpetas)
    set.add('/casabonsai/scr/actions/auth.php');
    set.add('/scr/actions/auth.php');
    // relativo (sin /) permite resolución respecto a la URL actual (se añade al final)
    // rutas absolutas y combinaciones
    bases.forEach(b => {
      const base = b.endsWith('/') ? b.slice(0,-1) : b;
      set.add(base + '/src/actions/auth.php');
      set.add(base + '/scr/actions/auth.php');
    });
    // intenta también origin + posible bases
    try {
      const origin = window.location.origin || '';
      bases.forEach(b => {
        const base = b.endsWith('/') ? b.slice(0,-1) : b;
        set.add(origin + base + '/src/actions/auth.php');
        set.add(origin + base + '/scr/actions/auth.php');
      });
    } catch(e){}
    // añadir la versión relativa al final para fallback
    // añadir ruta absoluta en raiz como fallback (evita resoluciones relativas como /index.html/scr/...)
    set.add('/scr/actions/auth.php');
    // export final array
    window.__AUTH_ENDPOINT_CANDIDATES = Array.from(set);
  })();
  // Determinar dinámicamente la base de la app.
  // Prioridad: window.BASE_URL (si fue definida), luego la ubicación del propio script (si se incluyó desde /casabonsai/...),
  // luego heurística por pathname.
  const detectAppBase = () => {
    try {
      // 1) si el desarrollador define window.BASE_URL explícitamente, úsala
      if (typeof window.BASE_URL === 'string' && window.BASE_URL.length) return window.BASE_URL.replace(/\/$/, '');

      // 2) intentar inferirla desde la URL del propio script <script src="...auth.js">
      // buscar el script que contiene "auth.js" en su src
      const scripts = document.getElementsByTagName('script');
      for (let i = 0; i < scripts.length; i++){
        const s = scripts[i].getAttribute('src') || '';
        if (s && s.indexOf('auth.js') !== -1){
          try {
            const scriptUrl = new URL(s, window.location.href);
            const path = scriptUrl.pathname; // p.ej. /casabonsai/scr/scripts/auth.js or /scr/scripts/auth.js
            const base = path.replace(/\/scr\/scripts\/auth(\.min)?\.js$/,'');
            if (base) return base.replace(/\/$/, '');
          } catch(e){ /* ignore */ }
        }
      }

      // 3) heurística: buscar 'casabonsai' en pathname
      const p = window.location.pathname || '';
      if (p.indexOf('/casabonsai') !== -1) return '/casabonsai';
      // Evitar devolver nombres de archivo (p.ej. /index.html) como base.
      // Si el primer segmento contiene un punto (.) lo consideramos un archivo y no una base.
      const parts = p.split('/').filter(Boolean);
      if (parts.length > 0) {
        const first = parts[0];
        if (first.indexOf('.') === -1 && first !== 'scr' && first !== 'pages') return '/' + first;
      }
    } catch (e){}
    return '';
  };
  const APP_BASE = detectAppBase();
  // Determinar un endpoint primario absoluto basado en el origin y la base detectada
  const AUTH_ENDPOINT_PRIMARY = (window.location.origin || '') + (APP_BASE || '') + '/scr/actions/auth.php';
  // Priorizar la ruta absoluta conocida y variantes comunes (incluyendo versiones con y sin base)
  const AUTH_ENDPOINT_CANDIDATES = window.__AUTH_ENDPOINT_CANDIDATES || [
    AUTH_ENDPOINT_PRIMARY,
    (window.location.origin || '') + '/scr/actions/auth.php',
    APP_BASE + '/scr/actions/auth.php',
    APP_BASE + '/src/actions/auth.php',
    '/scr/actions/auth.php'
  ];

  async function tryGetStatus(){
    // Intentar primero el endpoint primario absoluto
    try {
      console.debug('[auth] GET primary:', AUTH_ENDPOINT_PRIMARY);
      const res = await fetch(AUTH_ENDPOINT_PRIMARY + '?action=status', {credentials: 'same-origin'});
      if (res) return res;
    } catch(e){ console.warn('[auth] GET primary failed', AUTH_ENDPOINT_PRIMARY, e); }
    // Fallback: probar candidatos adicionales
    for (const p of AUTH_ENDPOINT_CANDIDATES){
      try {
        console.debug('[auth] GET candidate:', p);
        const res = await fetch(p + '?action=status', {credentials: 'same-origin'});
        if (res && res.ok) return res;
        if (res && res.status >= 400 && res.status < 600) return res;
      } catch(e){ console.warn('[auth] GET failed for', p, e); }
    }
    throw new Error('No endpoint auth disponible');
  }

  async function tryPostForm(form){
    // Intentar primero el endpoint primario absoluto
    try {
      console.debug('[auth] POST primary:', AUTH_ENDPOINT_PRIMARY);
      const res = await fetch(AUTH_ENDPOINT_PRIMARY, {method: 'POST', body: form, credentials: 'same-origin'});
      if (res) return res;
    } catch(e){ console.warn('[auth] POST primary failed', AUTH_ENDPOINT_PRIMARY, e); }
    for (const p of AUTH_ENDPOINT_CANDIDATES){
      try {
        console.debug('[auth] POST candidate:', p);
        const res = await fetch(p, {method: 'POST', body: form, credentials: 'same-origin'});
        if (res && (res.ok || res.status >= 400)) return res;
      } catch(e){ console.warn('[auth] POST failed for', p, e); }
    }
    throw new Error('No endpoint auth disponible');
  }

  // Check session status using tolerant endpoint discovery
  tryGetStatus().then(r=>r.json()).then(data=>{ if (data && data.ok) updateNavUser(data.user); }).catch(()=>{});

  // Login submit
  if (loginForm){ loginForm.addEventListener('submit', function(e){
    e.preventDefault();
    const form = new FormData(loginForm);
    form.append('action','login');
    tryPostForm(form).then(r=>r.json()).then(data=>{
      if (data && data.ok){
        // Respuesta de login: cerrar modal y actualizar UI (sin redirección automática)
        try { closeModal(loginModal); } catch(e){}
        updateNavUser(data.user || {user: document.getElementById('login-user')?.value});
      }
      else alert('Error: ' + (data.error||'Credenciales inválidas'));
    }).catch(()=>alert('Error de red: no se encontró el endpoint de autenticación'));
  }); }

  // Register submit
  if (registerForm){ registerForm.addEventListener('submit', function(e){
    e.preventDefault();
    const form = new FormData(registerForm);
    form.append('action','register');
    tryPostForm(form).then(r=>r.json()).then(data=>{
      if (data && data.ok){ closeModal(registerModal); alert('Registro correcto. Ahora puedes ingresar.'); }
      else alert('Error: ' + (data.error||'No se pudo registrar'));
    }).catch(()=>alert('Error de red: no se encontró el endpoint de autenticación'));
  }); }

});

// show/hide password toggle for register modal
document.addEventListener('click', function (e) {
  if (e.target && e.target.matches('.pwd-toggle')){
    var wrapper = e.target.closest('.input-with-toggle');
    var input = wrapper && wrapper.querySelector('input[type="password"]');
    if (!input) return;
    if (input.type === 'password'){ input.type = 'text'; e.target.setAttribute('aria-label','Ocultar contraseña'); e.target.textContent = '🙈'; }
    else { input.type = 'password'; e.target.setAttribute('aria-label','Mostrar contraseña'); e.target.textContent = '👁️'; }
  }
});
