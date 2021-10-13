<?php

namespace aktivgo\PhpRestApi\database;

use PDO;
use PDOException;

class Database
{
    public static function getConnection(): PDO
    {
        try {
            return new PDO('mysql:host=' . $_ENV['MYSQL_HOST'] . ';dbname=' . $_ENV['MYSQL_DATABASE'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD']);
        } catch (PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }
}