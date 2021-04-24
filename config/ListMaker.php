<?php
/**
*  Example of new use  
* 
*  new Listmaker ( $DB );
*  ListConf ($PerPage,$Page,$Header,$Link);<br>
*  SqlConf ($Fields = array(),$SqlMiddle, $SqlAfterWhere) {<br>
*  SearchConf  ($Fields = array());<br>
*  SortConf ($Fields = array(),$OrderField,$OrderType)<br>
*  return(DB_RESULT_HANDLE,COUNT)  execute
*
*	Description of class
*
*	Class produces list of elements

**/

class ListMaker  { 

/**
*	@var $ListCFG array List of configs 
**/
   var $ListCFG = array(
			   "Host" => "http://host",
			   "ItemPerPage" => 20, 
			   "NavLinks" => 20,
			   "NavType" => "NavLinks",
			   "LinkAdd" => "",
			   "PageShow" => 20,
			   "PageCount" => 0,
			   "Page" => 0 ); 

/**
*	@var $Html array Html elements 
**/
			   
   var $Html = array(
			   "NavSelect" => "",   
			   "NavLinks" => "",
			   "NavByPage" => "",
		    );


    var $DBHandle = undef;
    var $SqlFields = array();

    var $SqlOrder = "";
    var $SqlMiddle = ''; // sql 
    var $SqlAfterWhere = ''; // sql 
		
    var $SqlSearch  = array(); // czesc sql zawierajaca predykat;
    var $SqlPredicate = "";   	
    var $SqlCountExe = "";   
    var $SqlExe = " ";
	
    var $Nav = array();
    var $Result;   
// 
    var $CountTaken; // ilosc rekordow w aktualnej stronie 
    var $CountAll; // ilosc wszystkich rekord�w spelniajacych kryteria
//
    var $OrderFields = array();  // lista pol po ktorych mozna sortowac
    var $OrderNameFields = array(); // Nazwy pol po ktorych mozna sortowac 
    var $OrderField =0;
    var $OrderType =0;

//
    var $HeaderTemplate = ''; // template  naglowka listy 
    var $InitValues = 0; //oznacza zainicjowanie zmiennych lkusty
    var $NavType = array('NavSelect','NavLinks','NavbyPage');  // typy nawigacji 

	
  function ListMaker  (&$DBHandle) {

    global $ErrHandle;
       
    if (!is_object($DBHandle)) return FALSE;
	$this->DBHandle = &$DBHandle;
    $this->InitValue = 1;

    return TRUE;	  
  } 


/**
*  Sets sort fields 
*
*  array format 
*  
*  @param array $Fields contains sort field
*  @param int $OrderField index of field to order 
*  @param int $OrderType type of order  up/down 
* 
**/

  function SortConf($Fields = array(),$OrderField='0',$OrderType ='1')
  {
      if (count($Fields) == 0 or !is_array($Fields) ) { return FALSE; }

      if ($OrderField=='') $OrderField = '0';
      if ($OrderType == '')  $OrderType= '1';
            
      if ($Fields[$OrderField] == '' ) $OrderField = 0;
      $this->OrderFields = $Fields;
      $this->SortOrder($OrderType,$OrderField);

      return TRUE;	  
  }     

/**
*  Sets search field values 
*  array format 
*
*    (   array(url_name,value,and_or," sql comparision  "), )
*  
*  @param array $Fields search fields array 
* 
**/

  function SearchConf  ($Fields = array())
  {
       if (count($Fields) == 0 or !is_array($Fields) ) { return FALSE; }

	   foreach ($Fields as $key => $value )
	   {
    	  $this->SearchField($value[0],$value[1],$value[2],$value[3]);
	   }  

       return TRUE;	  
  } 


/**
*   Sets some of list values 
* 
*   @param int $PerPage rows per page 
*   @param int $Page current page 
*   @param string $Header List html header 
*   @param string $Link Base link  
*
**/
 
  function ListConf($PerPage,$Page,$Header,$Link) 
  {
   if ($Header == '' or  $Link == '') { return FALSE; }

   if ($Page != '') { $this->ListCFG['Page'] = $Page;  } 
   if ($PerPage > 0 ) { $this->ListCFG['ItemPerPage'] = $PerPage;  }    
   if ($Header != '' ) { $this->HeaderTemplate = $Header;  } 

   $this->ListCFG['Host'] = $Link;   

   return TRUE;
    
  } 

/**
*	Sets configuration for sql query 
* 
*	@param $SqlMiddle
* 	@param $SqlAfterWhere
**/


