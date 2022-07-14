<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Содержимое заявок</title>
    <link href="<?=BASE_URL?>css/foundation.min.css" rel="stylesheet" type="text/css">
    <meta name="viewport" content="width=device-width">
    <style>
        table {
            border-top: 1px solid grey;
            border-collapse: collapse;
            width: 100%;
        }

        tr:nth-child(even) {
            background-color: #F0F0F0;
        }

        .full-width {
            max-width: 95%;
        }

    </style>
</head>
<body>
<br>
<div class="row full-width">
    <div class="large-6 columns">
        <div><a href="<?=BASE_URL?>journal" class="button">назад</a></div>
        <p>заявка на <?=$to_date?></p>
        <table>
            <tr>
                <th>наименование</th>
                <th>кол-во</th>
            </tr>
                <?php
                    foreach ($list_goods as $goods) {
                        echo '<tr>';
                        echo '<td>', $goods['name'], '</td>';
                        echo '<td>', $goods['kol_vo'], '</td>';
                        echo '</tr>';
                    }
                ?>
        </table>
        <p>дата и время заполнения:<br> <?=$date_create?> </p>
    </div>
</div>

</body>
</html>
