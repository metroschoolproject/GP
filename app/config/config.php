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

// Platform bank accounts for manual payment — fill in real account numbers before going live
define('PLATFORM_BANK_ACCOUNTS', [
    'KBZ Pay'           => ['account' => '09-XXXX-XXXXX',    'name' => 'Golden Promise Co., Ltd.'],
    'Wave Money'        => ['account' => '09-XXXX-XXXXX',    'name' => 'Golden Promise Co., Ltd.'],
    'AYA Pay'           => ['account' => '09-XXXX-XXXXX',    'name' => 'Golden Promise Co., Ltd.'],
    'Yoma Bank'         => ['account' => 'XXXX-XXXX-XXXX',   'name' => 'Golden Promise Co., Ltd.'],
    'CB Bank'           => ['account' => 'XXXX-XXXX-XXXX',   'name' => 'Golden Promise Co., Ltd.'],
    'Visa / MasterCard' => ['account' => 'XXXX-XXXX-XXXX-XXXX', 'name' => 'Golden Promise Co., Ltd.'],
]);

// Cron Security
define('CRON_TOKEN', 'your-secret-cron-token');
