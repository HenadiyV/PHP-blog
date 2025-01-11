<?php

namespace App;
use http\Exception\InvalidArgumentException;
use PDO;
use PDOException;

class Database
{
    /**
     * @var PDO
     */
    private PDO $connection;

    /**
     * @param string $dsn
     * @param string $username
     * @param string $password
     */
    public function __construct(string $dsn, string $username = null, string $password = ''){
        try{
            $this->connection = new PDO($dsn, $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
            $this->connection->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE,PDO::FETCH_ASSOC);
        }catch(PDOException $e){
            throw new InvalidArgumentException('Database error:'.$e->getMessage());
        }
    }

    /**
     * @return PDO
     */
    public function getConnection(): PDO{

        return $this->connection;
    }

}