<?php
/**
*Class for use as abstract layer in connections with databases.
*
*/
class DataBase  { 

/**
* DB object
* 
*/       

/**  @var mixed $DB_handle keeps db  handle  */
var $DB_handle;

/**  @var string $DB_last_query keeps last query to db   */
var $DB_last_query ='';

/**  @var string $DB_name name of db */
var $DB_name  ='';

/** @var string $DB_user name of db user  */
var $DB_user  ='';

/** @var string $DB_password name of db password */
var $DB_password  ='';

/** @var string $DB_host name of db host */
var $DB_host ='';

/** @var string $DB_table_prefixname of db table prefix  */
var $DB_table_prefix =''; 

var $DB_port='';

/** @var int $DB_CONFIG 0|1 if cfg data from a file was set to DB vars  */
var $DB_CONFIG = 0;

var $ErrHandle;
var $query_history= array();

	function Q($string)
	{
	return mysql_escape_string($string);
	}

        /**
        *      Read DB config from file and set config to class
        *      File must be written in ini file format with section of DB  

        *      @param string $File full path to file with db config values 
        *      @return boolean 0|1 
        */

	function DBFileConfig($File) {
  
             if (!file_exists($File)) return FALSE; 
    
             $config = array(); 
             $config = parse_ini_file($File, true);

      		 if (count($config['DB']) == 0 ) return FALSE;
             if (!$this->DBConfig(trim($config['DB']['dbname']),trim($config['DB']['dbhost']),trim($config['DB']['dbuser']),trim($config['DB']['dbpass']),trim($config['DB']['dbtableprefix']),trim($config['DB']['dbport']))) return FALSE;

             return TRUE;

          } 

        /**
        *      Constructor  of class 
        *      It check if file config is successful if is not use the other values to db config 
 
        *      @param string $File path to db file config (optional) 
        *      @param string $DBName Name of db (optional)
        *      @param string $DBHost host of db (optional)
        *      @param string $DBUser user to access db (optional)
        *      @param string $DBPassword password to access db (optional)
        *      @param string $DBTablePrefix table prefix (optional)
        *      @return boolean 0|1 

        */ 

        function DataBase($File ='',$DBName='',$DBHost='',$DBUser='',$DBPassword='',$DBTablePrefix='',$DBPort='') {

            global $ErrHandle;
	    $this->ErrHandle = &$ErrHandle;
            if (!$this->DBFileConfig($File)) 
            { 
                $this->DBConfig($DBName,$DBHost,$DBUser,$DBPassword,$DBTablePrefix,$DBPort);                 

            }

        } 


        /** 
        *      Sets db config values 
        *      @param string $DBName Name of db 
        *      @param string $DBHost host of db 
        *      @param string $DBUser user to access db 
        *      @param string $DBPassword password to access db 
        *      @param string $DBTablePrefix table prefix (optional)
        *      @return boolean    
        */ 

	function DBConfig($DBName='',$DBHost='',$DBUser='',$DBPassword='',$DBTablePrefix='',$DBPort='')
	{
  
              global $ErrHandle;

              if ($this->SetDBHost($DBHost) &&  $this->SetDBName($DBName) && $this->SetDBUser($DBUser) && $this->SetDBPassword($DBPassword)  ) 
              { 
                 $this->SetDBPrefix($DBTablePrefix);
		 $this->DB_port = $DBPort;
                 $this->DB_CONFIG = 1;				 
                 return TRUE;                 
              }
              else return FALSE;                         
        }


        /** 
        *     Check  DB_CONFIG flag 
        *     @return boolean    
        */ 

	function CheckConfig()
	{
        if ($this->DB_CONFIG  != 1) return FALSE;
	   	return TRUE;
    }


        /** 
        *     Clear db config and set DB_CONFIG class to 0 
        *    @return boolean    

        */ 

	function DBConfigClear()
	{
                if ($this->DB_CONFIG  != 1) return FALSE;

                $this->DB_name = '';
                $this->DB_table_prefix = '';
                $this->DB_user = '';
                $this->DB_password = '';
                $this->DB_host = '';
                $this->DB_CONFIG =0;
	   	return TRUE;
        }


        /** 
        *    Set DB Host to class config vars - ONLY if $this->DB_CONFIG == 0 
        *    @param string $Host db host
        *    @return boolean    

        */ 

	function SetDBHost($Host ='')
	{
                if ($this->DB_CONFIG  != 0 || $Host == '') return FALSE;
                $this->DB_host = $Host;
			   	return TRUE;
    }

