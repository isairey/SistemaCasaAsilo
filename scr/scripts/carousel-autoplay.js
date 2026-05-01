/* carousel-autoplay.js
   Simple JS to add autoplay to a radio-driven CSS-only slider.
   - cambia al siguiente radio cada INTERVAL ms
   - pausa en mouseenter / focusin / visibilitychange
   - respeta cambios manuales del usuario
*/
(function(){
  const INTERVAL = 5000; // ms por slide
  const radios = Array.from(document.querySelectorAll('input[name="slider"]'));
  if(!radios.length) return;

  let current = radios.findIndex(r => r.checked);
  if(current < 0) current = 0;
  let paused = false;

  function next(){
    if(paused) return;
    current = (current + 1) % radios.length;
    radios[current].checked = true;
    radios[current].dispatchEvent(new Event('change', {bubbles:true}));
  }

  const timer = setInterval(next, INTERVAL);

  // Pause/resume on hover and focus inside the carousel container
  const container = document.querySelector('.hero-inner') || document.querySelector('#miCarrusel');
  if(container){
    container.addEventListener('mouseenter', ()=> paused = true);
    container.addEventListener('mouseleave', ()=> paused = false);
    container.addEventListener('focusin', ()=> paused = true);
    container.addEventListener('focusout', ()=> paused = false);
  }

  // Pause when page not visible
  document.addEventListener('visibilitychange', ()=>{
    paused = document.hidden;
  });

  // If user manually changes radio, sync current index to it
  radios.forEach((r, idx)=> r.addEventListener('change', ()=> { if(r.checked) current = idx; }));

  // Cleanup on unload
  window.addEventListener('beforeunload', ()=> clearInterval(timer));
})();
