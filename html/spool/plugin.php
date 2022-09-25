<?php 

class SPOOL {

    public $content = '';
    public $style_color = 'default';    

    public function get_content()
    {
		//to do, check if http://printserver:631/ is online
		if (true)
		{	$this->content = 'Online';
			$this->style_color = 'success';
		}
		else
		{
			$this->content = 'Offline';
			$this->style_color = 'danger';
		}
		$this->content .= '<br />http://printserver:631/';

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