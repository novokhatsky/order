<?php

$json = file_get_contents('php://input');
$bid = json_decode($json, true);

$address = new \order\models\Address($db, $auth->currUser());

$guid_address = $address->restore();

$data = ["error" => 1, "msg" => "данные не записаны, попробуйте позже"];

if ($address->check($guid_address)) {

    $result = (new \order\models\Bid($db))->save($guid_address, $bid);

    if ((int)$result != -1) {
        $data = ["error" => 0, "msg" => $result];
    }
}

require_once 'views/json.php';

