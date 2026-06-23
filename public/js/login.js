/* PASSWORD STRENGTH */

const passwordInput = document.getElementById('passwordInput');
const strengthBox = document.querySelector('.strength-meter');
const strengthText = document.getElementById('strengthText');
const segments = ['seg1','seg2','seg3','seg4'];

passwordInput.addEventListener('input', updateStrength);

function resetStrength() {
  segments.forEach(id => {
    document.getElementById(id).classList.remove('active');
  });

  strengthText.textContent = 'Weak';
}

function toggleStrengthUI(show) {
  if (!strengthBox) return;

  strengthBox.style.opacity = show ? '1' : '0';
  strengthBox.style.visibility = show ? 'visible' : 'hidden';
}



function updateRequirements(value) {
  const reqs = {
    reqLength: value.length >= 8,
    reqUpper:  /[A-Z]/.test(value),
    reqLower:  /[a-z]/.test(value),
    reqNumber: /[0-9]/.test(value),
    reqSymbol: /[^A-Za-z0-9]/.test(value),
  };
  Object.entries(reqs).forEach(([id, met]) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle('met', met);
    el.textContent = el.dataset.label || '';
  });
}

function resetRequirements() {
  ['reqLength','reqUpper','reqLower','reqNumber','reqSymbol'].forEach(id => {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.remove('met');
    el.textContent = el.dataset.label || '';
  });
}

function updateStrength() {
  if (isSignIn || passwordInput.value.trim() === '') {
    hideStrengthUI();
    return;
  }

  showStrengthUI();

  const value = passwordInput.value;

  updateRequirements(value);

  let score = 0;

  if (value.length >= 8) score++;
  if (/[A-Z]/.test(value)) score++;
  if (/[0-9]/.test(value)) score++;
  if (/[^A-Za-z0-9]/.test(value)) score++;

  ['seg1','seg2','seg3','seg4']
    .forEach((id,index)=>{
      const el = document.getElementById(id);
      el.classList.toggle('active', index < score);
      el.setAttribute('data-level', score);
    });

  const text = document.getElementById('strengthText');

  if(score <= 1) text.textContent = 'Weak';
  else if(score === 2) text.textContent = 'Fair';
  else if(score === 3) text.textContent = 'Good';
  else text.textContent = 'Strong';
}




function hideStrengthUI() {
  const strengthBox = document.querySelector('.strength-meter');
  if (strengthBox) {
    strengthBox.style.opacity = '0';
    strengthBox.style.visibility = 'hidden';
  }
  resetStrength();
  resetRequirements();
}

