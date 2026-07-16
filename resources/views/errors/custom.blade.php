{{-- resources/views/errors/custom.blade.php --}}
<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $status }} | エラー</title>
</head>

<body style="font-family: sans-serif; text-align: center; padding: 80px 16px;">
    <h1 style="font-size: 48px; margin: 0;">{{ $status }}</h1>
    <p style="font-size: 18px; color: #555;">{{ $message }}</p>
    <a href="{{ url('/') }}" style="color: #2563eb;">トップページへ戻る</a>
</body>

</html>