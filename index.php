<?php 
session_start();
require_once("vendor/autoload.php");
use \Slim\Slim;
use \Hcode\Page;
use \Hcode\PageAdmin;
use \Hcode\Model\User;
use \Hcode\Model\Category;

$app = new Slim();

$app->config('debug', true);

$dbopts = parse_url(getenv('postgresql-opaque-85309'));
Sql::setDBConfig($dbopts);
$app->register(new Csanquer\Silex\PdoServiceProvider\Provider\PDOServiceProvider('pdo'),
               array(
                'pdo.server' => array(
                   'driver'   => 'mysql',
                   'user' => $dbopts["user"],
                   'password' => $dbopts["pass"],
                   'host' => $dbopts["host"],
                   'port' => $dbopts["port"],
                   'dbname' => ltrim($dbopts["path"],'/')
                   )
               )
);

require_once("functions.php");
require_once("site.php");

require_once("admin.php");

require_once("admin-user.php");

require_once("admin-categories.php");

require_once("admin-product.php");

require_once("admin-orders.php");

$app->run();

 ?>