<?php

$id_factura = (int)$param[3];

$bid = new \order\models\Bid($db);

$list_goods = $bid->getBodyFact($id_factura);

$to_date = $bid->getToDate($id_factura);

$date_create = $bid->getCreateDate($id_factura);

//todo указать адрес доставки
//
require_once('views/detail.php');