        /** 

        *    Set DB name to class config vars - ONLY if $this->DB_CONFIG == 0 
        *    @param string $Name db name
        *    @return boolean    

        */ 


	function SetDBName($Name='')
	{
                if ($this->DB_CONFIG  != 0 || $Name == '') return FALSE;
                $this->DB_name = $Name;
			   	return TRUE;
        }

        /** 
        *    Set table prefix name to class config vars - ONLY if $this->DB_CONFIG == 0 
        *    @param string $Name table prefix 
        *    @return boolean    

        */ 

	function SetDBPrefix($Name='')
	{
                if ($this->DB_CONFIG  != 0 || $Name == '') return FALSE;
                $this->DB_table_prefix = $Name;
	   	return TRUE;
        }

        /** 
        *    Set DB User to class config vars - ONLY if $this->DB_CONFIG == 0 
        *    @param string $User db user name
        *    @return boolean    

        */ 

	function SetDBUser($User = '')
	{
                if ($this->DB_CONFIG  != 0 || $User == '') return FALSE;
                $this->DB_user = $User;
			   	return TRUE;
    }

        /** 
        *    Set DB Password to class config vars - ONLY if $this->DB_CONFIG == 0 
        *    @param string $Password  password to access db 
        *    @return boolean 0|1   
	*
        */ 


	function SetDBPassword($Password ='')
	{
                if ($this->DB_CONFIG  != 0 || $Password == '') return FALSE;
                $this->DB_password = $Password;
	  		   	return TRUE;
        }

        /** 
        *    Set Table prefix to class config vars - ONLY if $this->DB_CONFIG == 0 
	*    
        *    @param string $Prefix name of db prefix - can be ''  
        *    @return boolean 0|1   
        */ 

	function SetDBTablePrefix($Prefix = '')
	{
                if ($this->DB_CONFIG  != 0) return FALSE;
                $this->DB_table_prefix = $Prefix;
        	   	return TRUE;

        }

	/**
	*    Send query to DB
	*    
	*    @param string $Query Query to send
	*    @return mixed handle to results
	*    
	*/
	function DBQuery($Query = '') {
	    //var_dump($this->ErrHandle->Debug);
            if ($Query == '' || $this->DB_CONFIG  != 1) return FALSE;              

   	    $this->DB_last_query = $Query;
	    array_push($this->query_history, $Query);
	    $result = mysqli_query ($this->DB_handle, $Query);
	    if($this->DBError() && $this->ErrHandle->Debug)
	    {
		var_dump($this->DB_last_query);
		var_dump($this->DBError());
		exit;
	    }
	    else
	    {
                return $result;
	    }
	          
        } 

	/** 
	*    Check if connected to DB
	*    @return boolean 0|1
	*/
        function DBConnect() {     

            global $ErrHandle;

            if ($this->DB_CONFIG  != 1) return FALSE;

		if($this->DB_port)
		{
	        	$this->DB_handle = @mysql_connect ($this->DB_host.':'.$this->DB_port, $this->DB_user, $this->DB_password);                                   
		}
		else
		{
                   $this->DB_handle = @mysqli_connect ($this->DB_host, $this->DB_user, $this->DB_password);    
                }
	        if ($this->DBError()) { return FALSE; }

                if (@!mysqli_select_db($this->DB_handle, $this->DB_name))
	        {
	        return false;
        	}
        	if(!$this->DBQuery("SET NAMES latin1"))
		{
		    if($ErrHandle->Debug)
		    {
		    print __LINE__.' '.__FILE__;
		    exit;
		    }
		    return false;
		}
		
            return true;

        }


        /** 
        *   Return the db handle             
        *   @return mixed  handle to db 
	*
        */ 

	function GetDBHandle()
	{
                if ($this->DB_CONFIG  != 1 || $Result == '') return FALSE;                
	   	return $this->DB_handle;
        }

	/**
	*    Returns number of records 
	*    @param mixed $Result  handle to results
	*    @return integer
	*/
	function DBNumRows($Result)
	{
                if ($this->DB_CONFIG  != 1 || $Result == '') return FALSE;                
	   	return @mysql_num_rows($Result);
        }
	
	/**
	*    Get a result row as an enumerated array
	*    @param mix handle to results
	*    @return array a numerical array that corresponds to the fetched row and moves the internal data pointer ahead. 
	*         
	*/
	function DBFetchRow($Result)
	{
                if ($this->DB_CONFIG  != 1 || $Result == '') return FALSE;
		return @mysqli_fetch_row($Result);
	}

