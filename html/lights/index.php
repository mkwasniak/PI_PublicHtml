<?php 
    require_once('class/crontab.php');

    print("Lights On/Off Scheduler");
?>
<!-- Menu start -->
<table>
    <tr>
	<td><a href="../index.php">Back</a></td>
        <td><a href="host_refresh.php">Host Refresh</a></td>
        <td><a href="plugin_script.php">Logs</a></td>
        <td><a href="duck.log">Logs</a></td>
    </tr>
</table>
<!-- Menu end-->
<table>
    <tr>
	<td>No.</td>
	<td>Light Name</td>
	<td>Mon</td>
	<td>Tue</td>
	<td>Wed</td>
	<td>Thu</td>
	<td>Fri</td>
	<td>Sat</td>
	<td>Sun</td>
	<td>Time ON</td>
	<td>Time OFF</td>
	<td>MQTT Topic</td>
	<td>Active</td>
    </tr>
</table>
<?php

    $output = shell_exec('crontab -l');
    echo "<pre>shell\n $output \n </pre>";

    $conn = new mysqli('localhost', 'cod', 'cod123', 'cod');
    
    $sql = "SELECT * FROM updates ORDER BY release_date DESC";		
    
    $conn->close();
    $DBresult = $conn->query($sql);

    while($Row = $DBresult->fetch_assoc())
    {
?>
    <tr>
	<td><?php echo $Row['release_date'];?></td>
	<td><?php echo $Row['last_log_date'];?></td>
    </tr>
<?php
    }


    $conn->close();
?>