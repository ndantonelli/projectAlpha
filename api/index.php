<?php
require 'php-scrypt/scrypt.php';
require 'vendor/autoload.php';

$app = new \Slim\Slim();
$db = new mysqli("localhost", "root", "Foundry", "FoundryDB" );

$app->post('/auth', function(){
	global $db;
	$result;
	if (isset($_POST['email'])){
		$email = $_POST['email'];
		$result = $db->query("SELECT id, pass FROM users WHERE email='$email'");
	}
	else{
		$area = $_POST['area'];
		$num = $_POST['num'];
		$result = $db->query("SELECT id, pass FROM users WHERE area='$area' AND num = '$num'");
	}
	if($result->num_rows == 0)
		echo json_encode(array("id" => 0));
	else{
		$result = $result->fetch_assoc();
		if(Password::check($_POST['pass'], $result['pass']))
			echo json_encode(array("id" => $result['id']));
		else
			echo json_encode(array("id" => -1));
	}
});

$app->post('/user', function(){
	global $db;
	$email = $_POST['email'];
	$first = $_POST['first'];
	$last = $_POST['last'];
	$area = $_POST['area'];
	$num = $_POST['num'];
	$tut = $_POST['tut'];

	$salt = Password::generateSalt(50);
	$hash = Password::hash($_POST['pass'], $salt);
	// if(Password::check("bye", $hash))
	// 	echo json_encode(array("id"=>1));
	// else
	// 	echo json_encode(array("id"=>0));

	
	$result = $db->query("SELECT id FROM users WHERE email = '$email' OR (num = '$num' AND area = '$area')");
	if($result->num_rows > 0)
		echo json_encode(array("id" => -1));
	else{
		$db->query("INSERT INTO users(email, pass, first, last, area, num, salt, tutor)
					VALUES ('$email', '$hash', '$first', '$last', '$area', '$num', '$salt', '$tut') ");
		$result = $db->query("SELECT LAST_INSERT_ID()");
		echo json_encode(array("id"=> $result->fetch_assoc()['LAST_INSERT_ID()']));
	}
});

$app->run();
?>