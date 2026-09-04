<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404</title>
    <link rel="stylesheet" href="{{ asset('client/css/error.css') }}">
</head>

<body>
    <div class="wrapper">

        <div class="errorContainer">
            <div class="errorNumberDiv">
                <p class="errorNumber">4</p>
                <p class="errorNumber">0</p>
                <p class="errorNumber">4</p>
            </div>
            <p class="bigMessageText">This page does not exist :(</p>
            <div class="button"><a class="buttonLink" href="{{ route('admin') }}" aria-label="home">Return to the main page</a></div>
        </div>
    </div>
</body>
</html>