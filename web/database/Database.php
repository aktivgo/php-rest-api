<?php

namespace aktivgo\PhpRestApi\database;

use PDO;
use PDOException;

class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        try {
            if (self::$connection === null) {
                //$config = require_once '../configs/mysql.php';
                $config = [
                    'host' => $_ENV['MYSQL_HOST'],
                    'dbname' => $_ENV['MYSQL_DATABASE'],
                    'username' => $_ENV['MYSQL_USER'],
                    'password' => $_ENV['MYSQL_PASSWORD']
                ];
                self::$connection = new PDO('mysql:host=' . $config['host'] . ';dbname=' . $config['dbname'], $config['username'], $config['password']);
            }
            return self::$connection;
        } catch (PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }
}