	/**
	*    Stops executing of script and displays an execute DBError
	*    
	*/
        function DBDie()
        { 
           global $ErrHandle;  
           $ErrHandle->Fatal(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.")","DB Error : ".$DB->DBError()." # last query : ".$this->DB_last_query);       
           $ErrHandle->SaveErrors();
           die("MYSQL-error: ".$this->DBError());
           exit;
        } 


	/**
	*    Free memory reserved for DB results
	*    @param mixed DB results
	*    @return boolean 0|1
	*/
        function DBFreeResult($Result) 
        {  
           if ($this->DB_CONFIG  != 1 || $Result == '') return FALSE;
           return @mysql_free_result($Result);
        }
         
         /** 
	*    Fetch a result row as an associative array, a numeric array, or both
	*    @param mixed handle to results
	*    @return array an array that corresponds to the fetched row and moves the internal data pointer ahead.
	*/    
        function DBFetchArray($Result) 
        { 
           if ($this->DB_CONFIG  != 1 || $Result == '') return FALSE;
           return @mysql_fetch_assoc($Result);
        } 

	/**
	*    Get the ID generated from the previous INSERT operation
	*    @return integer ID generated for an AUTO_INCREMENT column by the previous INSERT query.
	*/
	function DBInsertID()				
        { 
           if ($this->DB_CONFIG  != 1 ) return FALSE;
   	   return @mysql_insert_id();
        } 
	
	/**
	*    Returns number of records
	*    @param mixed $Result handle to results
	*    @return integer
	*/ 
	function DBCountRows($Result) 				
        { 
            if ($this->DB_CONFIG  != 1 || $Result == '') return FALSE;
   	    return @mysql_num_rows($Result);
        } 

	/**
	*    Close connection to DB
	*/
	function DBDisconnect() 
        { 
            if ($this->DB_CONFIG != 1 || $this->DB_handle == '') return;
 	    @mysql_cose($this->DB_handle);
        } 
	
	/**
	*    Get the name of the specified field in a result
	*    @param mixed $Result handle to results
	*    @param integer $Field field index
	*    @return string the name of the specified field index.
	*/
	function DBGetFieldName($Result,$Field) 
        { 
            if ($this->DB_CONFIG != 1) return;
  	    return @mysql_field_name($Result, $Field);
        } 
	/**
	*    Get column information from a result and return as an object
	*    @param mixed $Result  handle to results
	*    @return mixed object with an object containing field information.
	*    This function can be used to obtain information about fields in the provided query result.
	*    
	*/
	function DBFieldInfo($Result) 
        {         	       
                if ($this->DB_CONFIG  != 1) return ;
	        return @mysql_fetch_field($Result);
        } 


	/**
	*    Get column information for a table
	*    
	*/
	function DBTableFields($table_name) 
        {         	       
                if ($this->DB_CONFIG  != 1) return false;
               $Result = $this->DBQuery("SHOW COLUMNS FROM ".$this->Q($table_name)."");

               while ($row = $this->DBFetchArray($Result))
	       {
	       $list[] = $row;     
               }


	        return $list;
        } 




	/**
	*    Returns Table list, optionaly define prefix of tables
	*    @param string $Prefix prefix of tables
	*    @return array $Tables array of tables
	*/
	function DBTableList($Prefix) 
        { 
               global $ErrHandle;

               if ($this->DB_handle == '' || $this->DB_CONFIG != 1) { return; } 

               $Tables = array();
               $Result = $this->DBQuery("show tables");

               while ($name = $this->DBFetchRow($Result)) {     

                 if (preg_match("/^$this->DB_table_prefix$Prefix/",$name[0]) && !in_array($name[0],$Tables)  ) 
                 {                                       
                     array_push($Tables,$name[0]);                   
                 }
               }

	       return $Tables;
        } 

	/**
	*    Describe error event
	*    @return string $Comment description of error with query witch throw it
	*/
        function DBError() { 

       	    global $ErrHandle;
       	    if ($this->DB_handle) { $Comment = @mysqli_error($this->DB_handle); } 
       	    else { $Comment = @mysqli_error(); }
            if ($Comment =='') { return FALSE; }  
            $ErrHandle->Error(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.")","DB Error: ".$Comment);
            $ErrHandle->Debug($Comment." ".$this->DB_last_query);     
            return $Comment;  
        } 
	/**
	*    adden new argument TypeCols to define type of a field to add 
	*       if is a function then there shoud be string 'function'
	*    Set new values into existing record
	*    @param string $Table name of table
	*    @param array $Values an array of values where keys are same as in the table
	*    @return boolean 0|1
	*/
	
