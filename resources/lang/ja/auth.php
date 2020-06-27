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

    'failed' => '結果が見つかりません。',
    'throttle' => 'ログインが失敗しました。後ほどもう一度実行してください。',

    'login' => [
        'success' => 'ログインが完了しました。',
        'failed' => 'ログインでエラーが発生しました。',
    ],
    'register' => [
        'success' => '登録が完了しました。',
        'failed' => '登録が失敗しました。',
        'email' => 'メールアドレスが登録されました。',
        'confirm_code' => '認証が完了しました。'
    ],
    'logout' => [
        'success' => 'ログアウトしました。',
    ],
    'reset' => [
        'password' => [
            'email_success' => 'メールを送信しました。',
            'reset_success' => 'パスワードがリセットされました。',
            'token_success' => 'パスワードが見つかりました。',
        ]
    ]

];
