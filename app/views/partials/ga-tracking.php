<!-- Google Analytics (GA4) — only fires if cookie_consent=accepted (checked by cookie-consent.php JS) -->
<script>
(function() {
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

  // Only load GA if user has accepted cookies
  if (getCookie('cookie_consent') === 'accepted') {
    var script = document.createElement('script');
    script.async = true;
    script.src = 'https://www.googletagmanager.com/gtag/js?id=G-B36CEYW68L';
    document.head.appendChild(script);

    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    gtag('config', 'G-B36CEYW68L');
  }
})();
</script>