       function DBUpdateRecord($Table,$Values = array(),$TypeCols=array())   
       {

           global $ErrHandle;
           if ($Table == '' or count($Values) == 0 or !$this->DB_CONFIG ) { return 0 ; }

           $Sql = ' update  '.$Table.' ';
           $SqlField = '';
           $SqlSurfix = '';

           foreach ($Values as $field => $value)
           {
               if( preg_match('/^\_up/',$field) ) { continue; } 
               $value = preg_replace("/^'/",'',$value);
               $value =	preg_replace("/'$/",'',$value);
		if($TypeCols[$field] != 'function')  $value = "'".$this->Q($value)."'";

	       
               if ($SqlField == '') { $SqlField  = " set `".$field."` = ".$value." "; }  
               else { $SqlField  .= " , `".$field."` = ".$value." "; }  
           }

           if ($Values['_upf'] == '' ) { return 0; } 

               $Values['_upv'] = preg_replace("/^'/",'',$Values['_upv']);
               $Values['_upv'] = preg_replace("/'$/",'',$Values['_upv']);
	       $Values['_upv'] = "'".$this->Q($Values['_upv'])."'";

           $SqlSurfix  = " where ".$Values['_upf']." = ".$Values['_upv']." ";

           $this->DBQuery($Sql.$SqlField.$SqlSurfix); 
           if ($this->DBError()) { return 0;}
           return 1;  

       }

	/**
	*    Added new argument TypeCols=array() to define type of adding field
	*	if it is a function then shoud have $fieldname => 'function'
	*		, e.g. : array ( 'date' => 'function' ) (BAD USE : array('date' => 'now()')
	*    Adds new record into table
	*    @param string $Table name of table
	*    @param array $Values an array of values to put into record
	*/
       function DBAddRecord($Table,$Values = array(), $TypeCols=array()) 
       {

	   global $ErrHandle;
           //var_dump($Table);
	   //var_dump($Values);
           if ($Table == '' or count($Values) == 0 or !$this->DB_CONFIG ) { return 0 ; }

           $Sql = ' insert into '.$Table.'  ';
           $SqlField = '';

           foreach ($Values as $field => $value)
           {
               $value = preg_replace("/^'/",'',$value);
               $value =	preg_replace("/'$/",'',$value);
	       //if (!preg_match('/\(\)$/',$value)) $value = "'".mysql_escape_string($value)."'";	      
		if($TypeCols[$field] != 'function')  $value = "'".$this->Q($value)."'";

               if ($SqlField == '') { $SqlField  = " set `".$field."` = ".$value." "; }  
               else { $SqlField  .= " , `".$field."` = ".$value." "; }  
           }

           $this->DBQuery($Sql.$SqlField); 
	    if($id =  $this->DBInsertID() )
	    {
		return $id;
	    }
	    else
	    {
		return true;
	    }
       }

        /**
	 *  Make Archive table 
         *  i.e. 
         *     current_table  
         *     current_table_10_2002

         *  It creates table current_table_10_2002  ( clone of current_table but with no data ) 
         *  It separates data from current_table, with criteria given as param , then they are deleted 
         *  from main table 

         *  @param string $Table  name of table  to be duuplicated 
         *  @param string $SQLCriteria  sql criteria to copy data from source table to 
         *  @param string $SQLIndexField index field to delete selected data 
         *  @param string $NewTable name of new table 

         *  @return boolean 0|1

        */

       function DBMakeArchive($Table,$SQLCriteria,$SQLIndexField,$NewTable) {  

           global $ErrHandle;
           if ($NewTable == '' || $Table == '' || $SQLCriteria =='' || $this->DB_handle == '') return FALSE; 

           if (!$this->DBTableExists($NewTable)) 
           {
	           if (!$this->DBDuplicateTable($Table,$NewTable))
        	   {
           	        $ErrHandle->Debug("Duplication of ".$Table." (new name ".$NewTable." ) fails. DB->DBMakeArchive($Table,$SQLCriteria,$SQLIndexField,$NewTable)");     
        	        return FALSE;
	           } 		 
                 
                   if (!$this->DBCopyData($Table,$SelectSQL,$NewTable))
                   {
                       $ErrHandle->Debug("Copy data fails. DB->DBCopyData($Table,$SelectSQL,$NewTable) DB->DBMakeArchive($Table,$SQLCriteria,$SQLIndexField,$NewTable)");     
                       return FALSE;
                   }

                    $SQLSelect = " select ".$SQLIndexField." from ".$NewTable; 
                    $Result = $this->DBQuery($SQLSelect);
                    if ($this->DBError()) FALSE;

                    while ($row = $this->DBFetchArray($Result))
        	    {
                        $this->DBQuery("delete from ".$Table." where ".$SQLIndexField." = '".$row[0]."' ");                   	            
                    } 

                    if ($this->DBError()) FALSE;
                    return TRUE; 

           }  
           return FALSE;                    

       }


