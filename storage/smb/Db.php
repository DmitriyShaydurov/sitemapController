<?php
/**
 * Description: This class connects to opencart Db by PDO
 * so it could be used in any opencart version
 * Author: Dmitriy Shaydurov <dmitriy.shaydurov@gmail.com>
 * Author URI: http://smb-studio.com
 * Version: 1.0
 * Date: 30.04.2016
 **/

namespace shaydurov\opencart;
class Db
{
      private $pdo;
      private $stmt;
      public $error;

    public function __construct()
    {

        $dsn = 'mysql:host=' . DB_HOSTNAME . ';dbname=' . DB_DATABASE;
        try {
        $this->pdo = new \PDO($dsn, DB_USERNAME, DB_PASSWORD);
        $this->pdo->exec("set names utf8");
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
        $this->pdo->setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING );
        } catch(PDOException $e) {
        $this->error = $e->getMessage();
         }
    }

    // Prepare statement with query
      public function query($sql,$params)
      {
          $this->stmt = $this->pdo->prepare($sql);
          return $this->stmt->execute($params);
      }

      // Get result set as array of arrays
      public function fetchAll($sql,$params = [])
      {
          $this->query($sql,$params);
          return $this->stmt->fetchAll();
      }
      // Get single record as array
      public function fetchSingle($sql,$params = [])
      {
          $this->query($sql,$params);
          return $this->stmt->fetch();
      }
      // Get row count
      public function rowCount()
      {
          return $this->stmt->rowCount();
      }

      public function lastId()
      {
          return $this->pdo->lastInsertId();
      }

}
