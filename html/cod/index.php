<?php 
    print("CoD: Modern Warfare Updates");
    ?>
    <table width="100%" cellpadding="5px">
    <tr>
        <td style="font-weight: bold;">Update Release</td>
        <td style="font-weight: bold;">Last logged</td>
    </tr>
    <?php

    $conn = new mysqli('localhost', 'cod', 'cod123', 'cod');
    
    $sql = "SELECT * FROM updates ORDER BY release_date DESC";
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