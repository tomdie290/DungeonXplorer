<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'autoload.php';

class Router
{
    private $routes = [];
    private $prefix;

    public function __construct($prefix = '')
    {
        $this->prefix = trim($prefix, '/');
    }

    public function addRoute($uri, $controllerMethod)
    {
        $this->routes[trim($uri, '/')] = $controllerMethod;
    }

    public function route($url)
    {
        $urlParts = explode('?', $url, 2);
        $path = trim($urlParts[0], '/');

        if ($this->prefix && strpos($path, $this->prefix) === 0) {
            $path = substr($path, strlen($this->prefix) + 1);
        }

        foreach ($this->routes as $route => $controllerMethod) {
            if ($route === $path) {
                list($controllerName, $methodName) = explode('@', $controllerMethod);
                $controller = new $controllerName();
                $controller->$methodName();
                return;
            }
        }
        require_once 'view/404.php';
    }
}

$router = new Router('DungeonXplorer');

$router->addRoute('', 'HomeController@index');
$router->addRoute('home', 'HomeController@index');
$router->addRoute('login', 'LoginController@index');
$router->addRoute('register', 'RegisterController@index');
$router->addRoute('account', 'AccountController@index');
$router->addRoute('hero', 'HeroController@index');
$router->addRoute('chapter', 'ChapterController@index');
$router->addRoute('chapter/choice', 'ChapterController@choice');
$router->addRoute('profil', 'ProfilController@index');
$router->addRoute('update_password', 'UpdatePasswordController@index');
$router->addRoute('adventure', 'AdventureController@index');
$router->addRoute('start_adventure', 'AdventureController@start');
$router->addRoute('logout', 'LogoutController@index');
$router->addRoute('combat', 'CombatController@index');


$url = $_SERVER['REQUEST_URI'];

$router->route($url);