<?php

use aktivgo\PhpRestApi\app\Activation;
use aktivgo\PhpRestApi\app\App;
use aktivgo\PhpRestApi\database\Database;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\HttpFoundation\Request;

require_once __DIR__ . "/composer/vendor/autoload.php";

try {
    // Роут для /users
    $routeUsers = new Route('/users');
    // Роут для /users/id
    $routeUsersId = new Route('/users/{id}', [], ['id' => '\\d+']);
    // Роут для подтверждения почты
    $routeUsersActivation = new Route("/users/activation");

    $routes = new RouteCollection();
    $routes->add('getUsers', $routeUsers);
    $routes->add('getUser', $routeUsersId);
    $routes->add('userActivation', $routeUsersActivation);

    $context = new RequestContext();
    $context->fromRequest(Request::createFromGlobals());

    $matcher = new UrlMatcher($routes, $context);
    $parameters = $matcher->match($context->getPathInfo());
} catch (Exception $e) {
    App::echoResponseCode(['The request is incorrect'], 404);
    return;
}

$db = Database::getConnection();

if ($parameters['_route'] === 'userActivation') {
    $token = $_GET['token'];
    if (!$token) {
        App::echoResponseCode(['The request is incorrect'], 404);
    }
    Activation::confirmEmail($token);
    return;
}

$data = file_get_contents('php://input');
$data = json_decode($data, true);

if (!$parameters['id']) {
    if ($context->getMethod() === 'GET') {
        App::getUsers($db, $_GET);
        return;
    }
    if ($context->getMethod() === 'POST') {
        App::addUser($db, $data);
        return;
    }

    App::echoResponseCode(['The request is incorrect'], 404);
    return;
}

if ($context->getMethod() === 'GET') {
    App::getUser($db, $parameters['id']);
    return;
}

if ($context->getMethod() === 'PUT') {
    $data['id'] = $parameters['id'];
    App::updateUser($db, $data);
    return;
}

if ($context->getMethod() === 'DELETE') {
    App::deleteUser($db, $parameters['id']);
    return;
}