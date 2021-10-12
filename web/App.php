<?php

namespace aktivgo\PhpRestApi;

use PDO;
use PDOException;

header('Content-type: json/application');

class App
{
    public static function connectToDb(): PDO
    {
        try {
            return new PDO('mysql:host=' . $_ENV['MYSQL_HOST'] . ';dbname=' . $_ENV['MYSQL_DATABASE'], $_ENV['MYSQL_USER'], $_ENV['MYSQL_PASSWORD']);
        } catch (PDOException $e) {
            echo $e->getMessage();
            die();
        }
    }

    public static function getUsers($db, $get)
    {
        $page = $get['page'] ?? 1;
        $limit = 3;
        $offset = ($page - 1) * $limit;

        $userList = [];
        $sth = $db->prepare("select * from users where id > 0 limit $offset, $limit");
        $sth->execute();

        while ($res = $sth->fetch(PDO::FETCH_ASSOC)) {
            $userList[] = $res;
        }

        self::echoResponseCode($userList, 200);
    }

    public static function getUser($db, $id)
    {
        $sth = $db->prepare("select * from users where id = $id");
        $sth->execute();
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            self::echoResponseCode('User not found', 404);
            die();
        }

        self::echoResponseCode($res, 200);
    }

    public static function addUser($db, $data)
    {
        self::checkData($data);

        $sth = $db->prepare("insert into users values (null, :firstName, :lastName)");
        $sth->execute($data);

        http_response_code(201);
        echo $db->lastInsertId();
    }

    public static function updateUser($db, $id, $data)
    {
        self::checkId($db, $id);
        self::checkData($db, $data);

        $sth = $db->prepare("update users set firstName = :firstName, lastName = :lastName where id = $id");
        $sth->execute($data);

        http_response_code(202);
    }

    public static function deleteUser($db, $id)
    {
        $sth = $db->prepare("delete from users where id = $id");
        $sth->execute();

        http_response_code(204);
    }

    public static function echoResponseCode($res, $code)
    {
        http_response_code($code);
        echo json_encode($res);
    }

    private static function checkId($db, $id)
    {
        $sth = $db->prepare("select * from users where id = $id");
        $sth->execute();
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            self::echoResponseCode('User not found', 404);
            die();
        }
    }

    private static function checkData($db, $data)
    {
        /*$columnNames = self::getColumnNames($db);
        foreach ($columnNames as $columnName) {
            if(!isset($data) || $data[$columnName] === '' || isset($data[$columnName])) {
                self::echoResponseCode('The fields are incorrect', 400);
                die();
            }
            $regex = '/^[_a-z0-9-]+(.[_a-z0-9-]+)*@[a-z0-9-]+(.[a-z0-9-]+)*(.[a-z]{2,4})$/';
            if ($columnName['3'] === 'email' && preg_match($regex, $columnName['3'])) {

            }
        }*/

        if (!isset($data) || $data['firstName'] === '' || $data['lastName'] === '' || $data['email']
            || !isset($data['firstName']) || !isset($data['lastName']) || !isset($data['email'])) {
            self::echoResponseCode('The fields are incorrect', 400);
            die();
        }
    }

    /*private static function getColumnNames($db): array
    {
        $sth = $db->prepare("SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA='test' AND TABLE_NAME = users");
        $sth->execute();
        $output = [];
        while ($row = $sth->fetch(PDO::FETCH_ASSOC)) {
            $output[] = $row['COLUMN_NAME'];
        }
        return $output;
    }*/


}