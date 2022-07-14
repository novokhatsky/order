<?php
/*
    для ручной отправки фактуры 
    использование: /send/id_factura
*/

$id_fact = $param[3];

$bid = new \order\models\Bid($db);

$result = $bid->unloadInvoice([['id_factura' => $id_fact]], true);


