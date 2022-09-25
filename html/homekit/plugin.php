<?php 

class HOMEKIT {

    public $content = '';
    public $style_color = 'default';    

    public function get_content()
    {
	$this->content = '031-45-154';
	//$this->style_color = 'warning';
	$this->style_color = 'success';
	//$this->style_color = 'danger';
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