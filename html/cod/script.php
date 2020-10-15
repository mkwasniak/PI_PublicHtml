<?php 
    echo 'CoD MW Script';
    $timestamp = date('__Y-m-d__H_i_s');
    $conn = new mysqli('localhost', 'cod', 'cod123', 'cod');
    $html = file_get_contents('https://support.activision.com/modern-warfare/articles/latest-updates-for-call-of-duty-modern-warfare');
    $htmlLines = explode("\n", $html);

    foreach($htmlLines as $line)
    {
	$ret = preg_match("/<span.*class=\"pubdate\".*>.*(\d{2}\/\d{2}\/\d{2})/", $line, $match);
	if($ret)
	{
	    var_dump($match[1]);
	    $release = $newDate = date("Y-m-d", strtotime($match[1]));
	}
    }	

    $sql = "INSERT INTO updates (release_date, last_log_date) VALUES('".$release."', NOW()) ON DUPLICATE KEY UPDATE release_date='".$release."', last_log_date=NOW()";
    $DBresult = $conn->query($sql);

    $conn->close();
?>