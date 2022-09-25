<?php 

class LIGHTS {

    public $content = '';
    public $style_color = 'default';    

    public function get_content()
    {
		$conn = new mysqli('localhost', 'lights', 'Lights123', 'lights');
		
		$sql = 'SELECT COUNT(id) AS count FROM `schedule` WHERE active = "X" ';
		$DBresult = $conn->query($sql);

		$conn->close();

		$crontab_is_on = shell_exec('crontab -l');
		if (strlen($crontab_is_on) > 1 )
			$crontab_is_on = TRUE;
		else
			$crontab_is_on = FALSE;

		while($Row = $DBresult->fetch_assoc())
		{
			$count = $Row['count'];
			if ($count > 0)
				$schedule_is_on = TRUE;
			else
				$schedule_is_on = FALSE;
		}

		if ($crontab_is_on && $schedule_is_on)
		{	$this->content = 'Active';
			$this->style_color = 'success';
		}
		else if (!$crontab_is_on && !$schedule_is_on)
		{
			$this->content = 'Not Active';
			$this->style_color = 'danger';
		}
		else if (($crontab_is_on && !$schedule_is_on))
		{
			$this->content = 'Crontab Active <br /> Scheduler Not Active';
			$this->style_color = 'warning';
		}
		else if ((!$crontab_is_on && $schedule_is_on))
		{
			$this->content = 'Crontab Not Active <br /> Scheduler Active';
			$this->style_color = 'warning';
		}
		$this->content .= '<br />'.$count.' - lights activated';

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