<?php

$db = require_once("db.php");

$pid = getmypid();
$startTime = time();


function send_email( $email, $from, $to, $subj, $body )
{
	sleep(rand(1, 10));
	return 1;
}

$data = (array)json_decode(base64_decode($argv[1]));
if(!isset($data["email"]) || !filter_var($data["email"], FILTER_VALIDATE_EMAIL))
	die("input string error: " . $argv[1] . "\r\n");

$email = $data["email"];


$result = send_email( $email, "admin@site.ru", $email, "Услуга заканчивается", "Бла бла бла продлите услугу"  );

if($result)
{
	$sql = "UPDATE `emails` SET `time_send` = " . time() . " WHERE `id` = " . $data["id"] . ";";
	mysqli_query($db, $sql);
}

