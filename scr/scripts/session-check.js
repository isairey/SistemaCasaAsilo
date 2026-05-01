// Comprueba el estado de sesión desde cualquier página (usa ruta absoluta a la acción de auth)
(function(){
  const STATUS_URL = (window.location.origin || '') + '/casabonsai/scr/actions/auth.php?action=status';
  async function check(){
    try{
      const res = await fetch(STATUS_URL, {credentials:'same-origin'});
      if (!res) return;
      const data = await res.json().catch(()=>null);
  const nav = document.getElementById('nav-auth-wrap');
  const mobileAuthNodes = document.querySelectorAll('.nav-auth-mobile');
  if (!nav) return;
  if (data && data.ok && data.user){
        // Muestra imagen de perfil con enlace a la página de perfil y el enlace de cerrar sesión
        nav.innerHTML = `
          <li class="nav-item">
            <a class="nav-link nav-profile" href="/casabonsai/scr/pages/perfil.php" title="Ver perfil" style="display:flex;align-items:center;gap:8px">
              <img src="/casabonsai/scr/resources/images/perfil.png" alt="Perfil" style="width:32px;height:32px;border-radius:50%">
              <span class="nav-user">Hola, ${escapeHtml(data.user.user||data.user)}</span>
            </a>
          </li>
          <li class="nav-item"><a href="#" id="logout-link" class="nav-link">Cerrar sesión</a></li>
        `;
        const logout = document.getElementById('logout-link');
        if (logout) logout.addEventListener('click', function(e){
          e.preventDefault();
          fetch((window.location.origin||'') + '/casabonsai/scr/actions/auth.php', {method:'POST', body: new URLSearchParams({action:'logout'}), credentials:'same-origin'}).then(()=>location.reload());
        });
        // Also populate mobile auth containers if present
        const mobileHtml = `
          <li class="nav-item"><a class="nav-link nav-profile" href="/casabonsai/scr/pages/perfil.php" title="Ver perfil"><img src="/casabonsai/scr/resources/images/perfil.png" alt="Perfil" style="width:36px;height:36px;border-radius:50%;vertical-align:middle;margin-right:10px"> <span class="nav-user">${escapeHtml(data.user.user||data.user)}</span></a></li>
          <li class="nav-item"><a href="#" class="nav-link" id="logout-link-mobile">Cerrar sesión</a></li>
        `;
        mobileAuthNodes.forEach(n => n.innerHTML = mobileHtml);
        const logoutMobile = document.getElementById('logout-link-mobile');
        if (logoutMobile) logoutMobile.addEventListener('click', function(e){ e.preventDefault(); fetch((window.location.origin||'') + '/casabonsai/scr/actions/auth.php', {method:'POST', body: new URLSearchParams({action:'logout'}), credentials:'same-origin'}).then(()=>location.reload()); });
      } else {
        nav.innerHTML = `<li class="nav-item"><a href="#" class="nav-link open-login">Ingresar</a></li><li class="nav-item"><a href="#" class="nav-link open-register">Registrarse</a></li>`;
        mobileAuthNodes.forEach(n => n.innerHTML = `<li class="nav-item"><a href="#" class="nav-link open-login">Ingresar</a></li><li class="nav-item"><a href="#" class="nav-link open-register">Registrarse</a></li>`);
      }
    }catch(e){
      // noop
    }
  }
  function escapeHtml(s){ return String(s||'').replace(/[&<>'"]/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c])); }
  // run on DOM ready
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', check); else check();
})();