  function SqlConf ($Fields =array(),$SqlMiddle, $SqlAfterWhere) {

     if (count($Fields) == 0 or !is_array($Fields) or $SqlMiddle == '' ) { return FALSE; }

     $this->SqlFields = $Fields;	 
     $this->SqlMiddle = $SqlMiddle;
     $this->SqlAfterWhere = $SqlAfterWhere;

     return TRUE;     
  } 

/**
*	Starts creating list and returns list 
* 
*	@return array 
**/


  function execute() {

      global $ErrHandle; 

      if (count($this->SqlFields) == 0 or $this->SqlMiddle == '' or $this->ListCFG['Host'] =='' or $this->HeaderTemplate =='' ) { return FALSE; }     

      $this->SqlCfg();
      $this->ListDB();

      if ($this->CountAll > 0 and $this->CountTaken > 0)  {
	  
             $this->Nav($this->CountAll,$this->CountTaken);             
             return array($this->Result,$this->CountTaken);
      }
      else { return array(0,0); }
  }

/**
*	Includes values into array
*
* 	@param $Fields array 
**/  
      
  function Fields($Fields) {
   
         foreach ($Fields as $value) 
         {        
             $this->SqlFields[] = $value;    
         } 
  } 

/*
*	Includes names of columns into array
*
* 	@param $Fields array 
**/
      
  function OrderFields($Fields) {

         if(count($Fields) == 0) return;
         foreach ($Fields as $sqlname => $name) 
         {                  
             $this->OrderFields[] = array($sqlname,$name);
         } 
		 
  } 


/**
*	Sets type of order for DB query
*
* 	@param $Order string type of order
* 	@param $Field string order by
*
*	@return boolean true|false
**/


  function SortOrder($Order,$Field) {
              
     if ($Field == '' or $Field > (count($this->OrderFields)-1) or $Field < 0  ) { return FALSE; }   

     $this->OrderField = $Field; 
     $this->OrderType = $Order;

     $this->SqlOrder .= "ORDER BY ".$this->OrderFields[$this->OrderField];

     if ($Order == '1') {  $this->SqlOrder .= ' ASC '; } 
     else { $this->SqlOrder .= ' DESC '; }   

     return TRUE;

  } 

/**
*  Set value to ListCFG property array 
*  
*  @param string $Name  key name of propery array 
*  @param string $Value  key value of propery array
* 
*  @return boolean true|false
**/

  function CFG($Name,$Value) {

      if (array_key_exists($Name,$this->ListCFG))
      {      
          $this->ListCFG[$Name] = $Value;
          return TRUE;
      }
      return FALSE;   
  } 

  
/**
*	Adds 'and' string if query has more complicity
*	and includes params into $SqlSearch array       
*
*	@param $Name string
*	@param $Value string
*	@param $Type string
*	@param $Sql string
*
*	@return boolean true|false  
**/

  function SearchField($Name,$Value,$Type,$Sql) {


      if ($Name == '' or $Sql == '') return FALSE ;
      if ($Type == '') $Type = ' and '; 

      $this->SqlSearch[] = array($Name,$Value,$Type,$Sql); 
      return TRUE;   
  } 

/**
*	Generates SQL from $SqlSearch property array 
* 
*	@return string Generated sql query
**/  
  
  function SearchField_Cfg() {
 
      if (!is_array($this->SqlSearch)) { return FALSE; } 
      $Sql = '';

      foreach ($this->SqlSearch as $key => $value)
      {
            if ($value[1] == '') continue;     
            if ($Sql =='') {  $Sql .= " ".$value[3];  }           
            else { $Sql .= $value[2]."  ".$value[3];  }         
      }
      return $Sql;
  } 

/**
*   Get value of ListCFG 
*
*   @param string $Name key name for value
* 
*   @return string Value of given key
*  
**/

  function GetCFG($Name) {

      return $this->ListCFG[$Name];      
      return FALSE;   
  } 

/**
*  Return current value of Nav array 
*  
* */

  function GetNav() {

      if (!is_array($this->Nav)) { return; }        
      return $this->Nav;

  } 

/**
* 	Deconstructor
*
**/
 
