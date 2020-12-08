<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL ^ E_NOTICE);



$db = require_once("db.php");

$maxThread = 10;
$difTime = time() - (3 * 24 * 60 * 60); //За сколько дней до конца отправлять уведомление.
$timeout = 10;


function is_run_by_pid($pid, $comm)
{
	$cmd = "ps -p ". $pid . " -o comm=";
	$name = exec($cmd);

	return ($name == $comm) ? 1 : 0;
}

function run_background($filename, $argc)
{
	$fullPathExec = (__DIR__) . "/" . $filename;
	$cmd = "/usr/bin/php ". $fullPathExec . " " . $argc . " >> /dev/null & echo $!";

	$pid = intval(exec($cmd));
	
	$process = array();
	$process["time"] = time();
	$process["result"] = 0;
	$process["pid"] =  $pid;

    return ($pid > 0) ? $process : 0;

}


$emailCollection = array();
$offset = 0;
$end = false;


function getEmails()
{
	global $db, $difTime, $emailCollection, $offset, $maxThread, $end;
	

	if($end) 
		return $emailCollection;
	
	if(count($emailCollection) > 10)
		return $emailCollection;
	
	
	$limit = $maxThread + $maxThread;

	
	$sql = "SELECT * FROM `users` LEFT JOIN `emails` USING(`email`)
		WHERE `users`.`email_confirmed` = 1 and `emails`.`valid` = 1 LIMIT " . $offset . "," . $limit . ";";
	
	
	$offset += $limit;
	
	//echo $sql . "\r\n";

	$resource = mysqli_query($db, $sql);
	if(!$resource) die("error sql: " . $sql);
	//var_dump($resource);
	
	$n = 0;
	while($row = mysqli_fetch_array($resource))
	{
		$emailCollection[] = $row;
		$n++;
	}
	
	if($n == 0) 
		$end = true;
		
	printf("Selected %d row total: %d\r\n", $n, count($emailCollection));
	
	return $emailCollection;

}

$taskList = array();

function createTask()
{
	global $taskList, $emailCollection;
	
	$emailCollection = getEmails();
	//die;
	
	
	foreach($emailCollection as $id => $item)
	{
		
		//var_dump($item); die;
		
		$process = array();
	
		$base64string = base64_encode(json_encode($item));
		$process = run_background("sender_instance.php", $base64string);
		if(!$process)
			return 0;
			
		
		//print_r($process); die;
		
		$taskList[] = $process;
		
		unset($emailCollection[$id]);
		return 1;
	}
	
	return 0;
}

printf("Start %d threads\r\n", $maxThread);


for($i = 0; $i < $maxThread; $i++)
{
	if(!createTask())
		break;
}

//print_r($taskList);die;



while(true) //check state process
{
	sleep(1);
	
	printf("Thread size %d Emails %d\r\n", count($taskList), count($emailCollection) + count($taskList));
	
	if(count($taskList) == 0)
	{
		printf("Task ended stack %d item\r\n", 0);
		break;
	}
	

	foreach($taskList as $id => $process)
	{
		$time = $process["time"];
		$pid = $process["pid"];


		
		if($time + $timeout < time())
		{
			posix_kill(-$pid, 9);
			echo "TTL expired send kill\r\n";
			unset($taskList[$id]);
			
			createTask();
		}
		
		if(!is_run_by_pid($pid, "php"))
		{
			echo "Thread exit\r\n";
			unset($taskList[$id]);
			createTask();
		}
		

	}
}