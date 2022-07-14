<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Журнал заявок</title>
    <link href="<?=BASE_URL?>css/foundation.min.css" rel="stylesheet" type="text/css">
    <meta name="viewport" content="width=device-width">
    <style>
        .full-width {
            max-width: 95%;
        }
    </style>
</head>
<body>
<br>

<div class="row full-width">
    <div class="large-12 columns">
        <div><a href="<?=BASE_URL?>" class="button">назад</a></div>
        <h3>заявка на число:</h3>
        <?php
            foreach ($list_bid as $bid) {
                echo '<div><a href="' . BASE_URL . 'detail/', $bid['id_factura'], '">';
                echo $bid['dt'], '&nbsp;&nbsp;';
                echo $bid['name'];
                echo '</a></div><br>';
            }
        ?>
    </div>
</div>

</body>
</html>
