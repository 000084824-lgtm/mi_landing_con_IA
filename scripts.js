// scripts.js — small helpers for accessibility and reduced-motion
document.documentElement.classList.add('js-enabled');
(function(){
  try{
    if(window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches){
      document.documentElement.classList.add('reduced-motion');
    }
    // Make .btn elements activate on Space as well as Enter (for non-button anchors)
    var btns = document.querySelectorAll('.btn');
    btns.forEach(function(b){
      b.addEventListener('keydown', function(e){
        if(e.key === ' ' || e.key === 'Spacebar'){
          e.preventDefault();
          b.click();
        }
      });
    });
  }catch(e){console.warn(e)}
})();
