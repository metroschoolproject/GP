<?php if (empty($GLOBALS['gpSharedFooterRendered'])): $GLOBALS['gpSharedFooterRendered'] = true; ?>
<style>
footer.gp-footer{display:none!important}
.gp-shared-footer{position:relative;z-index:2;overflow:visible;width:100%;margin-top:0;padding:clamp(58px,5vw,74px) 24px 12px 0;background:#2A1710;color:#fcf8f5;font-family:'Playfair Display',Georgia,serif}
.gp-shared-footer::before{content:"";position:absolute;top:-38px;left:0;right:0;height:42px;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='260' height='42' viewBox='0 0 260 42'%3E%3Cpath fill='%232A1710' d='M0 42h260V24C218 0 174 44 130 24 86 4 42 44 0 24v18Z'/%3E%3C/svg%3E") repeat-x bottom left/260px 42px;pointer-events:none}
.gp-shared-footer-inner{width:min(100%,1180px);display:grid;grid-template-columns:minmax(260px,.82fr) minmax(460px,1fr);gap:clamp(34px,7vw,110px);align-content:center;align-items:center;justify-items:start;text-align:left;margin:0 auto}
.gp-shared-footer-left{display:grid;justify-items:start}
.gp-shared-footer-nav{display:flex;align-items:center;justify-content:flex-start;gap:clamp(28px,7vw,92px);margin-top:0;font-family:'Playfair Display',Georgia,serif}
.gp-shared-footer-nav a{color:rgba(252,248,245,.84);font-size:12px;font-weight:700;letter-spacing:.22em;text-transform:uppercase;text-decoration:none;transition:color 220ms ease,opacity 220ms ease}
.gp-shared-footer-nav a:hover{color:#fcf8f5;opacity:1}
.gp-shared-footer-contact-card{text-align:left;margin-top:clamp(10px,1.4vw,16px)}
.gp-shared-footer-contact-card h2{margin:0;color:#fcf8f5;font-size:clamp(1.7rem,4vw,3rem);font-weight:700;line-height:1.15}
.gp-shared-footer-contact-card p{margin:4px 0 0;color:#aaa;font-family:'Poppins',system-ui,sans-serif;font-size:13px}
.gp-shared-footer-contact-card h2,.gp-shared-footer-contact-card p{transition:opacity .35s ease,transform .35s ease}
.gp-shared-footer-contact-card .text-changing{opacity:0;transform:translateY(12px)}
.gp-shared-footer-icons{display:flex;align-items:center;justify-content:flex-start;gap:12px;margin-top:12px}
.gp-shared-social{display:grid;place-items:center;width:42px;height:42px;border:1px solid rgba(252,248,245,.16);border-radius:10px;background:rgba(252,248,245,.05);color:#999;font-family:'Poppins',system-ui,sans-serif;font-size:12px;font-weight:700;text-decoration:none;transition:background .3s ease,color .3s ease,border-color .3s ease}
.gp-shared-social svg{width:21px;height:21px}
.gp-shared-social:hover,.gp-shared-social.active{background:rgba(252,248,245,.16);border-color:rgba(252,248,245,.4);color:#fcf8f5}
.gp-shared-footer-right-space{justify-self:start;display:grid;grid-template-columns:minmax(130px,.72fr) minmax(220px,1fr);gap:clamp(28px,5vw,58px);padding-left:0}
.gp-shared-footer-col+.gp-shared-footer-col{padding-left:clamp(24px,4vw,46px);border-left:1px solid rgba(252,248,245,.16)}
.gp-shared-footer-title{margin:0 0 18px;color:#fcf8f5;font-size:15px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
.gp-shared-footer-links,.gp-shared-footer-contact-list{display:grid;gap:11px;margin:0;padding:0;list-style:none}
.gp-shared-footer-links a{color:rgba(252,248,245,.78);font-family:'Poppins',system-ui,sans-serif;font-size:13px;text-decoration:none;transition:color .2s ease,transform .2s ease}
.gp-shared-footer-links a:hover{color:#fcf8f5;transform:translateX(2px)}
.gp-shared-footer-contact-list li{display:grid;grid-template-columns:22px 1fr;align-items:center;gap:12px;color:rgba(252,248,245,.78);font-family:'Poppins',system-ui,sans-serif;font-size:13px;line-height:1.55}
.gp-shared-footer-contact-list svg{width:18px;height:18px;color:rgba(252,248,245,.86);stroke-width:1.7}
.gp-shared-footer-copy{grid-column:1/-1;margin:clamp(10px,1.4vw,16px) 0 0;color:rgba(252,248,245,.78);font-family:'Poppins',system-ui,sans-serif;font-size:11px;letter-spacing:.04em}
@media(max-width:900px){.gp-shared-footer{padding:56px 18px 16px}.gp-shared-footer-inner,.gp-shared-footer-right-space{grid-template-columns:1fr}.gp-shared-footer-nav{gap:18px;flex-wrap:wrap}.gp-shared-footer-col+.gp-shared-footer-col{padding-left:0;border-left:0}.gp-shared-footer-copy{grid-column:auto}}
</style>

<footer class="gp-shared-footer" aria-label="Website Footer">
  <div class="gp-shared-footer-inner">
    <div class="gp-shared-footer-left">
      <nav class="gp-shared-footer-nav" aria-label="Footer navigation">
        <a href="<?= URLROOT ?>/customerServices/service">Services</a>
        <a href="<?= URLROOT ?>/main/index#story">Our Story</a>
        <a href="<?= URLROOT ?>/main/index#reviews">Review</a>
      </nav>
      <div class="gp-shared-footer-contact-card">
        <h2 data-gp-shared-footer-text>GPromise Wedding</h2>
        <p data-gp-shared-footer-label>Facebook</p>
        <div class="gp-shared-footer-icons" aria-label="Social links">
          <a class="gp-shared-social active" href="#" aria-label="Facebook" data-text="GPromise Wedding" data-label="Facebook">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14 8.5h2.2V5.2c-.4-.1-1.7-.2-3.1-.2-3.1 0-5.2 1.9-5.2 5.4v3H4.5V17h3.4v7h4.1v-7h3.3l.5-3.6H12v-2.6c0-1 .3-2.3 2-2.3Z"/></svg>
          </a>
          <a class="gp-shared-social" href="#" aria-label="Instagram" data-text="@gpromise_wedding" data-label="Instagram">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><rect x="4" y="4" width="16" height="16" rx="5"/><circle cx="12" cy="12" r="3.2"/><circle cx="16.8" cy="7.2" r=".8" fill="currentColor" stroke="none"/></svg>
          </a>
          <a class="gp-shared-social" href="<?= URLROOT ?>/main/index#top" aria-label="Website" data-text="GPromise.com" data-label="Website">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3c2.3 2.5 3.5 5.5 3.5 9S14.3 18.5 12 21c-2.3-2.5-3.5-5.5-3.5-9S9.7 5.5 12 3Z"/></svg>
          </a>
        </div>
      </div>
    </div>

    <div class="gp-shared-footer-right-space" aria-label="Footer quick links and contact">
      <div class="gp-shared-footer-col">
        <h3 class="gp-shared-footer-title">Quick Links</h3>
        <ul class="gp-shared-footer-links">
          <li><a href="<?= URLROOT ?>/main/index#top">Home</a></li>
          <li><a href="<?= URLROOT ?>/customerServices/service">Services</a></li>
          <li><a href="<?= URLROOT ?>/customerServices/packages">Wedding Packages</a></li>
          <li><a href="<?= URLROOT ?>/users/auth?type=supplier">Suppliers</a></li>
          <li><a href="<?= URLROOT ?>/main/index#contact">Contact Us</a></li>
        </ul>
      </div>
      <div class="gp-shared-footer-col">
        <h3 class="gp-shared-footer-title">Contact</h3>
        <ul class="gp-shared-footer-contact-list">
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 21s7-4.9 7-11a7 7 0 1 0-14 0c0 6.1 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/></svg>
            <span>Yangon, Myanmar</span>
          </li>
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M4 6h16v12H4z"/><path d="m4 7 8 6 8-6"/></svg>
            <span>support@gpromise.com</span>
          </li>
          <li>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.9 19.9 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.9 19.9 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 2 .7 2.9a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.2-1.2a2 2 0 0 1 2.1-.5c.9.3 1.9.6 2.9.7a2 2 0 0 1 1.7 2Z"/></svg>
            <span>+95 XXX XXX XXXX</span>
          </li>
        </ul>
      </div>
    </div>
    <p class="gp-shared-footer-copy">&copy; <span data-gp-shared-footer-year><?= date('Y') ?></span> GPromise Wedding. All rights reserved.</p>
  </div>
</footer>

<script>
(function(){
  const footer=document.querySelector('.gp-shared-footer');
  if(!footer) return;
  const year=footer.querySelector('[data-gp-shared-footer-year]');
  if(year) year.textContent=new Date().getFullYear();

  const footerButtons=footer.querySelectorAll('.gp-shared-social');
  const footerText=footer.querySelector('[data-gp-shared-footer-text]');
  const footerLabel=footer.querySelector('[data-gp-shared-footer-label]');
  footerButtons.forEach(btn=>{
    btn.addEventListener('mouseenter',()=>{
      footerButtons.forEach(item=>item.classList.remove('active'));
      btn.classList.add('active');
      if(!footerText || !footerLabel) return;
      footerText.classList.add('text-changing');
      footerLabel.classList.add('text-changing');
      setTimeout(()=>{
        footerText.textContent=btn.dataset.text || footerText.textContent;
        footerLabel.textContent=btn.dataset.label || footerLabel.textContent;
        footerText.classList.remove('text-changing');
        footerLabel.classList.remove('text-changing');
      },180);
    });
  });
})();
</script>
<?php endif; ?>
