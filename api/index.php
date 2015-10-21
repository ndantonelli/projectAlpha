<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim();
$db = new mysqli("localhost", "root", "Foundry", "FoundryDB" );

$app->post('/auth', function(){
	echo -1;
});

$app->post('/user', function(){
	global $db;
	$email = $_POST['email'];
	$pass = $_POST['pass'];
	$first = $_POST['first'];
	$last = $_POST['last'];
	$area = $_POST['area'];
	$num = $_POST['num'];
	$tut = $_POST['tut'];

	$result = $db->query("SELECT id FROM users WHERE email = '$email' OR (num = '$num' AND area = '$area'");
	if($result->num_rows > 0)
		echo json_encode(array("id" => -1));
	else{
		$db->query("INSERT INTO user(email, pass, first, last, area, num, tutor)
					VALUES ('$email', '$pass', '$first', '$last', '$area', '$num', '$tut') ");
		$result = $db->query("SELECT LAST_INSERT_ID()");
		echo $result->fetch_assoc();
	}
});

$app->run();
?>