<?php

define('APPNAME', 'Golden Promise');
define('URLROOT', 'http://localhost/GP');
define('IMG_ROOT', 'http://localhost/GP/public');
define('NETWORK_URLROOT', 'http://10.247.249.2/GP');
define('NETWORK_IMG_ROOT', 'http://10.247.249.2/GP/public');
define('APPROOT', dirname(dirname(__FILE__)));
// define('VENDOR_AUTOLOAD','');

define('DB_HOST', 'localhost;port=3307');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'goldenpromise');

define('GOOGLE_CLIENT_ID', '453132170855-j9npo21t5tr7n6c874ml66ta1l96km1j.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', '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');
define('GOOGLE_REDIRECT_URI', 'http://localhost/GP/users/googleCallback');

define('FACEBOOK_APP_ID', '26938920369127434');
define('FACEBOOK_APP_SECRET', '0b24838fe93fdae640f11a882f1a298c');
define('FACEBOOK_REDIRECT_URI', 'http://localhost/GP/users/facebookCallback');



define('GEMINI_API_KEY', 'AQ.Ab8RN6K4xV4_5A_Gq4vIRxK4gH0hnTZgyseqwUjKbyCgXWqNlg');


// Email Configuration
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_USERNAME', 'hsumyatm7308@gmail.com');
define('MAIL_PASSWORD', 'app-password-here');
define('MAIL_ENCRYPTION', 'tls');
define('MAIL_PORT', 587);

// Payment Gateway (2C2P - MM QR & Card Payments)
// Sandbox docs: https://developer.2c2p.com/docs/sandbox-setup
define('PAYMENT_GATEWAY_SANDBOX', true); // Switch to false only with production credentials
define('PAYMENT_GATEWAY_SECRET', '72B8F060B3B923E580411200068A764610F61034AE729AB9EF20CAFF93AFA1B9'); // Sandbox/production Secret Key
define('MERCHANT_ID', 'JT02'); // Sandbox/production Merchant ID
define('PAYMENT_GATEWAY_CURRENCY', 'MMK'); // ISO 4217 alpha code for Myanmar Kyat
define('PAYMENT_GATEWAY_CARD_CHANNEL', 'CC');
define('PAYMENT_GATEWAY_MMQR_CHANNEL', ''); // Fill this when 2C2P gives you the MMQR sandbox channel code
// 2C2P sandbox card: 4111111111111111, CVV 123, OTP 123456, any future expiry date.

// Cron Security
define('CRON_TOKEN', 'your-secret-cron-token');
