<?php

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
    $pass = filter_input(INPUT_POST, 'pass', FILTER_SANITIZE_STRING);

    if ($guid = $user->validatePass($login, $pass)) {

        $auth->loginUser($guid);

        header('Location: ' . BASE_URL);
        exit;
    }

    $message = 'Неверный адрес или пароль!';
}

require_once 'views/login.php';

