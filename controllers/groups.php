<?php

$guid = $auth->currUser();

$goods = new \order\models\Goods($db);

$data = $goods->groups($guid);

require_once 'views/json.php';
