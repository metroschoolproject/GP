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

// Default service time windows by category (used for fullday package bookings).
// Priority: service_schedules open/close > services.default_start/end_time > this fallback.
define('CATEGORY_DEFAULT_TIMES', [
    10 => ['start' => '06:00:00', 'end' => '10:00:00'], // Make Up & Hair
     2 => ['start' => '09:00:00', 'end' => '11:00:00'], // Dress
     1 => ['start' => '09:00:00', 'end' => '11:00:00'], // Accessories
     9 => ['start' => '09:00:00', 'end' => '11:00:00'], // Jewelry
    12 => ['start' => '06:00:00', 'end' => '11:00:00'], // Decoration
    11 => ['start' => '10:00:00', 'end' => '14:00:00'], // Car
     5 => ['start' => '08:00:00', 'end' => '20:00:00'], // Studio / Photography
     6 => ['start' => '10:00:00', 'end' => '22:00:00'], // Venue
     3 => ['start' => '11:00:00', 'end' => '22:00:00'], // Food
     8 => ['start' => '08:00:00', 'end' => '10:00:00'], // Invitation & Gifts
     7 => ['start' => '10:00:00', 'end' => '18:00:00'], // Others (fallback)
]);