function showStrengthUI() {
  const strengthBox = document.querySelector('.strength-meter');
  if (strengthBox) {
    strengthBox.style.opacity = '1';
    strengthBox.style.visibility = 'visible';
  }
}
    //   passwordInput.addEventListener('input', updateStrength);

    //   function updateStrength() {
    //     const value = passwordInput.value;

    //     let score = 0;

    //     if (value.length >= 6) score++;
    //     if (/[A-Z]/.test(value)) score++;
    //     if (/[0-9]/.test(value)) score++;
    //     if (/[^A-Za-z0-9]/.test(value)) score++;

    //     const segments = ['seg1','seg2','seg3','seg4'];

    //     segments.forEach((id,index)=>{
    //       document.getElementById(id)
    //         .classList.toggle('active', index < score);
    //     });

    //     const text = document.getElementById('strengthText');

    //     if(score <= 1) text.textContent = 'Weak';
    //     else if(score === 2) text.textContent = 'Fair';
    //     else if(score === 3) text.textContent = 'Good';
    //     else text.textContent = 'Strong';
    //   }

      const $ = id => document.getElementById(id);
      const particles = [];
      let isSignIn = true;
      let isAnimating = false;

      const canvas = $('sparkleCanvas');
      const ctx = canvas.getContext('2d');
      const decorImg = $('decorLine');
      const mainH = $('mainHeading');
      const subH = $('subHeading');
      const mainBtn = $('mainBtn');
      const divider = $('divider');
      const fieldGroup = $('fieldGroup');

      function icon(name, btnId) {
        return $(`${name}Template`).innerHTML.replace(/{id}/g, btnId);
      }

      function toggleVisibility(inputId, btnId) {
        const input = $(inputId);
        const btn = $(btnId);
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.innerHTML = icon(show ? 'eyeClosed' : 'eyeOpen', btnId);
        btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
        passwordInput.autocomplete = isSignIn
        ? 'current-password'
        : 'new-password';
      }

      function setModeText(el, signup) {
        el.textContent = el.dataset[signup ? 'signup' : 'signin'];
      }

      function allFields() {
        return Array.from(document.querySelectorAll('#fieldGroup .field-wrap'));
      }

      function modeFields(signup) {
        const mode = signup ? 'signup' : 'signin';
        return allFields()
          .filter(el => el.dataset.modes.split(' ').includes(mode))
          .map(el => el.id);
      }

      function resizeCanvas() {
        canvas.width = canvas.parentElement.offsetWidth;
        canvas.height = canvas.parentElement.offsetHeight;
      }
      resizeCanvas();
      window.addEventListener('resize', resizeCanvas);

      function buildSpans(el, text = el.textContent, hidden) {
        el.innerHTML = '';
        for (let i = 0; i < text.length; i++) {
          const s = document.createElement('span');
          s.className = 'char';
          s.textContent = text[i];
          if (!hidden) { s.style.opacity='1'; s.style.filter='blur(0px)'; s.style.transform='scale(1)'; }
          el.appendChild(s);
        }
      }

      function getCharRects(el) {
        const cardRect = canvas.getBoundingClientRect();
        return Array.from(el.querySelectorAll('.char')).map(s => {
          const r = s.getBoundingClientRect();
          return { el: s, cx: r.left - cardRect.left + r.width/2, cy: r.top - cardRect.top + r.height/2 };
        });
      }

      const WHITE_SHADES = [
        ['rgba(255,182,193,1)', 'rgba(255,105,180,0.88)', 'rgba(255,20,147,0.92)', 'rgba(219,112,147,0.80)', 'rgba(255,192,203,0.65)']
      ];

      class Particle {
        constructor(x, y) {
          this.x=x; this.y=y;
          const angle=Math.random()*Math.PI*2, speed=0.3+Math.random()*1.6;
          this.vx=Math.cos(angle)*speed; this.vy=Math.sin(angle)*speed-0.5;
          this.life=1; this.decay=0.022+Math.random()*0.028;
          this.size=0.25+Math.random()*0.75;
          this.color=WHITE_SHADES[Math.floor(Math.random()*WHITE_SHADES.length)];
          this.twinkle=Math.random()>0.4; this.phase=Math.random()*Math.PI*2;
        }
        update() {
          this.x+=this.vx; this.y+=this.vy;
          this.vy+=0.03; this.vx*=0.97;
          this.life-=this.decay; this.phase+=0.22;
        }
        draw(ctx) {
          const a=Math.max(0,this.life), f=this.twinkle?0.5+0.5*Math.sin(this.phase):1;
          ctx.globalAlpha=a*f; ctx.fillStyle=this.color;
          ctx.beginPath(); ctx.arc(this.x,this.y,this.size,0,Math.PI*2); ctx.fill();
          if(this.size>0.55){ctx.globalAlpha=a*f*0.2;ctx.beginPath();ctx.arc(this.x,this.y,this.size*2.2,0,Math.PI*2);ctx.fill();}
        }
      }

      function emitParticles(cx, cy, count) {
        for(let i=0;i<count;i++) particles.push(new Particle(cx,cy));
      }

      function loop() {
        ctx.clearRect(0,0,canvas.width,canvas.height);
        for(let i=particles.length-1;i>=0;i--){
          particles[i].update(); particles[i].draw(ctx);
          if(particles[i].life<=0) particles.splice(i,1);
        }
        requestAnimationFrame(loop);
      }
      loop();

      function dissolveOut(el, onDone) {
        const rects = getCharRects(el);
        rects.forEach(({el:c,cx,cy},i) => {
          setTimeout(()=>{
            if(c.textContent.trim()) emitParticles(cx,cy,10);
            c.style.transition='opacity 0.4s ease,filter 0.4s ease,transform 0.4s ease';
            c.style.opacity='0'; c.style.filter='blur(6px)'; c.style.transform='scale(0.87)';
          }, i*18);
        });
        setTimeout(onDone, rects.length*18+200);
      }

      function assembleIn(el) {
        const rects = getCharRects(el);
        rects.forEach(({el:c,cx,cy},i) => {
          setTimeout(()=>{
            if(c.textContent.trim()) emitParticles(cx,cy,6);
            c.style.transition='opacity 0.48s cubic-bezier(0,0,0.2,1),filter 0.48s cubic-bezier(0,0,0.2,1),transform 0.48s cubic-bezier(0,0,0.2,1)';
            c.style.opacity='1'; c.style.filter='blur(0px)'; c.style.transform='scale(1)';
          }, i*20);
        });
        return rects.length*42+500;
      }

      function hideAllFields(onDone) {
        allFields().forEach((el, i) => {
          setTimeout(() => {
            el.style.transition = `max-height 0.45s cubic-bezier(0.4,0,0.2,1) ${i*60}ms, opacity 0.38s cubic-bezier(0.4,0,0.2,1) ${i*60}ms, transform 0.38s cubic-bezier(0.4,0,0.2,1) ${i*60}ms`;
            el.style.maxHeight = '0';
            el.style.opacity = '0';
            el.style.transform = 'translateY(10px)';
            el.classList.remove('visible');
          }, i*60);
        });
        setTimeout(onDone, allFields().length*60+480);
      }

      function showFields(ids) {
        ids.forEach((id, i) => {
          setTimeout(() => {
            const el = $(id);
            el.style.maxHeight = el.dataset.height;
            el.style.opacity = '1';
            el.style.transform = 'translateY(0)';
            el.style.marginBottom = el.dataset.margin;
          }, i * 90);
        });
      }

      function setupInitialFields(ids) {
        allFields().forEach(el => {
          const visible = ids.includes(el.id);
          el.style.transition = 'none';
          el.style.maxHeight = visible ? el.dataset.height : '0';
          el.style.opacity = visible ? '1' : '0';
          el.style.transform = visible ? 'translateY(0)' : 'translateY(10px)';
          el.style.marginBottom = visible ? el.dataset.margin : '0';
          requestAnimationFrame(() => requestAnimationFrame(() => { el.style.transition = ''; }));
        });
      }

      function switchDecorImage(src) {
        decorImg.style.transition =
        "opacity 3s cubic-bezier(0.22, 1, 0.36, 1), transform 3s cubic-bezier(0.22, 1, 0.36, 1)";
        decorImg.style.opacity = "0";
        decorImg.style.transform = "scale(0.9)";

        setTimeout(() => {
          decorImg.onerror = () => console.error("Image failed:", src);
          decorImg.onload = () => {
            decorImg.style.opacity = "1";
            decorImg.style.transform = "scale(1)";
          };
          decorImg.src = src + "?t=" + Date.now();
        }, 1000);
      }

      function setDecorMode(isSignup) {
        decorImg.classList.toggle('signup-mode', isSignup);
      }

      buildSpans(mainH, mainH.textContent, false);
      buildSpans(subH, subH.textContent, false);
      setupInitialFields(modeFields(false));
      fieldGroup.classList.add('mb-2');
      mainBtn.classList.remove('hidden');
      divider.classList.remove('hidden');

      $('toggleBtn').addEventListener('click', () => {
        if (isAnimating) return;
        isAnimating = true;

        hideStrengthUI();

        ['passwordInput', 'confirmInput'].forEach(inputId => {
          const input = $(inputId);
          const btnId = inputId === 'passwordInput' ? 'eyePassword' : 'eyeConfirm';
          if (input) input.type = 'password';
          if ($(btnId)) $(btnId).innerHTML = icon('eyeOpen', btnId);
        });

        const goingToSignup = isSignIn;
        const nextFields = modeFields(goingToSignup);

        setDecorMode(goingToSignup);
        switchDecorImage('signInDecorLine.png');

        fieldGroup.classList.toggle('mb-5', goingToSignup);
        fieldGroup.classList.toggle('mb-2', !goingToSignup);

        mainBtn.classList.add('hidden');
        divider.classList.add('hidden');

        dissolveOut(mainH, ()=>{});
        dissolveOut(subH, ()=>{
        isSignIn = !isSignIn;

        document.querySelectorAll('#fieldGroup input').forEach(input => input.value = '');
        // hideStrengthUI();

        const isSignup = !isSignIn;

        setModeText(mainH, isSignup);
        setModeText(subH, isSignup);
        buildSpans(mainH, mainH.textContent, true);
        buildSpans(subH, subH.textContent, true);

        setTimeout(()=>{
            const t1 = assembleIn(mainH);
            const t2 = assembleIn(subH);
            setTimeout(()=>{ isAnimating = false; }, Math.max(t1,t2));
        }, 80);
        });

        hideAllFields(()=>{
          showFields(nextFields);
          setTimeout(()=>{
            mainBtn.classList.remove('hidden');
            divider.classList.remove('hidden');
          }, nextFields.length*90+200);
        });

        setModeText($('btnText'), goingToSignup);
        setModeText($('togglePrompt'), goingToSignup);
        setModeText($('toggleBtn'), goingToSignup);
      });
