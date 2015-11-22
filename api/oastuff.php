<?php
require 'vendor/autoload.php';

$app = new \Slim\Slim();
$db = new mysqli("localhost", "root", "Foundry", "OADB" );

$app->get('/user', function(){
	global $db;
	$result = $db->query("SELECT name,id FROM users");
	$array1 = [];
	if($result->num_rows > 0)
		while($row = $result->fetch_assoc())
			array_push($array1, $row);
	echo json_encode(array("users"=> $array1));
});

$app->post('/user', function(){
	global $db;
	$id = $_POST['id'];
	$SMU = $_POST['SMU'];

	$result = $db->query("SELECT recipientId FROM users WHERE id='$id' AND smuId='$SMU'");
	if($result->num_rows > 0){
		$result = $result->fetch_assoc();
		$recipId = $result['recipientId'];
		if($recipId == -1){
			$result = $db->query("SELECT name, id FROM users WHERE secretSanta = -1 AND id != '$id' LIMIT 1");
			$result = $result->fetch_assoc();
			$recipId = $result['id'];
			$db->query("UPDATE users SET secretSanta = '$id' WHERE id = '$recipId'");
			$db->query("UPDATE users SET recipientId = '$recipId' WHERE id = '$id'");
			echo json_encode(array("name" => $result['name']));
		}
		else{
			$result = $db->query("SELECT name FROM users WHERE id = '$recipId'");
			echo json_encode(array("name" => $result->fetch_assoc()['name']));
		}
	}
	else{
		echo json_encode(array("result" => -1));
	}

});

$app->run();
?>