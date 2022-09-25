<?php 

//template plugin --- change class name and all references

class INTERNET {

    public $content = '';
    public $style_color = 'default';     

    public function get_content()
    {
		$test = file_get_contents("/var/www/html/internet/script_result");
		$test = trim($test);
		$this->content = $test;
		if ($test == "Online") {
			$this->style_color = 'success';
		}
		else
		{
			$this->style_color = 'danger';
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