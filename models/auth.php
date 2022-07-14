<?php

namespace order\Models;

Class Auth
{
    private $id_user;


    function __construct()
    {
        if (isset($_SESSION['id_user'])) {
            $this->id_user = $_SESSION['id_user'];
        } else {
            $this->id_user = false;
        }
    }


    function currUser()
    {
        return $this->id_user;
    }


    function needLogin($action)
    {
        $page_not_login = [
            'login',
            'send',
        ];

        return (!$this->currUser() && !in_array($action, $page_not_login));
    }


    function loginUser($id_user)
    {
        $_SESSION['id_user'] = $id_user;
        $this->id_user = $id_user;
    }


    function logout()
    {
        $this->id_user = false;
        unset($_SESSION['id_user']);
    }
}

