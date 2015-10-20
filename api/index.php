<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim();
//$database = new mysqli("localhost", "root", "12345678", "BurritoDB" );

$app->post('/auth', function(){
	echo -1;
});

$app->post('/user', function(){
	echo -1;
});

$app->run();
?>