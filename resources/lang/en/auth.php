<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines are used during authentication for various
    | messages that we need to display to the user. You are free to modify
    | these language lines according to your application's requirements.
    |
    */

    'failed' => 'These credentials do not match our records.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',

    'login' => [
        'success' => 'Login successful',
        'failed' => 'Login error',
    ],
    'register' => [
        'success' => 'Register successful',
        'failed' => 'Register error',
        'email' => 'Register email successful',
        'confirm_code' => 'Code confirm successful.'
    ],
    'logout' => [
        'success' => 'Logout successful',
    ],
    'reset' => [
        'password' => [
            'email_success' => 'Send email success',
            'reset_success' => 'Reset password success',
            'token_success' => 'Find reset password success',
        ]
    ]

];
