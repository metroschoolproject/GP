<!-- Cookie Consent Banner -->
<style>
  .cookie-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 9999;
    background: linear-gradient(135deg, #fdf8f3 0%, #f5e8d9 100%);
    border-top: 2px solid #d4a574;
    padding: 20px 24px;
    box-shadow: 0 -4px 20px rgba(0, 0, 0, 0.1);
    font-family: 'Poppins', sans-serif;
    animation: slideUp 0.4s ease-out;
  }
  @keyframes slideUp {
    from { transform: translateY(100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
  }
  .cookie-banner-inner {
    max-width: 1200px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
  }
  .cookie-icon {
    font-size: 28px;
    flex-shrink: 0;
  }
  .cookie-text {
    flex: 1;
    min-width: 250px;
  }
  .cookie-text h3 {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    color: #5a3e2b;
    margin: 0 0 6px;
  }
  .cookie-text p {
    font-size: 13px;
    color: #7a6255;
    margin: 0;
    line-height: 1.5;
  }
  .cookie-text a {
    color: #b8860b;
    text-decoration: underline;
  }
  .cookie-buttons {
    display: flex;
    gap: 10px;
    flex-shrink: 0;
  }
  .cookie-btn {
    padding: 10px 24px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    transition: all 0.2s ease;
    font-family: 'Poppins', sans-serif;
  }
  .cookie-btn-accept {
    background: linear-gradient(135deg, #b8860b, #d4a574);
    color: #fff;
  }
  .cookie-btn-accept:hover {
    background: linear-gradient(135deg, #9a7209, #b8860b);
    transform: translateY(-1px);
  }
  .cookie-btn-reject {
    background: transparent;
    color: #7a6255;
    border: 1.5px solid #d4a574;
  }
  .cookie-btn-reject:hover {
    background: #f5e8d9;
  }
  @media (max-width: 640px) {
    .cookie-banner-inner {
      flex-direction: column;
      text-align: center;
    }
    .cookie-buttons {
      width: 100%;
      justify-content: center;
    }
  }
</style>

<div id="cookieBanner" class="cookie-banner" style="display:none;">
  <div class="cookie-banner-inner">
    <span class="cookie-icon">🍪</span>
    <div class="cookie-text">
      <h3>We Value Your Privacy</h3>
      <p>
        We use cookies to enhance your experience and analyze site traffic.
        You can choose to accept or reject non-essential cookies.
        <a href="#" onclick="return false;">Learn more</a>
      </p>
    </div>
    <div class="cookie-buttons">
      <button class="cookie-btn cookie-btn-reject" onclick="setCookieConsent('rejected')">Reject</button>
      <button class="cookie-btn cookie-btn-accept" onclick="setCookieConsent('accepted')">Accept All</button>
    </div>
  </div>
</div>

<script>
(function() {
  // Check if consent already given
  var consent = getCookie('cookie_consent');
  if (!consent) {
    document.getElementById('cookieBanner').style.display = 'block';
  }

  // If consent was already accepted, load GA
  if (consent === 'accepted') {
    loadGoogleAnalytics();
  }
})();

function setCookieConsent(value) {
  // Set cookie for 365 days
  var expires = new Date();
  expires.setTime(expires.getTime() + (365 * 24 * 60 * 60 * 1000));
  document.cookie = 'cookie_consent=' + value + ';expires=' + expires.toUTCString() + ';path=/;SameSite=Lax';

  // Hide banner
  document.getElementById('cookieBanner').style.display = 'none';

  // If accepted, load GA
  if (value === 'accepted') {
    loadGoogleAnalytics();
  }
}

function loadGoogleAnalytics() {
  // Only load if not already loaded
  if (document.querySelector('script[src*="googletagmanager.com/gtag"]')) return;

  var script = document.createElement('script');
  script.async = true;
  script.src = 'https://www.googletagmanager.com/gtag/js?id=G-B36CEYW68L';
  document.head.appendChild(script);

  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', 'G-B36CEYW68L');
}

function getCookie(name) {
  var nameEQ = name + '=';
  var ca = document.cookie.split(';');
  for(var i=0;i < ca.length;i++) {
    var c = ca[i];
    while (c.charAt(0)==' ') c = c.substring(1);
    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length);
  }
  return null;
}
</script>
