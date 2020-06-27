<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => ':attributeを承認してください。',
    'active_url' => ':attributeは正しいURLではありません。',
    'after' => ':attributeは:date以降の日付にしてください。',
    'after_or_equal' => ':attributeは:date以降の日付にしてください。',
    'alpha' => ':attributeは英字のみにしてください。',
    'alpha_dash' => ':attributeは英数字とハイフンのみにしてください。',
    'alpha_num' => ':attributeは英数字のみにしてください。',
    'array' => ':attributeは配列にしてください。',
    'before' => ':attributeは:date以前の日付にしてください。',
    'before_or_equal' => ':attributeは:date以前の日付にしてください。',
    'between' => [
        'numeric' => ':attributeは:min?:maxまでにしてください。 ',
        'file' => ':attributeは:min?:max KBまでのファイルにしてください。',
        'string' => ':attributeは:min?:max文字にしてください。',
        'array' => ':attributeは:min?:max個までにしてください。',
    ],
    'boolean' => ':attributeはtrueかfalseにしてください。',
    'confirmed' => ':attributeは確認用項目と一致していません。',
    'date' => ':attributeは正しい日付ではありません。',
    'date_equals' => ':attributeは:dateの日付にしてください。',
    'date_format' => ':attributeは":format"書式と一致していません。',
    'different' => ':attributeは:otherと違うものにしてください。',
    'digits' => ':attributeは:digits桁にしてください',
    'digits_between' => ':attributeは:min?:max桁にしてください。',
    'dimensions' => ':attributeは画像サイズが不正です。',
    'distinct' => ':attributeは重複しています。',
    'email' => ':attributeを正しいメールアドレスにしてください。',
    'ends_with' => ':attributeの最後は次の:valuesにしてください。',
    'exists' => '選択された:attributeは正しくありません。',
    'file' => ':attributeはファイルにしてください。',
    'filled' => ':attributeは必須です。',
    'gt' => [
        'numeric' => ':attributeは:value以上にしてください。',
        'file' => ':attributeは:value KB以上のファイルにしてください。',
        'string' => ':attributeは:value文字以上にしてください。',
        'array' => ':attributeは:value個以上にしてください。',
    ],
    'gte' => [
        'numeric' => ':attributeは:value以上にしてください。',
        'file' => ':attributeは:value KB以上のファイルにしてください。',
        'string' => ':attributeは:value文字以上にしてください。',
        'array' => ':attributeは:value個以上にしてください。',
    ],
    'image' => ':attributeは画像にしてください。',
    'in' => '選択された:attributeは正しくありません。',
    'in_array' => ':attributeファイルは:otherに存在していません。',       // 追加
    'integer' => ':attributeは整数にしてください。',
    'ip' => ':attributeを正しいIPアドレスにしてください。',
    'ipv4' => ':attributeを正しいIPv4アドレスにしてください。',       // 追加
    'ipv6' => ':attributeを正しいIPv6アドレスにしてください。',       // 追加
    'json' => ':attributeを正しいJSON形式にしてください。', 
    'lt' => [
        'numeric' => ':attributeは:value より小さくなければなりません。',             // 追加
        'file'    => ':attributeは:value KBより小さくなければなりません。',           // 追加
        'string'  => ':attributeは:value 文字以下でなければなりません。',             // 追加
        'array'   => ':attributeは:value 項目以下でなければなりません。',
    ],
    'lte' => [
        'numeric' => ':attributeは:value以下にしてください。',                        // 追加
        'file'    => ':attributeは:value KB以下のファイルにしてください。.',          // 追加
        'string'  => ':attributeは:value文字以下にしてください。',                    // 追加
        'array'   => ':attributeは:value個以下にしてください。',
    ],
    'max' => [
        'numeric' => ':attributeは:max以下にしてください。',
        'file'    => ':attributeは:max KB以下のファイルにしてください。.',
        'string'  => ':attributeは:max文字以下にしてください。',
        'array'   => ':attributeは:max個以下にしてください。',
    ],
    'mimes' => ':attributeは:valuesタイプのファイルにしてください。',
    'mimetypes' => ':attributeは:valuesタイプのファイルにしてください。',
    'min' => [
        'numeric' => ':attributeは:min以上にしてください。',
        'file'    => ':attributeは:min KB以上のファイルにしてください。.',
        'string'  => ':attributeは:min文字以上にしてください。',
        'array'   => ':attributeは:min個以上にしてください。',
    ],
    'not_in' => '選択された:attributeは正しくありません。',
    'not_regex' => ':attributeの書式が正しくありません。',                 // 追加
    'numeric' => ':attributeは数字にしてください。',
    'present' => ':attributeは存在する必要があります。',                 // 追加
    'regex'  => ':attributeの書式が正しくありません。',
    'required' => ':attributeは必須です。',
    'required_if' => ':otherが:valueの時、:attributeは必須です。',
    'required_unless' => ':otherが:valueにないの時、:attributeは必須です。',     // 追加
    'required_with' => ':valuesが存在する時、:attributeは必須です。',
    'required_with_all' => ':valuesが存在する時、:attributeは必須です。',
    'required_without' => ':valuesが存在しない時、:attributeは必須です。',
    'required_without_all' => ':valuesが存在しない時、:attributeは必須です。',
    'same' => ':attributeと:otherは一致していません。',
    'size' => [
        'numeric' => ':attributeは:sizeにしてください。',
        'file'    => ':attributeは:size KBにしてください。.',
        'string'  => ':attribute:size文字にしてください。',
        'array'   => ':attributeは:size個にしてください。',
    ],
    'starts_with' => ':attributeは:valuesからしてください',
    'string' => ':attributeは文字列にしてください。',
    'timezone' => ':attributeは正しいタイムゾーンを指定してください。',
    'unique' => ':attributeは既に存在します。',
    'uploaded' => ':attributeのアップロードに失敗しました。',             // 追加
    'url' => ':attributeを正しい書式にしてください。',
    'uuid' => ':attributeは正しいUUIDにしてください',
    'password_format' => [
        'lowercase' => ':attributeは最低1文字は小文字を入れてください。 ',
        'uppercase' => ':attributeは最低1文字は大文字を入れてください。',
        'numeric' => ':attributeは最低1英数字を入れてください。',
        'special' => ':attributeは最低1文字は記号を入れてください。',
        'starts_with' => ':attributeの先頭は英字にしてください',
    ],
    'validation_error' => 'バリデーションエラー',
    'resetPassword' => [
        'email' => [
            'verification' => 'メールアドレスと一致するユーザーが見つかりません。'
        ],
        'token' => [
            'verification' => 'ユーザーが見つかりません。',
            'invalid' => 'パスワードリセットトークンが正しくありません。'
        ]

    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
        'email' => [
            'required' => ':attributeは必須です。',
            'email' => ':attributeの形式が正しくありません。',
            'max' => ':attributeは:max以下にしてください。',
            'unique' => ':attributeは既に登録しています。他の:attributeを入力してください'
        ],
        'password' => [
            'required' => ':attributeは必須です。',
            'pwd_lowercase' => ':attributeは最低1文字は小文字を入れてください。',
            'pwd_uppercase' => ':attributeは最低1文字は大文字を入れてください。',
            'pwd_numeric' => ':attributeは最低1英数字を入れてください。',
            'pwd_not_special_character' => ':attributeは記号の入力ができません。',
            'pwd_start_with' => ':attributeの先頭は: [a-zA-Z0-9]からしてください',
        ],
        'generated_code' => [
            'math_email'=> 'メールアドレスと発行コードが正しくありません。',
            'expired' => '登録期限が切れました'
        ],
        'job' => [
            'job' => '職業は正しくありません。',
        ],
        'plan' => [
            'plan' => 'プランは正しくありません'
        ],
        'date_of_birth' => [
            'date_multi_format' => ':attributeはYYYY-MM-DD, YYYY/MM/DD, DD-MM-YYYYの形式にしてください。'
        ],
        'created_at' => [
            'date_multi_format' => ':attributeはYYYY-MM-DD, YYYY/MM/DD, DD-MM-YYYYの形式にしてください。'            
        ],
        'arrival_time' => [
            'date_multi_format' => ':attributeはY/m/d H:i:s, Y-m-d H:i:sの形式にしてください。'
        ],
        'leave_time' => [
            'date_multi_format' => ':attributeはY/m/d H:i:s, Y-m-d H:i:sの形式にしてください。'
        ],
        'bar_id' => [
            'not_found' => 'このユーザーが本店舗を所属していません。'
        ],
        'name' => [
            'missing' => '名前は必須です。',
            'duplicated' => '名前は重複しています'
        ],
        'serial' => [
            'missing' => '並び順は必須です。',
            'duplicated' => '並び順は重複しています。',
            'invalid_format' => '並び順の兄姉区は正しくありません。',
            'min_value' => '並び順は0001から設定してください。'

        ],
        'query' => [
            'empty_bottle' => 'ボトルを選択してください。',
            'empty_category' => 'カテゴリを選択してください。'
        ],
        'remain' => [
            'required' => '名前は必須です',
            'not_number' => '残量は数字で11文字未満である必要があります'
        ],
        'note' => [
            'max_value' => '注は255文字未満である必要があります。'
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [
        'email' => 'メールアドレス',
        'keep_bottle_day_limit' => 'ボトル期限',
        'record_per_customer_page' => '表示件数（顧客一覧)',
        'record_per_visit_page' => '表示件数（来店一覧)',
        'order_name' => 'ソート',
        'order_by' => '降順',
        'furigana_name' => 'ふりがな',
        'company_name' => '会社名',
        'date_of_birth' => '生年月日',
        'post_no' => '郵便番号',
        'remain' => '残量',
        'created_at' => '作成日',
        'csv_file' => "csvファイル",
        'bar_id' =>  "店舗",
        'tel' => "電話番号",
        'note' => "注意",
        'name' => '名前',
        'new_email' => '新しいメール',
        'password' => 'パスワード',
        'arrival_time' => '到着時間',
        'leave_time' => '時間を残す'
    ],
    'specific_in_line' => "行内 ",
    'specific_in_column' => "列内"
];