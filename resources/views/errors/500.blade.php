<!DOCTYPE html>
<html lang="ru">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500</title>
    <link rel="stylesheet" href="{{ asset('client/css/error.css') }}">
</head>

<body>
    <div class="wrapper">
        <div class="blueBackground">
            <div class="absoluteDiv">
                <div class="relativeDiv"><img class="bgImage" src="{{ asset('client/media/errorImage.png') }}" alt="errorImage"></div>
            </div>
        </div>
        <div class="errorContainer">
            <div class="errorNumberDiv">
                <p class="errorNumber">5</p>
                <p class="errorNumber">0</p>
                <p class="errorNumber">0</p>
            </div>
            <p class="miniMessageText">Xatolikni bartaraf etamiz, hozircha bosh sahifaga qayting</p>
            <div class="button"><a class="buttonLink" href="{{ route('index') }}" aria-label="home">Bosh sahifaga qaytish</a></div>
        </div>
    </div>
</body>

</html>