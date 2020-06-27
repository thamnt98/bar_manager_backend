<?php
/**
 * Created by PhpStorm.
 * User: ducnn
 * Date: 1/22/20
 * Time: 8:31 AM
 */

return [
    // frontend URL
    'url' => env('FRONTEND_URL', 'http://localhost:3000'),
    // path to my frontend page with query param queryURL(temporarySignedRoute URL)
    'email_verify_url' => env('FRONTEND_EMAIL_VERIFY_URL', '/user/verify-email?queryURL='),
];
