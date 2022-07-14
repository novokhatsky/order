<?php

$bid = new \order\models\Bid($db);

$list_bid = $bid->getList($auth->currUser());

require_once('views/journal.php');

