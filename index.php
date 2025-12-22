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

        if ($this->prefix && str_starts_with($path, $this->prefix)) {
            $path = trim(substr($path, strlen($this->prefix)), '/');
        }

        if (isset($this->routes[$path])) {
            list($controllerName, $methodName) = explode('@', $this->routes[$path]);
            $controller = new $controllerName();
            $controller->$methodName();
            return;
        }

        require_once 'view/404.php';
    }
}

$router = new Router('DungeonXplorer');

define('BASE_URL', __DIR__);

$router->addRoute('', 'HomeController@index');
$router->addRoute('home', 'HomeController@index');
$router->addRoute('login', 'LoginController@index');
$router->addRoute('register', 'RegisterController@index');
$router->addRoute('account', 'AccountController@index');
$router->addRoute('delete_hero', 'AccountController@deleteHero');
$router->addRoute('delete_account', 'AccountController@deleteAccount');
$router->addRoute('inventory', 'InventoryController@index');
$router->addRoute('hero', 'HeroController@index');
$router->addRoute('chapter', 'ChapterController@index');
$router->addRoute('chapter/choice', 'ChapterController@choice');
$router->addRoute('chapter/quit', 'ChapterController@quit');
$router->addRoute('profil', 'ProfilController@index');
$router->addRoute('update_password', 'UpdatePasswordController@index');
$router->addRoute('adventure', 'AdventureController@index');
$router->addRoute('adventure/resume', 'ChapterController@resume');
$router->addRoute('start_adventure', 'AdventureController@start');
$router->addRoute('logout', 'LogoutController@index');
$router->addRoute('combat', 'CombatController@start');
$router->addRoute('combat/end', 'CombatController@endCombat');
$router->addRoute('deconnexion', 'DeconnexionController@index');
$router->addRoute('admin', 'AdminController@index');
$router->addRoute('manage_chapters', 'AdminController@manageChapters');
$router->addRoute('manage_chapters/store',  'AdminController@storeChapter');
$router->addRoute('manage_chapters/edit',   'AdminController@editChapter');
$router->addRoute('manage_chapters/update', 'AdminController@updateChapter');
$router->addRoute('manage_accounts', 'AdminController@manageUsers');
$router->addRoute('manage_accounts/delete', 'AdminController@deleteUser');
$router->addRoute('manage_accounts/edit', 'AdminController@editUser');
$router->addRoute('manage_accounts/update', 'AdminController@updateUser');
$router->addRoute('manage_images', 'AdminController@manageImages');
$router->addRoute('manage_images/upload', 'AdminController@uploadImage');
$router->addRoute('manage_images/delete', 'AdminController@deleteImage');
$router->addRoute('manage_images', 'AdminController@manageImages');
$router->addRoute('manage_treasures', 'AdminController@manageTreasures');
$router->addRoute('manage_monsters', 'AdminController@manageMonsters');
$router->addRoute('manage_monsters/store', 'AdminController@storeMonster');
$router->addRoute('manage_monsters/edit', 'AdminController@editMonster');
$router->addRoute('manage_monsters/update', 'AdminController@updateMonster');
$router->addRoute('manage_monsters/delete', 'AdminController@deleteMonster');

$url = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


$router->route($url);
