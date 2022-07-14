<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Заявка на продукцию</title>
    <link href="<?=BASE_URL?>css/foundation.min.css" rel="stylesheet" type="text/css">
    <meta name="viewport" content="width=device-width">
</head>
<body>
    <div class="row">
        <div class="large-4 columns">
            <h3>Заявка на продукцию</h3>
        </div>
    </div>

    <form method="post">

    <div class="row">
        <div class="large-4 columns">
            <label>логин:
                <input type="text" name="login">
            </label>
        </div>
    </div>

    <div class="row">
        <div class="large-4 columns">
            <?=$message?>
        </div>
    </div>

    <div class="row">
        <div class="large-4 columns">
            <label>пароль:
                <input type="password" name="pass">
            </label>
        </div>
    </div>

    <div class="row">
        <div class="large-4 columns">
            <input class="button" type="submit" value="Вход">
        </div>
    </div>
    </form>
</body>
</html>
