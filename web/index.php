<?php

use aktivgo\PhpRestApi\App;

require_once __DIR__ . "/composer/vendor/autoload.php";

use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

$context = null;
$parameters = null;

try {

    $routes = new RouteCollection();
    $context = new RequestContext(Request::createFromGlobals());

    // Роут для /users
    $routeUsers = new Route('/users');
    $routes->add('users', $routeUsers);

    // Роут для /users/id
    $routeUsersId = new Route('/users/{id}');
    $routes->add('usersId', $routeUsersId);

    $matcher = new UrlMatcher($routes, $context);
    $parameters = $matcher->match($context['pathInfo']);
} catch (Exception $e) {
    App::echoResponseCode('The request is incorrect', 404);
    return;
}

$db = App::connectToDb();

$data = file_get_contents('php://input');
$data = json_decode($data, true);

if (!$parameters['id']) {
    if ($context['method'] == 'GET') {
        App::getUsers($db, $_GET);
        return;
    }
    if ($context['method'] == 'POST') {
        App::addUser($db, $data);
        return;
    }

    App::echoResponseCode('The request is incorrect', 404);
    return;
}

if ($context['method'] == 'GET') {
    App::getUser($db, $parameters['id']);
    return;
}

if ($context['method'] == 'PUT') {
    App::updateUser($db, $parameters['id'], $data);
    return;
}

if ($context['method'] == 'DELETE') {
    App::deleteUser($db, $parameters['id']);
    return;
}