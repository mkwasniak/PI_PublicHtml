<?php 

class GENES {

    public $content = '';
    public $style_color = 'default';    

    public function get_content()
    {

		$this->content = 'Last entry: ...';
		$this->style_color = 'success';

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