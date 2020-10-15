<?php 

	$file = 'ip.log';
	$myIP = file_get_contents('https://ipecho.net/plain');
	exec('ping -c 1 loyd.duckdns.org', $duckIPret, $return);
	foreach($duckIPret as $line)
	{
	    if(preg_match("/loyd.duckdns.org.\((\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})\)/", $line, $match))
	    {
		$duckIP = $match[1];
		break;
	    }
	}   
	$file_content = $myIP."\n";
	$file_content .= $duckIP;
		
	file_put_contents($file, $file_content);
	print_r($file_content);
?>