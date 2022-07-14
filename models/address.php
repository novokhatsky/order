<?php

namespace order\models;

class Address
{
    private $db;
    private $user_guid;


    public function __construct($db, $user_guid)
    {
        $this->db = $db;
        $this->user_guid = $user_guid;
    }


    public function all()
    {
        return $this->db->getList(
            'select guid, name from production.addresses where guid_client = :user_guid order by name',
            ['user_guid' => $this->user_guid]
        );
    }


    public function check($guid_address)
    {
        return (bool)$this->db->getValue(
            'select count(*) from production.addresses where guid = :guid_address and guid_client = :guid_client',
            ['guid_address' => $guid_address, 'guid_client' => $this->user_guid]
        );
    }


    public function name($guid_address)
    {
        return $this->db->getValue(
            'select name from production.addresses where guid = :guid_address and guid_client = :guid_client',
            ['guid_address' => $guid_address, 'guid_client' => $this->user_guid]
        );
    }


    public function save($guid_address)
    {
        $_SESSION[$this->user_guid] = $guid_address;
    }


    public function restore()
    {
        return $_SESSION[$this->user_guid];
    }
}

