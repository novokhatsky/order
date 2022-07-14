<?php

$dt = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));

$dt->add(new DateInterval('P1D'));

$guid_address = $param[3];

$address = new \order\models\Address($db, $auth->currUser());

if (!$address->check($guid_address)) {
    header('Location: /order');
    exit;
}

$address->save($guid_address);

$address_name = $address->name($guid_address);

require_once('views/new.php');

