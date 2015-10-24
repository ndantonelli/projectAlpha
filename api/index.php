<?php
require 'php-scrypt/scrypt.php';
require 'vendor/autoload.php';
require 'PHPMailer/class.phpmailer.php';

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

$app->post('/reset', function(){
	global $db;
	$area = $_POST['area'];
	$num = $_POST['num'];
	$result = $db->query("SELECT id FROM users WHERE area='$area' AND num = '$num'");
	if($result->num_rows == 0)
		echo json_encode(array("type" => 0));
	else{
		$salt = Password::generateSalt(6);
		$emailNum = $area + $num + "@vtext.com";
		// Instantiate Class
		$mail = new PHPMailer();
		 
		// Set up SMTP
		$mail->IsSMTP();                // Sets up a SMTP connection
		$mail->SMTPDebug  = 2;          // This will print debugging info
		$mail->SMTPAuth = true;         // Connection with the SMTP does require authorization
		$mail->SMTPSecure = "tls";      // Connect using a TLS connection
		$mail->Host = "smtp.live.com";
		$mail->Port = 587;
		$mail->Encoding = '7bit';       // SMS uses 7-bit encoding
		 
		// Authentication
		$mail->Username   = "ndantonelli@hotmail.com"; // Login
		$mail->Password   = "Nicholas1"; // Password
		 
		// Compose
		$mail->Subject = "";     // Subject (which isn't required)
		$mail->Body = "Your Reset code is: " + $salt;        // Body of our message
		 
		// Send To
		$mail->AddAddress( $emailNum + "@vtext.com" ); // Where to send it
		var_dump( $mail->send() );      // Send!
		//mail($emailNum, "", "You're reset code is " + $salt);
		echo json_encode(array("type" => 1));
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