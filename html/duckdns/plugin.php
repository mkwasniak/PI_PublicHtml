<?php 

class DUCKDNS {

    public $content = '';
    public $style_color = 'default';    

    public function get_content()
    {
	$file = file('/home/pi/public_html/duckdns/ip.log', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	$myIP = '';
	foreach($file as $ip)
	{
	    if($myIP == '')
	    {
		$myIP = $ip;
	    }
	    else
	    {
		$duckIP = $ip;
	    }

	}   
	$this->content = "Server's IP: ".$myIP;
	if($myIP == $duckIP)
	    {
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