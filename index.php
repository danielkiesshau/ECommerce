<?php 

require_once("vendor/autoload.php");

$app = new \Slim\Slim();

$app->config('debug', true);

$app->get('/', function() {
    $page = new \Hcode\Page();
    $page->setTpl("index");
	$sql = new \Hcode\DB\Sql();
   

});

$app->run();

 ?>