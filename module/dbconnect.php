<?php


namespace order\module;

Class DbConnect
{
    public $errInfo = [];

    private $handler;
    private $dsn;
    private $user;
    private $pass;

    public function __construct($config)
    {
        $this->dsn = 'mysql:host=' . $config['srv'] . ';dbname=' . $config['db'] . ';charset=utf8';
        $this->user = $config['user'];
        $this->pass = $config['pass'];
    }

    private function connector()
    {
        if (!$this->handler) {
            $this->handler = new \PDO($this->dsn, $this->user, $this->pass);
            $this->handler->query("set names 'utf8'");
        }

        return $this->handler;
    }

    function prepare($query)
    {
        return $this->connector()->prepare($query);
    }

    function getList($query, $params = [])
    {
        $stmt = $this
                    ->connector()
                    ->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    function getRow($query, $params = [])
    {
        $stmt = $this
                    ->connector()
                    ->prepare($query);
        $stmt->execute($params);

        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    function getValue($query, $params = [])
    {
        $stmt = $this
                    ->connector()
                    ->prepare($query);
        $stmt->execute($params);

        $result = $stmt->fetch(\PDO::FETCH_NUM);

        if ($result) {
            return $result[0];
        }

        return false;
    }

    function insertData($query, $params = [])
    {
        $stmt = $this
                    ->connector()
                    ->prepare($query);

        if ($stmt->execute($params)) {

            return $this
                        ->connector()
                        ->lastInsertId();
        } else {
            $this->errInfo = $stmt->errorInfo();

            return -1;
        }
    }

    function updateData($query, $params = [])
    {
        $stmt = $this
                    ->connector()
                    ->prepare($query);

        if ($stmt->execute($params)) {

            return $stmt->rowCount();
        } else {
            $this->errInfo = $stmt->errorInfo();

            return -1;
        }
    }

    function beginTransaction()
    {
        $this->connector()->beginTransaction();
    }

    function rollBack()
    {
        $this->connector()->rollBack();
    }

    function commit()
    {
        $this->connector()->commit();
    }
}

