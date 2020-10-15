<?php 

//template plugin --- change class name and all references

class COD_MW {

    public $content = '';
    public $style_color = 'default';    

    public function get_content()
    {
	$conn = new mysqli('localhost', 'cod', 'cod123', 'cod');
    
	$sql = "SELECT * FROM updates ORDER BY release_date DESC LIMIT 1";
	$DBresult = $conn->query($sql);

	$conn->close();

	while($Row = $DBresult->fetch_assoc())
	{
	    $this->content = 'Last update on: '.$Row['release_date'];
	    //calculate days from last game update
    	    $now = time();
    	    $past = strtotime($Row['release_date']);
    	    $datediff = $now - $past;
    	    $rel_days = floor($datediff/(60*60*24));

	    //calculate hours from last log update
    	    $past = strtotime($Row['last_log_date']);
    	    $timediff = $now - $past;
    	    $upd_hours = floor($timediff/(60*60));

	    if($rel_days > 2)
	    {
		$this->style_color = 'warning';
	    }
	    else
	    {
		$this->style_color = 'success';
	    }
	    if($upd_hours > 1)
	    {
		$this->style_color = 'danger';
	    }
	}
    }

    //need to create class from variable
    public function __construct(){
    //    $this->properties = $args;        
	$this->get_content();
    }

    public function __get($name) {
        if (array_key_exists($name, $this->properties)) {
            return $this->properties[$name];
        }
        return null;
    } 
}
?>