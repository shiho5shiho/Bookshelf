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

    'accepted' => ':attribute を承認してください。',
    'accepted_if' => ':other が :value の場合、:attribute を承認してください。',
    'active_url' => ':attribute には有効なURLを指定してください。',
    'after' => ':attribute には :date より後の日付を指定してください。',
    'after_or_equal' => ':attribute には :date 以降の日付を指定してください。',
    'alpha' => ':attribute にはアルファベットのみ使用できます。',
    'alpha_dash' => ':attribute にはアルファベット・数字・ダッシュ(-)・アンダースコア(_)のみ使用できます。',
    'alpha_num' => ':attribute にはアルファベットと数字のみ使用できます。',
    'array' => ':attribute は配列で指定してください。',
    'ascii' => ':attribute には半角の英数字と記号のみ使用できます。',
    'before' => ':attribute には :date より前の日付を指定してください。',
    'before_or_equal' => ':attribute には :date 以前の日付を指定してください。',
    'between' => [
        'array' => ':attribute の項目数は :min〜:max 個にしてください。',
        'file' => ':attribute のファイルサイズは :min〜:max KBにしてください。',
        'numeric' => ':attribute は :min〜:max の範囲で指定してください。',
        'string' => ':attribute は :min〜:max 文字で指定してください。',
    ],
    'boolean' => ':attribute には true か false を指定してください。',
    'can' => ':attribute に許可されていない値が含まれています。',
    'confirmed' => ':attribute と確認用の入力が一致しません。',
    'current_password' => 'パスワードが正しくありません。',
    'date' => ':attribute には有効な日付を指定してください。',
    'date_equals' => ':attribute には :date と同じ日付を指定してください。',
    'date_format' => ':attribute は :format 形式で指定してください。',
    'decimal' => ':attribute には小数点以下 :decimal 桁の数値を指定してください。',
    'declined' => ':attribute を拒否してください。',
    'declined_if' => ':other が :value の場合、:attribute を拒否してください。',
    'different' => ':attribute と :other には異なる値を指定してください。',
    'digits' => ':attribute は :digits 桁で指定してください。',
    'digits_between' => ':attribute は :min〜:max 桁で指定してください。',
    'dimensions' => ':attribute の画像サイズが無効です。',
    'distinct' => ':attribute に重複した値があります。',
    'doesnt_end_with' => ':attribute の末尾には次の値を使用できません: :values',
    'doesnt_start_with' => ':attribute の先頭には次の値を使用できません: :values',
    'email' => ':attribute には有効なメールアドレスを指定してください。',
    'ends_with' => ':attribute の末尾には次のいずれかを指定してください: :values',
    'enum' => '選択された :attribute は無効です。',
    'exists' => '選択された :attribute は無効です。',
    'extensions' => ':attribute は次のいずれかの拡張子にしてください: :values',
    'file' => ':attribute にはファイルを指定してください。',
    'filled' => ':attribute には値を指定してください。',
    'gt' => [
        'array' => ':attribute の項目数は :value 個より多くしてください。',
        'file' => ':attribute のファイルサイズは :value KBより大きくしてください。',
        'numeric' => ':attribute は :value より大きい値にしてください。',
        'string' => ':attribute は :value 文字より多くしてください。',
    ],
    'gte' => [
        'array' => ':attribute の項目数は :value 個以上にしてください。',
        'file' => ':attribute のファイルサイズは :value KB以上にしてください。',
        'numeric' => ':attribute は :value 以上にしてください。',
        'string' => ':attribute は :value 文字以上にしてください。',
    ],
    'hex_color' => ':attribute には有効な16進数カラーコードを指定してください。',
    'image' => ':attribute には画像ファイルを指定してください。',
    'in' => '選択された :attribute は無効です。',
    'in_array' => ':attribute は :other に存在する必要があります。',
    'integer' => ':attribute は整数で指定してください。',
    'ip' => ':attribute には有効なIPアドレスを指定してください。',
    'ipv4' => ':attribute には有効なIPv4アドレスを指定してください。',
    'ipv6' => ':attribute には有効なIPv6アドレスを指定してください。',
    'json' => ':attribute には有効なJSON文字列を指定してください。',
    'lowercase' => ':attribute は小文字で指定してください。',
    'lt' => [
        'array' => ':attribute の項目数は :value 個より少なくしてください。',
        'file' => ':attribute のファイルサイズは :value KBより小さくしてください。',
        'numeric' => ':attribute は :value より小さい値にしてください。',
        'string' => ':attribute は :value 文字より少なくしてください。',
    ],
    'lte' => [
        'array' => ':attribute の項目数は :value 個以下にしてください。',
        'file' => ':attribute のファイルサイズは :value KB以下にしてください。',
        'numeric' => ':attribute は :value 以下にしてください。',
        'string' => ':attribute は :value 文字以下にしてください。',
    ],
    'mac_address' => ':attribute には有効なMACアドレスを指定してください。',
    'max' => [
        'array' => ':attribute の項目数は :max 個以下にしてください。',
        'file' => ':attribute のファイルサイズは :max KB以下にしてください。',
        'numeric' => ':attribute は :max 以下にしてください。',
        'string' => ':attribute は :max 文字以内で指定してください。',
    ],
    'max_digits' => ':attribute は :max 桁以内で指定してください。',
    'mimes' => ':attribute には次のファイル形式を指定してください: :values',
    'mimetypes' => ':attribute には次のファイル形式を指定してください: :values',
    'min' => [
        'array' => ':attribute の項目数は :min 個以上にしてください。',
        'file' => ':attribute のファイルサイズは :min KB以上にしてください。',
        'numeric' => ':attribute は :min 以上にしてください。',
        'string' => ':attribute は :min 文字以上で指定してください。',
    ],
    'min_digits' => ':attribute は :min 桁以上で指定してください。',
    'missing' => ':attribute は指定できません。',
    'missing_if' => ':other が :value の場合、:attribute は指定できません。',
    'missing_unless' => ':other が :value でない場合、:attribute は指定できません。',
    'missing_with' => ':values が指定されている場合、:attribute は指定できません。',
    'missing_with_all' => ':values がすべて指定されている場合、:attribute は指定できません。',
    'multiple_of' => ':attribute は :value の倍数にしてください。',
    'not_in' => '選択された :attribute は無効です。',
    'not_regex' => ':attribute の形式が正しくありません。',
    'numeric' => ':attribute は数値で指定してください。',
    'password' => [
        'letters' => ':attribute には少なくとも1文字のアルファベットを含めてください。',
        'mixed' => ':attribute には少なくとも大文字と小文字を1文字ずつ含めてください。',
        'numbers' => ':attribute には少なくとも1つの数字を含めてください。',
        'symbols' => ':attribute には少なくとも1つの記号を含めてください。',
        'uncompromised' => '指定された :attribute は情報漏洩に含まれています。別の :attribute を指定してください。',
    ],
    'present' => ':attribute が存在している必要があります。',
    'present_if' => ':other が :value の場合、:attribute が存在している必要があります。',
    'present_unless' => ':other が :value でない場合、:attribute が存在している必要があります。',
    'present_with' => ':values が指定されている場合、:attribute も指定してください。',
    'present_with_all' => ':values がすべて指定されている場合、:attribute も指定してください。',
    'prohibited' => ':attribute は指定できません。',
    'prohibited_if' => ':other が :value の場合、:attribute は指定できません。',
    'prohibited_unless' => ':other が :values に含まれない場合、:attribute は指定できません。',
    'prohibits' => ':attribute を指定すると :other は指定できません。',
    'regex' => ':attribute の形式が正しくありません。',
    'required' => ':attribute は必須です。',
    'required_array_keys' => ':attribute には次のキーを含めてください: :values',
    'required_if' => ':other が :value の場合、:attribute は必須です。',
    'required_if_accepted' => ':other が承認された場合、:attribute は必須です。',
    'required_unless' => ':other が :values に含まれない場合、:attribute は必須です。',
    'required_with' => ':values が指定されている場合、:attribute は必須です。',
    'required_with_all' => ':values がすべて指定されている場合、:attribute は必須です。',
    'required_without' => ':values が指定されていない場合、:attribute は必須です。',
    'required_without_all' => ':values がいずれも指定されていない場合、:attribute は必須です。',
    'same' => ':attribute と :other が一致しません。',
    'size' => [
        'array' => ':attribute の項目数は :size 個にしてください。',
        'file' => ':attribute のファイルサイズは :size KBにしてください。',
        'numeric' => ':attribute は :size にしてください。',
        'string' => ':attribute は :size 文字にしてください。',
    ],
    'starts_with' => ':attribute の先頭には次のいずれかを指定してください: :values',
    'string' => ':attribute は文字列で指定してください。',
    'timezone' => ':attribute には有効なタイムゾーンを指定してください。',
    'unique' => 'その :attribute は既に使用されています。',
    'uploaded' => ':attribute のアップロードに失敗しました。',
    'uppercase' => ':attribute は大文字で指定してください。',
    'url' => ':attribute には有効なURLを指定してください。',
    'ulid' => ':attribute には有効なULIDを指定してください。',
    'uuid' => ':attribute には有効なUUIDを指定してください。',

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
        'name' => '名前',
        'email' => 'メールアドレス',
        'password' => 'パスワード',
        'password_confirmation' => 'パスワード（確認）',
        'title' => 'タイトル',
        'author' => '著者',
        'isbn' => 'ISBN',
        'published_date' => '出版日',
        'description' => '説明',
        'image_url' => '画像URL',
        'genres' => 'ジャンル',
        'genre_id' => 'ジャンル',
        'name_genre' => 'ジャンル名',
        'rating' => '評価',
        'comment' => 'コメント',
        'keyword' => 'キーワード',
        'sort' => '並び順',
        'page' => 'ページ番号',
        'per_page' => '1ページあたりの件数',
    ],
];
