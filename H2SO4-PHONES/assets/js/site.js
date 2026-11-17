(function(){
  const menuButton=document.getElementById('mobile-menu-toggle');
  const mobileMenu=document.getElementById('mobile-menu');
  if(menuButton&&mobileMenu){menuButton.addEventListener('click',()=>mobileMenu.classList.toggle('open'));}
  document.querySelectorAll('[data-qty-step]').forEach(btn=>{
    btn.addEventListener('click',()=>{
      const field=document.getElementById(btn.dataset.qtyTarget);if(!field)return;
      const max=Number(field.max||99),min=Number(field.min||1);
      field.value=Math.max(min,Math.min(max,Number(field.value||1)+Number(btn.dataset.qtyStep)));
    });
  });
  document.querySelectorAll('[data-confirm]').forEach(btn=>btn.addEventListener('click',e=>{
    if(!confirm(btn.dataset.confirm))e.preventDefault();
  }));
  const slides=[...document.querySelectorAll('.hero-slide')];const dots=[...document.querySelectorAll('.hero-dot')];
  if(slides.length>1){let active=0;
    function show(i){active=i;slides.forEach((s,j)=>s.classList.toggle('is-active',j===i));dots.forEach((d,j)=>d.classList.toggle('active',j===i));}
    dots.forEach((d,i)=>d.addEventListener('click',()=>show(i)));
    if(!window.matchMedia('(prefers-reduced-motion: reduce)').matches)setInterval(()=>show((active+1)%slides.length),6500);
  }
  document.querySelectorAll('.alert').forEach(el=>{el.addEventListener('click',()=>el.remove());});
  document.querySelectorAll('form[data-auto-submit] select').forEach(input=>input.addEventListener('change',()=>input.form.submit()));
})();
