<?php

namespace App;
use Exception;

class PostMapper
{

    //private PDO $connection;
    private Database $database;

    public function __construct( Database $database){
        $this->database=$database;
    }

    /**
     * @param string $urlKey
     * @return array|null
     */
    public function getByUrlKey(string $urlKey):?array{
        $stm = $this->database->getConnection()->prepare('SELECT * FROM post WHERE url_key = :url_key');
        $stm->execute(['url_key'=>$urlKey]);
        $result = $stm->fetchAll();
        return array_shift($result);
    }

    /**
     * @param int $page
     * @param int $limit
     * @param string $direction
     * @return array|null
     * @throws Exception
     */
    public function getList(int $page=1,int $limit=2,string $direction='ASC'):?array{
        if(!in_array($direction,['DESC','ASC'])){
            throw new Exception('The direction is not supported');
        }
        $start = ($page-1) * $limit;
        $stm = $this->database->getConnection()->prepare(
            'SELECT * FROM post ORDER BY published_date ' . $direction  . ' LIMIT ' . $start . ',' . $limit);
        $stm->execute();
        return  $stm->fetchAll();
    }

    /**
     * @return int
     */
    public function getTotalCount():int{
        $stm = $this->database->getConnection()->prepare(
            'SELECT count(post_id) as total FROM post');
        $stm->execute();
        return (int) ($stm->fetchColumn() ?? 0);
    }
}