        /** Dublicate db table  
        *   Data in table are not included in duplication process, just  create identical table with  new name 
	*
        *   WORNING !!!  
        *   This method uses sql used propably only in mysql

        *   @param string $Table  name of table  to be duuplicated 
        *   @param string $NewTable name of new table 
        *   @return boolean 0|1

        */

       function DBDuplicateTable($Table,$NewTable)   
       {

           global $ErrHandle;
           if ($Table == '' || $NewTable == '') return FALSE;

           $Definition = $this->DBCreateSQL($Table);
           if ($Definition == '') return FALSE;

           preg_replace('/ TABLE \`$Table\` /','TABLE \`$NewTable\` ',$Definition);

           $this->DBQuery($Definition); 
           if ($this->DBError()) return FALSE;
           return TRUE;  

       }

        /** Check if specified table exist in db 
	*
        *   WORNING !!!  
        *   This method uses sql used propably only in mysql
	*
        *   @param string $Table  name of table 
        *   @return boolean 0|1
	*
        */

       function DBTableExists($Table) {  

            global $ErrHandle;
            if ($Table == '' ) return FALSE;
            $Result = $this->DBQuery("show table status from  ".$this->DB_name." LIKE '".$this->DB_table_prefix.$this->Q($Table)."'");
            if ($this->DBError()) return FALSE;            
            $value = $this->DBNumRows($Result);
            if($value > 0) return FALSE;
            else return TRUE;
        }



        /** Returns  all table starting with specified suffix 
	*
        *   WORNING !!!  
        *   This method uses sql used propably only in mysql
	*
        *   @param string $Suffix  suffix name of tables
        *   @return array Tables with specified suffix
	*
        */

       function DBGetTables($Suffix) {  

            global $ErrHandle;
            if ($Suffix == '' ) return FALSE;
            $Tables = array();
            $Result = $this->DBQuery("show table status from  ".$this->DB_name." LIKE '".$this->DB_table_prefix.$this->Q($Suffix)."%'");
            if ($this->DBError()) return FALSE;            
            while ($row = $this->DBFetchArray($Result))
       	    {
                 $Tables[count($Tables)] = $row[0];                   
            }        

            return $Tables;
        }



        /** Return  sql create table definition for given table name   
	*
        *   WORNING !!!  
        *   This method uses sql used propably only in mysql
	*
        *   @param string $Table  name of table 
        *   @return string return the sql or nothing 
	*    
        */

       function DBCreateSQL($Table) {  

            global $ErrHandle;           
            if ($Table == '' ) return;

            $Result = $this->DBQuery("show create table ".$this->DB_name.".".$this->DB_table_prefix.$Table);
            if ($this->DBError()) return FALSE;
            $value = $this->DBFetchArray($Result);
            return $value[1];            
        }



        /** Copy data from one table to another
         *  Tables need to be identical , or destination table have all fields contained by source Table
         *
	 *
         *  @param string $SourceTable Name of course table 
         *  @param string $SQLCriteria sql string  ( criteria of data to copy )
         *  @param string $DestinationTable Name of destination table 
         *  @return boolean 0|1
	 *
        */


       function DBCopyData($SourceTable,$SQLCriteria,$DestinationTable) {  


            global $ErrHandle;
                         
            if ($SourceTable == '' or  $DestinationTable == '' ) return FALSE;        

            $SQLInsert = ' insert into '.$this->DB_table_prefix.$DestinationTable.' set ';

            $Result = $this->DBQuery($SQLCriteria);
            if ($this->DBError()) return FALSE;
                    while ($row = $this->DBFieldInfo($Result))
        	    {
	                $SQLInsert .= $row->name.' = ';
	                if ($row->numeric) { $SQLInsert .= ' %d '; } 
	                else {  $SQLInsert .= " '%s' "; } 
	            }


            while ($row = $this->DBFetchArray($Result))
       	    {
                 $this->DBQuery(sprintf($SQLInsert,array_keys($row)));                   
            }        

            if ($this->DBError()) return FALSE;        
            return TRUE;  

        }
        

     /**
     *    Deconstructor of class 
     */ 


     function DBClose() { 

 
          $this->DBDisconnect();
          $this->DBConfigClear();
                                                                             
     }                 



}



?>
