<?php

  class Template {

	public $Template = '';  // contain Html tempalte
	public $TemplatePath = '';   // contain path to template
	public $TemplateSQL = '';  // last sql toget tempalte
	public $ReplaceTags = array();   // contain  repalace names
	public $ReplaceTemplates = array();   // contain  repalace names
	public $Replace = array();
	public $DBHandle = '';
	public $CFGName = 'CFG';
	public $TMPName = 'TEMPLATE';
	public $LNGName = 'LNG';
  
  
	/**
	*       Constructor 
	* 
	*       @param string $TemplatePath path value of 
	*       @param object $DBHandle object of template 
	*       @param string $CFGName name of cfg tags replace values prefix
	*
	*       @return boolean true|false 
	**/
         
    function Template($TemplatePath, $DBHandle='', $CFGName='', $TMPName='', $MNUName='')
    {
        global $DB;
        $this->SetPath($TemplatePath);
	$this->DBHandle = $DB;
		                   
        if ($CFGName != '' ) $this->CFGName = $CFGName;
        if ($TMPName != '' ) $this->TMPName = $TMPName;

        $this->Replace = array();
        return TRUE;
    }


  
  
	/**
	*       Replace CFG tags in given template  
	*       @param string $Template  contains template
	*
	*       @return string $Template template with replaced CFG tags with proper values 
	**/

	function ReplaceCFGTags($Template)
	{
            $Tags = array();
            $Tags = $this->GetTagsNames($this->Template,2);
            if (!count($Tags)) { return $Template;}
         
    	    foreach ($Tags as $name)
	    {
    		if(preg_match("/^".$this->CFGName."\_/",$name))
            	    {
                        $cfgname = preg_replace("/^".$this->CFGName."\_/","", $name);
                	$Template = preg_replace("/".$cfgname."/",$GLOBALS[$this->CFGName."[$cfgname]"],$Template);
                    }
            }
            return $Template;
	}


	/**
	*       Builds template 
	*  
	*       @param string $Template name of template or sql query to get template 
	* 
	*       @return boolean true|false
	**/

    function Start($Template)
    {
        if (!$this->Get($Template)) return FALSE;
        $this->BuildTemplate();
        return TRUE;
    }
    
    /**     
	*       Build Template 
	*       Replace all tags ###TEMPLATE_.....### with other templates ( specified in dotted place )
	*       e.i. ###TEMPLATE_print_header### will be replaced  with print_header template name (filename)
	* 
	*       @return bolean true|false 
	**/

    function BuildTemplate()
    {   
        if ($this->Template == '') { return; }
        preg_match_all("/(\#\#\#".$this->TMPName."\_.*?\#\#\#)/",$this->Template,$Founded);

        if (count($Founded[0]) == 0 ) { return; }
        foreach ($Founded[0] as $value)
        {
            $tname ='';
            $tname = str_replace($this->TMPName."_","", $value);
      	    $tname = str_replace("###","", $tname);
                 
            $this->ReplaceTemplates[$tname] = $this->TemplatePath.$tname;
            $this->Template = str_replace("".$value."",$this->ReadTemplate($this->TemplatePath.$tname),$this->Template);
        }
                 
        $this->BuildTemplate();
        $this->ReplaceTags = $this->GetTagsNames($this->Template,1);
        return TRUE;    
    }

	/**
	*       Gets tags names from given template
	*        
	*       @param string $Template Contains template
	*       @param int $Type define what type of tags to take 0 - all tags 1 - olny starting from ###TEMPLATE_  2 - starting from  ###$this->CFGName_
	*       @return array Tags list 
	**/

    function GetTagsNames($Template, $Type = 'All')
    {
        $Founded=array();
        $Types=array("",$this->TMPName,$this->CFGName,$this->LNGName,$this->MNUName);
        $Tags=array();

        preg_match_all("/\#\#\#(.*?)\#\#\#/",$Template,$Founded);
        foreach ($Founded[1] as $key => $value)
        {
    	    if($Type == 0 and !preg_match("/^".$this->TMPName."\_/",$value) and !preg_match("/^".$this->CFGName."\_/",$value) and !preg_match("/^".$this->LNGName."\_/",$value) and !preg_match("/^".$this->MNUName."\_/",$value))
            {
                array_push($Tags,$value);
            }
            else if ($Type == 'All')
            {
                array_push($Tags,$value);
            }
            else if (preg_match("/^".$Types[$Type]."/",$value) and $Type > 0)
            {
                array_push($Tags,$value);
            }
        }         
        return $Tags;
    }
    /**
    *       Read  all tags without CFG tags and Tempaltes tags
    *       It basis on the $ReplaceTags property array, which contains all    
    **/
             
    function ReadGlobalsTagsValues()
    {     
        if (!count($this->ReplaceTags)) { return FALSE; }
        foreach ($this->ReplaceTags as $name)
        {
	    if(!preg_match("/^".$this->CFGName."\_/",$name) && !preg_match("/^".$this->TMPName."\_/",$name) && !preg_match("/^".$this->LNGName."\_/",$name))
    	    {
                        $names=array();
                        preg_match("/^(.*?)\[(.*?)\]$/",$name,$names);
                        $value = '';
                        if (count($names)==3)
                        {
                           if(isset($GLOBALS[$names[1]][$names[2]]) && $names[1] != '' && $names[2] != '' ) {   $value = $GLOBALS[$names[1]][$names[2]]; }
                        }
                        else {
                            if(isset($GLOBALS[$name])) { $value = $GLOBALS[$name]; }
                             }
                        $this->SetTagValue($name,$value);

	    }
	} 
    }
   
   
    /**
    *       Perform standard replace process 
    *       1. replace all cfg values 
    *       2. replace all globals
    **/
       
    function ReplaceAllTags()
    {
       if ($this->Template == '') { return; }
         
        $this->ReplaceTags = $this->GetTagsNames($this->Template);
        $this->ReadCFGToTagsValues();
        $this->ReadGlobalsTagsValues();
        foreach ($this->Replace as $name => $value)
        {
             $this->Template = str_replace("###".$name."###",$value,$this->Template);
        }
    }
                           
    /**	
    *       Replace only those tags gethered in Replace array property 
    **/
                             
    function ReplaceTags()
    {
        if ($this->Template == '') { return; }
        foreach ($this->Replace as $name => $value)
        {
             $this->Template = str_replace("###".$name."###",$value,$this->Template);
        }

    }

    /**
    *       Replace CFG tags in Replace array property
    **/

    function ReplaceCFG()
    {
        if ($this->Template == '') { return; }
        $this->Replace = array();
        $this->ReplaceTags = $this->GetTagsNames($this->Template,2);
        $this->ReadCFGToTagsValues();
        foreach ($this->Replace as $name => $value)
        {
             $this->Template = str_replace("###".$name."###",$value,$this->Template);
        }
    }
       
    /**
    *       Read CFG (configuration) values to tags
    **/ 
        
    function ReadCFGToTagsValues()
    {        
        if (!count($this->ReplaceTags) ) { return FALSE; }
        
        foreach ($this->ReplaceTags as $name)
        {
    	    if(preg_match("/^".$this->CFGName."\_/",$name))
            {
                $cfgname = preg_replace("/^".$this->CFGName."\_/","", $name);
                if(isset($GLOBALS[$this->CFGName][$cfgname])) 
                { 
		    $this->SetTagValue($name,$GLOBALS[$this->CFGName][$cfgname]); 
            	}
            }
        }
    }        
   
    /**
    *       Sets values to replacement property array $Replace
    *  
    *       @param string $TagName  template tag name  
    *       @param string $Value value to replace with
    **/
                        
    function SetTagValue($TagName,$Value)
    {
         if (!isset($this->Replace[$TagName])) {  $this->Replace[$TagName] = $Value; }
         else if ($this->Replace[$TagName] == '') {  $this->Replace[$TagName] = $Value; }
         return TRUE;
    }


   
	/**
	*       Get template
	*       Uses class's publics defined contructor 
	*       Sets Template content to $Template property 
	* 
	*       @param string $Template Name of template file
	*       @return boolena true|false
	* 
	**/

    function Get($Template)
    {
        if (!file_exists($this->TemplatePath.$Template) || $Template == '' ) { return FALSE; }
        $this->Template = $this->ReadTemplate($this->TemplatePath.$Template);
        return TRUE;
    }

	/**
	*       Read template file and return its value 
	* 
	*       @param string $File path to tempalte file
	*       @return string template content 
	**/
        
    function ReadTemplate($File)
    {
        if (!file_exists($File)) { return FALSE; }
        $Content = '';
        if (!$fh = fopen($File,"r")) { return FALSE; }
        while (!feof($fh)) { $Content.= fread($fh,10000); }
        fclose($fh);    
        return $Content;
    }

	
	/**
	*       Set templates main path 
	* 
	*       @param string $TemplatePath  path to templates dir
	*       @return boolean tru|false 
	**/

        function SetPath($TemplatePath)
	{
    	    if ($TemplatePath == '' || !file_exists($TemplatePath)) { return FALSE; }
    	    $this->TemplatePath = $TemplatePath;
  	    return TRUE;
	}
	
	
	/**
	*       Return Current $Template property value  
	*
	*       @return string 
	**/
   
	function Show()
	{
    	    return $this->Template;
	}
    
    }
?>