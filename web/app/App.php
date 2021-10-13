<?php

namespace aktivgo\PhpRestApi\app;

use PDO;

header('Content-type: json/application');

class App
{
    // Получает всех пользователей
    public static function getUsers($db, $table, $get)
    {
        $page = $get['page'] ?? 1;
        $limit = 3;
        $offset = ($page - 1) * $limit;

        $userList = [];
        $sth = $db->prepare("select * from $table where id > 0 limit $offset, $limit");
        $sth->execute();

        while ($res = $sth->fetch(PDO::FETCH_ASSOC)) {
            $userList[] = $res;
        }

        self::echoResponseCode($userList, 200);
    }

    // Получает пользователя
    public static function getUser($db, $table, $id)
    {
        $sth = $db->prepare("select * from $table where id = $id");
        $sth->execute();
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            self::echoResponseCode('User not found', 404);
            die();
        }

        self::echoResponseCode($res, 200);
    }

    // Добавляет пользователя в БД
    public static function addUser($db, $table, $data)
    {
        self::checkData($data);

        $sth = $db->prepare("insert into $table values (null, :firstName, :lastName, :email, false)");
        $sth->execute($data);

        $id = $db->lastInsertId();

        $token = Activation::generateToken($id);
        var_dump($token);
        //Activation::sendMessage($data['email'], $token);
        //Activation::confirmEmail($token);

        http_response_code(201);
        echo $id;
    }

    // Обновляет информацию о пользователе в БД
    public static function updateUser($db, $table, $id, $data)
    {
        self::checkId($db, $table, $id);
        self::checkData($data);

        $sth = $db->prepare("update $table set firstName = :firstName, lastName = :lastName, email = :email where id = $id");
        $sth->execute($data);

        http_response_code(202);
    }

    // Удаляет пользователя из БД
    public static function deleteUser($db, $table, $id)
    {
        $sth = $db->prepare("delete from $table where id = $id");
        $sth->execute();

        http_response_code(204);
    }

    // Посылает http код и выводит массив $res
    public static function echoResponseCode($res, $code)
    {
        http_response_code($code);
        echo json_encode($res);
    }

    // Проверяет существование id в БД
    private static function checkId($db, $table, $id)
    {
        $sth = $db->prepare("select * from $table where id = $id");
        $sth->execute();
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            self::echoResponseCode('User not found', 404);
            die();
        }
    }

    // Проверяет массив данных на корректность
    private static function checkData($data)
    {
        if (!isset($data) || !isset($data['firstName']) || !isset($data['lastName']) || !isset($data['email'])) {
            self::echoResponseCode('The fields are incorrect', 400);
            die();
        }
    }
}