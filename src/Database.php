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
    public function __construct(PDO $connection){
        try{
            $this->connection = $connection;

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