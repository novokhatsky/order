<?php

namespace order\models;

Class User
{
    private $db;


    public function __construct($db)
    {
        $this->db = $db;
    }


    public function name($guid)
    {
        $query = 'select name from production.clients where guid = :guid';

        return $this->db->getValue($query, ['guid' => $guid]);
    }


    public function validatePass($login, $pass)
    {
        $query = 'select guid from production.clients where login = :login and pass = :pass';

        $guid = $this->db->getValue($query, ['login' => $login, 'pass' => $pass]);

        return $guid;
    }
}

