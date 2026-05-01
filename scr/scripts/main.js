// main.js - comportamiento del carrusel (ligero) para inicio.html

document.addEventListener('DOMContentLoaded', function(){
  const carousel = document.getElementById('miCarrusel');
  if(!carousel) return;

  const items = Array.from(carousel.querySelectorAll('.carousel-item'));
  const indicators = Array.from(carousel.querySelectorAll('.carousel-indicators button'));
  const btnPrev = carousel.querySelector('.carousel-control-prev');
  const btnNext = carousel.querySelector('.carousel-control-next');

  let idx = items.findIndex(i => i.classList.contains('active'));
  if(idx < 0) idx = 0;

  // Asegura atributos ARIA básicos
  items.forEach((it,i)=>{
    it.setAttribute('role','group');
    it.setAttribute('aria-roledescription','slide');
    it.setAttribute('aria-label', `${i+1} de ${items.length}`);
    it.setAttribute('aria-hidden', i===idx ? 'false' : 'true');
  });

  indicators.forEach((btn,i)=>{
    btn.setAttribute('aria-current', i===idx ? 'true' : 'false');
  });

  function show(n){
    n = (n + items.length) % items.length;
    items.forEach((it,i)=>{
      it.classList.toggle('active', i===n);
      it.setAttribute('aria-hidden', i===n ? 'false' : 'true');
    });
    indicators.forEach((btn,i)=>{
      btn.classList.toggle('active', i===n);
      btn.setAttribute('aria-current', i===n ? 'true' : 'false');
    });
    idx = n;
  }

  // listeners
  indicators.forEach((btn,i)=> btn.addEventListener('click', (e)=>{ e.preventDefault(); show(i); }));
  if(btnPrev) btnPrev.addEventListener('click', (e)=>{ e.preventDefault(); show(idx-1); });
  if(btnNext) btnNext.addEventListener('click', (e)=>{ e.preventDefault(); show(idx+1); });

  // autoplay con pausa al hover/focus
  let interval = setInterval(()=> show(idx+1), 5000);
  carousel.addEventListener('mouseenter', ()=> clearInterval(interval));
  carousel.addEventListener('mouseleave', ()=> { clearInterval(interval); interval = setInterval(()=> show(idx+1), 5000); });

  // focus para control por teclado
  carousel.setAttribute('tabindex','0');
  carousel.addEventListener('focusin', ()=> clearInterval(interval));
  carousel.addEventListener('focusout', ()=> { clearInterval(interval); interval = setInterval(()=> show(idx+1), 5000); });
  carousel.addEventListener('keydown', (e)=>{
    if(e.key === 'ArrowLeft') { show(idx-1); }
    if(e.key === 'ArrowRight') { show(idx+1); }
  });

});
