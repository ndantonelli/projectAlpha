<?php
require 'php-scrypt/scrypt.php';
require 'vendor/autoload.php';

$app = new \Slim\Slim();
$db = new mysqli("localhost", "root", "Foundry", "FoundryDB" );


/*
{
"pass":"hi",
"area":901,
"num":7777777
}
*/
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
		$id = $result['id'];
		if(Password::check($_POST['pass'], $result['pass'])){
			$result = $db->query("Select id, email, first, last, url, area, num FROM users WHERE id='$id'");
			echo json_encode((Object)mysqli_fetch_assoc($result), JSON_NUMERIC_CHECK);
		}
		else
			echo json_encode(array("id" => -1));
	}
});

$app->get('/tutors', function(){
	global $db;
	$tid = $_GET['tid'];
	$sid = $_GET['sid'];

	$result = $db->query("SELECT id, first, last, url, area, num FROM tutors INNER JOIN users ON tutors.uid = users.id WHERE tutors.status = 'active' AND tutors.tid = '$tid' AND tutors.sid = '$sid'");
	if($result->num_rows > 0){
		$array1 = [];
		while($row = $result->fetch_assoc())
			array_push($array1, $row);
		echo json_encode($array1);
	}
	else
		echo json_encode(array("type" => 1));
});


$app->post('/subjects', function(){
	global $db;
	$topic = $_POST['topic'];
	$sub = $_POST['sub'];
	$uid = $_POST['uid'];

	$result = $db->query("SELECT id FROM topics WHERE name = '$topic'");
	$id = 0;
	if($result->num_rows == 0){
		$db->query("INSERT INTO topics(name, url) VALUES ('$topic', 'google.com')");
		$id = $db->query("SELECT LAST_INSERT_ID()")->fetch_assoc()['LAST_INSERT_ID()'];
	}
	else
		$id = $result->fetch_assoc()['id'];

	$result = $db->query("SELECT id FROM subs WHERE name = '$sub' AND tid = '$id'");
	if($result->num_rows == 0){
		$db->query("INSERT INTO subs(tid, name, url) VALUES ('$id', '$sub', 'google.com')");
		$sid = $db->query("SELECT LAST_INSERT_ID()")->fetch_assoc()['LAST_INSERT_ID()'];

		$db->query("INSERT INTO tutors (uid,tid,sid) values ('$uid', '$id', '$sid')");
		echo json_encode(array("type"=> 0));
	}
	else{
		$sid = $result->fetch_assoc()['id'];
		$result = $db->query("SELECT uid FROM tutors WHERE uid = '$uid' AND tid = '$id' AND sid='$sid'");
		if($result->num_rows == 0){
			$db->query("INSERT INTO tutors (uid,tid,sid) values ('$uid', '$id', '$sid')");
			echo json_encode(array("type"=> 0));
		}
		else
			echo json_encode(array("type"=> 1));
	}
});

$app->get('/subjects', function(){
	global $db;
	if(isset($_get['tid'])){

	}
	else{
		$result = $db->query("SELECT * FROM topics");
		if($result->num_rows > 0){
			$array1 = [];
			while($row = $result->fetch_assoc()){
				$id = $row['id'];
				$subs = $db->query("SELECT * FROM subs WHERE tid = '$id'");
				if($subs->num_rows > 0){
					$array2 = [];
					while($row2 = $subs->fetch_assoc()){
						array_push($array2, $row2);
					}
					array_push($array1, array("id" => $id, "name" =>  $row['name'], "url" => $row['url'], "subs" => $array2));
				}
			}
			echo json_encode($array1);
		}
		else{
			echo json_encode(array("error" => 0));
		}
	}
});

$app->post('/validate', function(){
	global $db;
	$email = $_POST['email'];
	$area = $_POST['area'];
	$num = $_POST['num'];

	$vEmail = true;
	$vPhone = true;
	$result = $db->query("Select id FROM users WHERE email = '$email'");
	if($result->num_rows > 0)
		$vEmail = false;
	$result = $db->query("Select id FROM users WHERE area = '$area' AND num = '$num'");
	if($result->num_rows > 0)
		$vPhone = false;

	if($vEmail && $vPhone)
		echo json_encode(array("exists" => true, "invalid" => 0));
	else if($vEmail && !$vPhone)
		echo json_encode(array("exists" => false, "invalid" => 2));
	else if(!$vEmail && $vPhone)
		echo json_encode(array("exists" => false, "invalid" => 1));
	else
		echo json_encode(array("exists" => false, "invalid" => 3));
});

/*
{
"email":"n@n.com",
"pass":"hi",
"first":"bob",
"last":"smith",
"area":901,
"num":7777777,
"tut":0
}
*/
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
	
	$result = $db->query("SELECT id FROM users WHERE email = '$email' OR (num = '$num' AND area = '$area')");
	if($result->num_rows > 0)
		echo json_encode(array("id" => -1));
	else{
		$db->query("INSERT INTO users(email, pass, first, last, area, num, salt, tutor)
					VALUES ('$email', '$hash', '$first', '$last', '$area', '$num', '$salt', '$tut') ");
		$result = $db->query("SELECT LAST_INSERT_ID()");
		echo json_encode(array("id"=> $result->fetch_assoc()['LAST_INSERT_ID()']), JSON_NUMERIC_CHECK);
	}
});

$app->run();
?>