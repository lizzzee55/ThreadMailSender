<?php

$db = require_once("db.php");

$pid = getmypid();
$startTime = time();


function check_email( $email )
{
	sleep(rand(1, 60));
	return rand(0, 1);
}

$data = (array)json_decode(base64_decode($argv[1]));
if(!isset($data["email"]) || !filter_var($data["email"], FILTER_VALIDATE_EMAIL))
	die("input string error: " . $argv[1] . "\r\n");

$email = $data["email"];


$result = check_email( $email );

function saveToSql($data)
{
	global $db;
	
	$sql = "UPDATE `emails` SET `check` = 1, `valid` = " . intval($data["check"]) . " WHERE `id` = '" . $data["id"] . "';";
	mysqli_query($db, $sql);
	//echo "save\r\n";
}


$data = array("id" => $data["id"], "check" => $result);

saveToSql($data);
