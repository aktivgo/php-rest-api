<?php

header('Content-type: json/application');

class App
{
    static function getUsers($db, $get)
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

    static function getUser($db, $id)
    {
        self::checkId($db, $id);

        $sth = $db->prepare("select * from users where id = $id");
        $sth->execute();
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        self::echoResponseCode($res, 200);
    }

    static function addUser($db, $data)
    {
        self::checkData($data);

        $sth = $db->prepare("insert into users values (null, :firstName, :lastName)");
        $sth->execute($data);

        http_response_code(201);
        echo $db->lastInsertId();
    }

    static function updateUser($db, $id, $data)
    {
        self::checkId($db, $id);
        self::checkData($data);

        $sth = $db->prepare("update users set firstName = :firstName, lastName = :lastName where id = $id");
        $sth->execute($data);

        http_response_code(202);
    }

    static function deleteUser($db, $id)
    {
        self::checkId($db, $id);

        $sth = $db->prepare("delete from users where id = $id");
        $sth->execute();

        http_response_code(204);
    }

    static function echoResponseCode($res, $code)
    {
        http_response_code($code);
        echo json_encode($res);
    }

    static function checkId($db, $id)
    {
        $sth = $db->prepare("select * from users where id = $id");
        $sth->execute();
        $res = $sth->fetch(PDO::FETCH_ASSOC);

        if (!$res) {
            self::echoResponseCode('User not found', 404);
            die();
        }
    }

    static function checkData($data)
    {
        if(!isset($data) || $data['firstName'] === '' || $data['lastName'] === '' || !isset($data['firstName']) || !isset($data['lastName'])) {
            self::echoResponseCode('The fields are incorrect', 400);
            die();
        }
    }
}