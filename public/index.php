<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . "/../vendor/autoload.php";

use App\Controllers\ArticleController;
use App\Response\RedirectResponse;
use Twig\Loader\FilesystemLoader;
use App\Response\ViewResponse;
use Twig\Environment;

$loader = new FilesystemLoader(__DIR__ . '/../app/views');
$twig = new Environment($loader);

if (isset($_SESSION["flush"])) {
    $twig->addGlobal("flush", $_SESSION["flush"]);
}

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$dispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $r) {
    $r->addRoute("GET", "/", [ArticleController::class, "index"]);
    $r->addRoute("GET", "/article/create", [ArticleController::class, "create"]);
    $r->addRoute("POST", "/article", [ArticleController::class, "store"]);
    $r->addRoute("GET", "/article/{id}", [ArticleController::class, "show"]);
    $r->addRoute("GET", "/article/edit/{id}", [ArticleController::class, "edit"]);
    $r->addRoute("POST", "/article/edit/{id}", [ArticleController::class, "update"]);
    $r->addRoute("POST", "/article/delete/{id}", [ArticleController::class, "delete"]);
});

[$httpMethod, $uri] = [$_SERVER['REQUEST_METHOD'], rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))];
$routeInfo = $dispatcher->dispatch($httpMethod, $uri);

switch ($routeInfo[0]) {
    case FastRoute\Dispatcher::FOUND:
        $vars = $routeInfo[2];

        [$controller, $method] = $routeInfo[1];

        $response = (new $controller)->{$method}(...array_values($vars));

        switch (true) {
            case $response instanceof ViewResponse:
                echo $twig->render($response->getViewName() . '.twig', $response->getData());
                if (isset($_SESSION["flush"])) {
                    unset($_SESSION["flush"]);
                }
                break;
            case $response instanceof RedirectResponse:
                header('Location: ' . $response->getLocation());
                break;
            default:
                break;
        }
        break;
    case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
        $allowedMethods = $routeInfo[1];
        break;
    default:
        echo "404. Not Found.";
}