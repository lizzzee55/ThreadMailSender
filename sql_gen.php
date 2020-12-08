<?php

$db = require_once("db.php");

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

for($i = 0; $i < 100; $i++)
{
	$email = generateRandomString(10) . "@mail.ru";
	$sql = sprintf("INSERT INTO `users` (`username`, `email`, `validts`, `email_confirmed`) VALUES ('%s', '%s', %d, %d);",
			generateRandomString(10), $email, time() + (rand(5, 15) * 24 * 60 * 60), (rand(0,9) > 3) ? 1 : 0);
	
	mysqli_query($db, $sql);
	$sql = sprintf("INSERT INTO `emails` (`email`, `check`, `valid`) VALUES ('%s', '%s', %d);",
			$email, 0, 0);
	
	mysqli_query($db, $sql);
	echo "insert id: " . mysqli_insert_id($db) . "\r\n";
}

