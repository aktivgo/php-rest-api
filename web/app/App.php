<?php

namespace aktivgo\PhpRestApi\app;

use PDO;

header('Content-type: json/application');

class App
{
    // Получает всех пользователей
    public static function getUsers(PDO $db, array $get)
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

    // Получает пользователя
    public static function getUser(PDO $db, string $id)
    {
        $sth = $db->prepare("select * from users where id = $id");
        $sth->execute();
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            self::echoResponseCode(['User not found'], 404);
            die();
        }

        self::echoResponseCode($res, 200);
    }

    // Добавляет пользователя в БД
    public static function addUser(PDO $db, ?array $data)
    {
        self::checkData($data);

        $sth = $db->prepare("insert into users values (null, :firstName, :lastName, :email, false)");
        $sth->execute($data);

        $id = $db->lastInsertId();

        $token = Activation::generateToken($id);
        Activation::sendMessage($data['email'], $token);


        http_response_code(201);
        echo $id;
    }

    // Обновляет информацию о пользователе в БД
    public static function updateUser(PDO $db, array $data)
    {
        self::checkData($data);
        self::checkId($db, $data['id']);

        $sth = $db->prepare("update users set firstName = :firstName, lastName = :lastName, email = :email where id = :id");
        $sth->execute($data);

        http_response_code(202);
    }

    // Удаляет пользователя из БД
    public static function deleteUser(PDO $db, string $id)
    {
        $sth = $db->prepare("delete from users where id = :id");
        $sth->execute(['id' => $id]);

        http_response_code(204);
    }

    // Посылает http код и выводит массив $res
    public static function echoResponseCode(array $res, int $code)
    {
        http_response_code($code);
        echo json_encode($res);
    }

    // Проверяет существование id в БД
    private static function checkId(PDO $db, string $id)
    {
        $sth = $db->prepare("select * from users where id = :id");
        $sth->execute(['id' => $id]);
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            self::echoResponseCode(['User not found'], 404);
            die();
        }
    }

    // Проверяет массив данных на корректность
    private static function checkData(?array $data)
    {
        if (!isset($data)) {
            self::echoResponseCode(['The input data is incorrect'], 400);
            die();
        }
        if (!isset($data['firstName'])) {
            self::echoResponseCode(['The \'firstName\' field is incorrect'], 400);
            die();
        }
        if (!isset($data['lastName'])) {
            self::echoResponseCode(['The \'lastName\' field is incorrect'], 400);
            die();
        }
        if (!isset($data['email'])) {
            self::echoResponseCode(['The \'email\' field is incorrect'], 400);
            die();
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            self::echoResponseCode(['The \'email\' field is incorrect'], 400);
            die();
        }
    }
}