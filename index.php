<?php

session_start();

date_default_timezone_set('Asia/Novosibirsk');

require_once 'config/params.php';

require_once 'module/autoload.php';

$db = new \order\module\DbConnect(DB);

$user = new \order\models\User($db);

$auth = new \order\models\Auth();

$param = explode('/', $_SERVER['REQUEST_URI']);

$action = strlen($param[2]) ? htmlspecialchars($param[2]) : 'index';

if ($auth->needLogin($action)) {
    $action = 'login';
}

$fullname = 'controllers/' . $action . '.php';

require_once file_exists($fullname) ? $fullname : 'views/404.html';
