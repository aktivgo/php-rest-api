<?php

use aktivgo\PhpRestApi\App;
require_once "/var/www/composer/vendor/autoload.php";

$db = App::connectToDb();

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$url = rtrim($_SERVER['REQUEST_URI'], '/');
$urlArr = explode('/', $url);

if ($urlArr[1] != 'users' && !preg_match("/users\?page=\d+$/", $urlArr[1])) {
    App::echoResponseCode('The request is incorrect', 404);
    return;
}

$id = $urlArr[2];

$data = file_get_contents('php://input');
$data = json_decode($data, true);

if (!$id) {
    if ($method == 'GET') {
        App::getUsers($db, $_GET);
        return;
    }
    if ($method == 'POST') {
        App::addUser($db, $data);
        return;
    }

    App::echoResponseCode('The request is incorrect', 404);
    return;
}

if ($method == 'GET') {
    App::getUser($db, $id);
    return;
}

if ($method == 'PUT') {
    App::updateUser($db, $id, $data);
    return;
}

if ($method == 'DELETE') {
    App::deleteUser($db, $id);
    return;
}