  function clear() {


    $ListCFG = array();
    $Html = array();
    $SqlFields = array();
    $SqlOrder = "";
    $SqlMiddle = ''; // sql 
    $SqlAfterWhere  = '';
    $SqlSearch  = array(); // czesc sql zawierajaca predykat;

    $SqlCountExe = "";   
    $SqlExe = " ";
    $Nav = array();
    $Result;   
// 
    $CountTaken; // ilosc rekordow w aktualnej stronie 
    $CountAll; // ilosc wszystkich rekord�w spelniajacych kryteria
//
    $OrderFields = array();  // lista pol po ktorych mozna sortowac
    $OrderNameFields = array(); // Nazwy pol po ktorych mozna sortowac 

//
    $HeaderTemplate = ''; // template  naglowka listy 

    $InitValues = 0;//oznacza zainicjowanie zmiennych lkusty
// 

  } 


/**
*  	Return NavLinks navigator value  
* 
*	@return string Links for html code
**/  
  
  function NavLinks() {

      if (!is_array($this->Nav)) { return; } 

      $this->Html['NavLinks'] ='';
      
      foreach ($this->Nav as $key => $value)    
      {
         if (!is_numeric($key)) continue; 	  
	 if ($key == $this->ListCFG['Page']+1)
	 {
	    $this->Html['NavLinks'] .= '<a class="actual" href="'.$this->Nav[$key].'" > '.$key.' </a>&nbsp;'; 
	 }
	 else
            $this->Html['NavLinks'] .= '<a href="'.$this->Nav[$key].'" > '.$key.' </a>&nbsp;'; 
      }

      return $this->Html['NavLinks']; 
  } 

/**
*	Return set of links first, previous, next, last
*
*	@return array Links
**/  
  
  function NavLinksSet() {

      if (!is_array($this->Nav)) { return; } 	  
      
      $link =  $this->ListCFG['Host'].$this->ListCFG['LinkAdd'].'&page=0&orderfield='.$this->OrderField.'&ordertype='.$this->OrderType;
      $last = $this->Nav[(count($this->Nav)-2)];
      if ($last == '')  $last = $this->ListCFG['Host'].$this->ListCFG['LinkAdd'].'&page='.$this->ListCFG['PageCount'].'&orderfield='.$this->OrderField.'&ordertype='.$this->OrderType;      
             
      $previous = $this->Nav["previous"];
      if ($previous == '')  $previous = $link; 
      $next = $this->Nav["next"];
      if ($next == '')  $next = $last; 

      
      return array(
      $link,
      $previous,
      $next,$last); 
  }
   
/**
*	Returns set of values for pages first, previous, next, last	
*
*	@return array Numerical representation of links
**/

  function NavNumberSet() {

      if ($this->ListCFG['PageCount'] =='' ) { return; } 	  
      
      $link =  '0';
      $last =  $this->ListCFG['PageCount'];         
      $previous = $this->ListCFG['Page']-1;     
      if ($previous == '' or $previous < 0)  $previous = $link; 
      $next = $this->ListCFG['Page']+1;     
      if ($next == '' or $next > $this->ListCFG['PageCount'])  $next = $last; 
      return array(
      $link,
      $previous,
      $next,$last); 
  }
  
/**
*	Returns text of navigation
*
*	@return string
**/  
  
  function NavText() {

      return $this->Html['NavText'];
  } 

/**
*	Changes actual page and checks if
*	it is is greater then last or smaller
*	than 1
* 
*	@param $Previous int Value 
*	@param $Next int   
**/
  
  function Link($Previous,$Next) {

      if ($this->Page >= 1) 
      {
         $prev = ($this->Page-1);
         $this->Html['NavByPage']  =  '<a href="'.$this->ListCFG['Host'].$this->ListCFG['LinkAdd']."&page=".$prev.'&orderfield='.$this->OrderField.'&ordertype='.$this->OrderType.'" >'.$Previous.'</a>'; 
      } 

      if ($this->ListCFG['Pages']  >= ($this->Page+1))
      {
          $next = ($this->Page+1);
          $this->Html['NavByPage']  .=  '<a href="'.$this->ListCFG['Host'].$this->ListCFG['LinkAdd']."&page=".$next.'&orderfield='.$this->OrderField.'&ordertype='.$this->OrderType.'" >'.$Next.'</a>'; 
      }

      return $this->Html['NavByPage']; 
  } 


/**
*	Creates navigation link for previous and  page 
* 
*	@param $Previous int 
*	@param $Next int 
*
*	@return int
**/  
  
  function NavByPage($Previous,$Next) {

      if (count($this->Nav) > 0) {

         $this->Html['NavByPage']  .= '<a href="'.$this->Nav['previous'].'" > '.$Previous.' </a>';		
         $this->Html['NavByPage']  .= '<a href="'.$this->Nav['next'].'" > '.$Next.' </a>';		

         return $this->Html['NavByPage']; 
	  }
  } 


/**
*	Creates html select navigation
* 
*	@see SelectHtml->Select
**/

