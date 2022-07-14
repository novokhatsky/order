<?php

$group = urldecode($param[3]);
$seek_str = urldecode($param[4]);

$guid_client = $auth->currUser();

$goods = new \order\models\Goods($db);

$data = $goods->list($guid_client, $group, $seek_str);

require_once 'views/json.php';

