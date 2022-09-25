<?php


class SelectHtml {

 var $HTML;

        /**
        *       @param Type = KEYVALUE/VALUEKEY/ARRAY/DB
                            KEYVALUE => @param SelectItems = array_flip(@param SelectItems),
                            VALUEKEY => no changes
                            ARRAY => foreach @param Select Items...     SelectItems[value]= value;
                            DB => @param SelectItems is SQL string
        *       @name -- <select (...)  name="costam" > (...)
        *       @ HTMLadd , e.g. $HTMLAdd = " dupa" <select (...) dupa />
        *       @OptionPrefix e.g. OptionPrefix='' <option >Costam</option>
                                    OptionPrefix='dupa' <option >dupaCostam</option>
        *       @OptionSurfix e.g. OptionSurfix='' <option >Costam</option>
                                    OptionSurfix='dupa' <option >Costamdupa</option>
                @Preselect      e.g. array( "" => "[Choose]" )
                @SelectItems = e.g. array( 0 => 'no', 1= > 'yes') or if Type param == "DB" here is sql "SELECT dupa,dupa2 from.."
                @SelectValues = e.g. usually : if @param name == "dupazmienna" then here : array($dupazmienna)
        *       @CountFrom  
                @Move
        *       
        *
        */
 function Select($Type,$Name,$HtmlAdd,$OptionPrefix,$OptionSurfix,$PreSelect = array(),$SelectItems = array(),$SelectValues = array(),$CountFrom = 0,$Move = 0,$sort=false) {
                                
  global $DB;
  global $ErrHandle;
                        

  $ConvPreSelect = array();
  $ConvSelectItems = array();
                
 $this->HTML = '';
                            
 switch ($Type) {
                                
    case  'KEYVALUE':
                
                   
                return $this->SFHtml($Name,$HtmlAdd,$OptionPrefix,$OptionSurfix,$PreSelect,$SelectItems,$SelectValues);
                break;

   case  'VALUEKEY':

                
                if(is_array($PreSelect)) {  $ConvPreSelect = array_flip($PreSelect); }
                if(is_array($SelectItems))
                {
                
                    // Changes 23.10.2005 Mariusz Kukawski :
                    if(in_array(NULL,$SelectItems, TRUE) )
                    {
                
                        if($ErrHandle->Debug)
                        {
                            echo ' Framework debug, bad data in file '.__FILE__.' at line '.__LINE__."<br>";
							var_export($SelectItems);
                            $ErrHandle->Debug(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.") ".$LNG['CNT_OFFER_EDIT_FAILED']);
                            exit;
                        }   
                        return FALSE;
                        
                    }   
                    // end of Changes

                $ConvSelectItems = array_flip($SelectItems);
                if($sort)
                {
                asort($ConvSelectItems);
                }
//              var_dump($ConvSelectItems);
                }
                return $this->SFHtml($Name,$HtmlAdd,$OptionPrefix,$OptionSurfix,$ConvPreSelect,$ConvSelectItems,$SelectValues);

     break;

   case  'KEYCOUNT':



                if(is_array($PreSelect)) {

                                foreach ($PreSelect as $name => $value)
                                {
                                    $ConvPreSelect[$name] = $CountFrom;
                                    $CountFrom =+ $Move;
                                }   
                }
  
                if(is_array($SelectItems)) {
                            foreach ($SelectItems as $name => $value )
                            {
                                $ConvSelectItems[$name] = $CountFrom;
                                $CountFrom =+ $Move;
                            }
                }           
                
                return $this->SFHtml($Name,$HtmlAdd,$OptionPrefix,$OptionSurfix,$ConvPreSelect,$ConvSelectItems,$SelectValues);
                break;

   case  'DB':

                if ($SelectItems == '') { return '0'; }
	
                $Result = $DB->DBQuery($SelectItems);
  
				if ($DB->DBError()) { return '0' ;}
                $Values = array();
                while ($row = $DB->DBFetchRow($Result)) { $Values[$row[0]] = $row[1]; }
                    
                return $this->SFHtml($Name,$HtmlAdd,$OptionPrefix,$OptionSurfix,$PreSelect,$Values,$SelectValues);
                break;
                        
    case  'ARRAY':
                     
		$ConvPreSelect = array();
		$ConvSelectItems = array();
		if(is_array($PreSelect)) {
			foreach ($PreSelect as $value )
			{
				$ConvPreSelect[$value] = $value;
			}   
		}
		if(is_array($SelectItems)) {
			foreach ($SelectItems as $value )
			{
			$ConvSelectItems[$value] = $value;
			}
		}
		return $this->SFHtml($Name,$HtmlAdd,$OptionPrefix,$OptionSurfix,$ConvPreSelect,$ConvSelectItems,$SelectValues);
	   break;
		
default:
 return;
 break;
}

}

function SFHtml($Name,$HtmlAdd,$OptionPrefix,$OptionSurfix,$PreSelect = array(),$SelectItems = array(),$SelectValues = array()) {

	 // if (count($PreSelect) ==0) { return FALSE; }
	 if (!is_array($SelectValues)) $SelectValues =array();
							
							
	  $this->HTML = '<SELECT name="'.$Name.'" '.$HtmlAdd.' >';
		
  if (is_array($PreSelect)) {
		foreach ($PreSelect as $name => $value )
		{
		   $this->HTML .= '<OPTION VALUE="'.$OptionPrefix.$value.$OptionSurfix.'"  ';
		   if (in_array(htmlentities($OptionPrefix.$value.$OptionSurfix),$SelectValues))
		   {
				 $this->HTML .= ' selected ';
		   }        
		   $this->HTML .= '> '.$name.' </OPTION>';
		}
}

  if (is_array($SelectItems)) {

		
		foreach ($SelectItems as $name => $value )
		{
		   $this->HTML .= '<OPTION VALUE="'.$OptionPrefix.$value.$OptionSurfix.'"  ';
		   if (in_array(htmlentities($OptionPrefix.$value.$OptionSurfix),$SelectValues))
		   {
				 $this->HTML .= ' selected ';
		   }
		   $this->HTML .= '> '.$name.' </OPTION>';
		}       
  }

  $this->HTML .= '</SELECT>';
  return $this->HTML;
}


function Start() {

  return $this->HTML;
}



function Clear() {

$this->HTML = '';
}
			  
}
		
	 
?>