  function NavSelect() {

    if (!is_array($this->Nav)) { return FALSE; } 
    $Values = array();       
	
	require_once("SelectHtml.php");
    $shtml = new SelectHtml();

    $Select = '<SCRIPT LANGUAGE=JAVASCRIPT TYPE="text/javascript">
	<!--
	function ChangePage(object) {         
          value = object.options[object.selectedIndex].value; if (value==\'\') return;
          window.parent.location.href =  \''.$this->ListCFG['Host'].$this->ListCFG['LinkAdd'].'&orderfield='.$this->OrderField.'&ordertype='.$this->OrderType.'&page='.'\'+value; 
	}
	//-->
	</script>';
              
//      for ($i=0;$i <= $this->ListCFG['PageCount'];$i++)    
      for ($i=0;$i <= $this->ListCFG['PageCount'];$i++)    
      {
                   	  $key = $i+1;
                      $Values[$key] = $i;        
      }
      return $Select.$shtml->Select("KEYVALUE","page",' onChange="Javascript:ChangePage(this);"  ',"","",array( "---" => "" ),$Values,array($this->ListCFG['Page']),"",""); 

  } 

/**
*	Return Nav array 
* 
* 	@return array
**/
  
  
  function NavArray() {

      if (!is_array($this->Nav)) { return FALSE; } 
      return $this->Nav; 
  } 

/**
*	Makes header of list with sorting links
*	for each column
* 
* 	@return boolean true|false
**/

  function Header() {

      if (!is_array($this->OrderFields)) { return FALSE; }       
      $Field = array(); 
      if ($this->OrderType == '' or $this->OrderType == '0') $Field[$this->OrderField] = 1;      

      foreach ($this->OrderFields as $id => $value) 
      {
         $this->Html['H_'.$value.'_down'] .= $this->ListCFG['Host'].$this->ListCFG['LinkAdd'].'&page='.$this->ListCFG['Page'].'&orderfield='.$id.'&ordertype=0';
         $this->Html['H_'.$value.'_up'] .= $this->ListCFG['Host'].$this->ListCFG['LinkAdd'].'&page='.$this->ListCFG['Page'].'&orderfield='.$id.'&ordertype=1';	  
     	 $this->Html['H_'.$value.'_current'] .= $this->ListCFG['Host'].$this->ListCFG['LinkAdd'].'&page='.$this->ListCFG['Page'].'&orderfield='.$id.'&ordertype='.$Field[$id];	  
      }
      return TRUE; 
  } 

/**
*	Replaces string to names in headers 
*
*	@return array Headers names
**/

  function ListHeader() { 
           
      if ($this->HeaderTemplate == '') { return FALSE; }
	  $this->Header();
      $this->ListHeader = $this->HeaderTemplate;
      foreach ($this->Html as $name => $value )
      {
         if (preg_match("/^H\_/",$name)) 
         {                      
            $rname = str_replace("H_",'',$name);    			
            $this->ListHeader = preg_replace("/###".$rname."###/",$value,$this->ListHeader);             
         }
      }
      return  $this->ListHeader;
  }

/**
*	Replaces string to names in headers 
*
*	@param $CountAll int
*	@param $CountTaken int 
*
*	@return array Headers names
**/

   
function Nav($CountAll,$CountTaken) {
     
  $this->ListCFG['LinkAdd'] = '';
  foreach  ($this->SqlSearch as  $value) 
  {
		$this->ListCFG['LinkAdd'] .= "&".$value[0]."=".$value[1];
  }

  if ($CountAll > $this->ListCFG['ItemPerPage']) 
  {
  
   $this->ListCFG['PageCount'] = ($CountAll%($this->ListCFG['ItemPerPage']));

   if ($this->ListCFG['PageCount'] == 0)
      {
             $this->ListCFG['PageCount'] = (($CountAll-$this->ListCFG['PageCount'])/$this->ListCFG['ItemPerPage'])-1;
	        }
		   else { $this->ListCFG['PageCount'] = (($CountAll-$this->ListCFG['PageCount'])/$this->ListCFG['ItemPerPage']);  };


   if ($this->ListCFG['Page'] > $this->ListCFG['PageCount'] or $this->ListCFG['Page'] == 0 or $this->ListCFG['Page']  == '') $this->ListCFG['Page']  = 0 ;  

   $this->PageStart = ($this->ListCFG['Page'] -($this->ListCFG['Page']%$this->ListCFG['PageShow']));
   if ($this->PageStart > $this->ListCFG['Page']) $this->PageStart = ($this->PageStart-$this->ListCFG['PageShow'])  ;


   $this->PageEnd =  (($this->PageStart+$this->ListCFG['PageShow'])-1);
   if ($this->PageEnd > $this->ListCFG['PageCount']) $this->PageEnd = ($this->ListCFG['PageCount']);
 
   $prev = ($this->ListCFG['Page']-1);
   if ($prev <0 ) { $prev =0; }
   $this->Nav["previous"] = $this->ListCFG['Host'].$this->ListCFG['LinkAdd'].'&orderfield='.$this->OrderField.'&ordertype='.$this->OrderType.'&page='.$prev;

//   for ($i = $this->PageStart; $i <= $this->PageEnd; $i++ )
   for ($i = $this->PageStart; $i <= $this->PageEnd; $i++ )
   {
        $count = $i+1;
        $this->Nav[$count] = $this->ListCFG['Host'].$this->ListCFG['LinkAdd'].'&orderfield='.$this->OrderField.'&ordertype='.$this->OrderType.'&page='.$i;        
   }

    $next = $this->ListCFG['Page'];
    //    if ($this->ListCFG['PageCount']  >= ($this->ListCFG['Page']+1) ) { $next++; }
    if ($this->ListCFG['PageCount']  >= ($this->ListCFG['Page']+1) ) { $next++; }
    $this->Nav["next"] = $this->ListCFG['Host'].$this->ListCFG['LinkAdd'].'&orderfield='.$this->OrderField.'&ordertype='.$this->OrderType.'&page='.$next;               
    
  }      

   $this->Html['NavText'] = array($CountAll,(($this->ListCFG['Page']*$this->ListCFG['ItemPerPage'])+1),(($this->ListCFG['Page']*$this->ListCFG['ItemPerPage'])+$CountTaken)); 
   return array($this->Nav,$this->Html['NavText']);

}

/**
*	Sets configuration for sql query
**/


function SqlCfg() {

   $SqlSearch = '';
   $SqlSearch = $this->SearchField_Cfg();
   
   if ($this->SqlAfterWhere != '' and $SqlSearch != '')  {

       $this->SqlPredicate = $this->SqlMiddle." where ".$SqlSearch." and ".$this->SqlAfterWhere."    ";
   }
   else if ( $SqlSearch != '' and  $this->SqlAfterWhere == '') {     $this->SqlPredicate = $this->SqlMiddle." where ".$SqlSearch."  "; }
   else if ( $this->SqlAfterWhere != '') { $this->SqlPredicate = $this->SqlMiddle." where ".$this->SqlAfterWhere."  "; }
   else  $this->SqlPredicate = $this->SqlMiddle." ".$SqlSearch."  ";
   
   //$count = join(',',$this->SqlFields);
   //if ($count =='') $count = ' count (*) as count ';
  $count = ' COUNT(*) AS count ';
   $this->SqlCountExe = "select ".$count." from ".$this->SqlPredicate." ";
   $this->SqlExe = "select ".join(',',$this->SqlFields)."  from  ".$this->SqlPredicate." ".$this->SqlOrder." LIMIT ".((int)$this->ListCFG['Page']*(int)$this->ListCFG['ItemPerPage']).",".(int)$this->ListCFG['ItemPerPage']." ";

}

/**
*	Lists elements from database
*
*	@return array Elements
**/

function ListDB() {

 global $ErrHandle;
 if($ErrHandle->Debug)
 {
 }

 if (!$this->Result = $this->DBHandle->DBQuery($this->SqlCountExe))
 {
    if($ErrHandle->Debug)
    {
    var_dump($this->DBHandle->DBError());
    print __LINE__.' '.__FILE__;
    }
   $ErrHandle->Error(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__."): ".__CLASS__."->".__FUNCTION__."()", $this->DBHandle->DBError());  
   return FALSE;		
 } 

    $tmp= $this->DBHandle->DBFetchArray($this->Result);  
 $this->CountAll =  (int)$tmp['count'];
    
// var_dump($this->CountAll);
// exit;

 if (!$this->Result = $this->DBHandle->DBQuery($this->SqlExe)) 
 {
    if($ErrHandle->Debug)
    {
    var_dump($this->DBHandle->DBError());
    print __LINE__.' '.__FILE__;
    }

   $ErrHandle->Error(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__."): ".__CLASS__."->".__FUNCTION__."()", $this->DBHandle->DBError());  
   return FALSE;		
 }  

 $this->CountTaken = $this->DBHandle->DBNumRows($this->Result);
 
 if ($this->CountTaken  > 0 ) {  return $this->Result; } 
 else {  return FALSE; }

}       

}

?>