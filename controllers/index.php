<?php

$user_name = $user->name($auth->currUser());

$address = new \order\models\Address($db, $auth->currUser());
$addresses = $address->all();

require_once 'views/index.php';

