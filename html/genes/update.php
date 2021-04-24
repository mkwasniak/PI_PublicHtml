<?php
	$link = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/index.php";

/* 	// require_once('class/crontab.php');
	// $Crontab = new Crontab();

	// if(isset($_GET['Action']))
	// {
	// 	$Return = $Crontab->update_fields_from_link($_GET);
	// 	$ok_code = $Return['ok_code'];
	// 	$message = $Return['message'];
	// }
 */	header("Location: $link?ok_code=$ok_code&message=$message");
	exit();
?>