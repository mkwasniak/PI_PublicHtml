<?php

    if ($CFG_Direct != 1) exit;

    require_once($CFG['IncludePath']."SelectHtml.php");
    require_once($CFG['IncludePath']."ParseData.php");
    require_once($CFG['IncludePath']."ListMaker.php");
    require_once($CFG['IncludePath']."Whishlist.php");
    require_once($CFG['IncludePath']."User.php");

    $User = new User();
    
     
    $ErrHandle->Debug(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.") Create template object ");    
    $template = new Template($CFG['CurrentTemplatePath']); 
    $template->Start('whishlist.html','0');        

    $ErrHandle->Debug(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.") Create SelectHtml object ");
    $status = new SelectHtml;

    $ErrHandle->Debug(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.") Create Notes object ");
    $Whishlist = new Whishlist;

    //get UserID
    $UserID = $_UD->SessionData('user');
    $r = $User->Get($_UD->SessionData('user'));

    //set update that u dont neeed to update a list
    if($r['draw_gift_finished'] == 'X')// || $r['user_login'] == 'Kinga')
	$no_need = $template->ReadTemplate($CFG['CurrentTemplatePath'].'whishlist_no_need_to_update.html');

    //Params : rows per page,page number from 0,header tempalate read , base link
    $per_page='';
    if ($per_page=='') { $per_page = $CFG["ListPerPage"]; }
    if ($page=='') { $p = 0; }

    $ErrHandle->Debug(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.") Create ListMaker object ");
    if (!$ListObject = new ListMaker($DB))
    {
	$ErrHandle->Error(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.")","Can't create ListMaker object; using params : ".$DB);
    }

    if (!$ListObject->ListConf($per_page,$page,$template->ReadTemplate($CFG['CurrentTemplatePath'].'whishlist_frame.html'),$Sys->Link()."&s=1&pp=".$per_page))
    {
		$ErrHandle->Error(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.")"," Can't setup list config.");
    }
    if (!$ListObject->SqlConf(array(" w.* "),//fields
     			" whishlist AS w ",// from
			" w.user_login = '{$UserID}' " //where
    						) )
    {
		$ErrHandle->Error(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.")"," Can't setup list Sql config.");
		if($ErrHandle->Debug)
		{
	    	var_dump($DB->DBError());
	    	var_dump($DB->DB_last_query);
	    	print __LINE__.' in '.__FILE__;
	    	exit;
		}
    }
    $ListObject->SearchConf(array());

    $ListObject->SortConf(array("whishlist_id"), $orderfield, 'DESC' );

    $ListObject->CFG("NavType","NavLinks");
////// page show 10 ??
    $ListObject->CFG("PageShow","10");  //to check how it works
    $ErrHandle->Check();

    if(!list($Result,$Count) = $ListObject->execute())
    {
        if($ErrHandle->Debug)
        {
	    	print __LINE__.' '.__FILE__;
	    	exit;
		}
        $ErrHandle->Error(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.")"," List execution fails.");
    }
    else if ($ErrHandle->Check())
    {
		if($ErrHandle->Debug)
		{
	    	print __LINE__.' '.__FILE__;
	   		exit;
		}
        // Put error message
        $Error = $ErrHandle->GetLast();
        $template->SetTagValue("list_result",$Error['description']);
    }
    else 
    {
	if ($Count > 0)
       	{
           	// perform action on List Frame template
    		$ListFrame = $ListObject->ListHeader();
           	$ListRowTemplate =  $template->ReadTemplate($CFG['CurrentTemplatePath'].'whishlist_row.html');

       	 	$ListRowsHtml = '';
    		$ListObject->NavLinks();
           	$ListFrame = preg_replace("/###NavLinks###/",$ListObject->NavLinks(),$ListFrame);
           	// NavText  returns text values  in array (CountAll,Showfrom,ShowTo)

           	list($First,$Previous,$Next,$Last) = $ListObject->NavLinksSet();		    
           	$ListFrame = preg_replace("/###next_link###/",$Next,$ListFrame);
           	$ListFrame = preg_replace("/###first_link###/",$First,$ListFrame);
           	$ListFrame = preg_replace("/###previous_link###/",$Previous,$ListFrame);
          	$ListFrame = preg_replace("/###last_link###/",$Last,$ListFrame);
			
           	list($AllRows,$ShowFrom,$ShowTo) = $ListObject->NavText();
           	$ListFrame = preg_replace("/###AllRows###/",$AllRows,$ListFrame);
           	$ListFrame = preg_replace("/###ShowFrom###/",$ShowFrom,$ListFrame);
           	$ListFrame = preg_replace("/###ShowTo###/",$ShowTo,$ListFrame);

           	// Red tag names from template
           	$ErrHandle->Debug(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.") Read tags from row template  ");
           	$TagsFromRowTemplate = $template->GetTagsNames($ListRowTemplate,'0');
           	$RowCount = $ShowFrom;
           	$new_previous = $Sys->LinkToPrevious();
	
            while ($row = $DB->DBFetchArray($Result))
            {			    
		$ErrHandle->Debug(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.") Create SelectHtml object ");

                $CurrRowHtml  = $ListRowTemplate;
                $row['count'] = $RowCount;
				
		if($row['link'] != '') $row['go_to_link'] = '<i class="fas fa-shopping-cart"></i> Do sklepu';
		    
	    	// replace tags
                foreach ($TagsFromRowTemplate as $Name)
                {
            	    $CurrRowHtml = preg_replace("/###".$Name."###/",$row[$Name],$CurrRowHtml);
                }
	
		// put bufor to ListRows
                $ListRowsHtml .= $CurrRowHtml;

            	// count up
            	$RowCount++;
	    } // end of while row =...

	    $ErrHandle->Debug(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.") Replace ListRows List row html in list frame ");

	    //replace tag ListRows with all rows html in List Frame
	    $ListFrame = str_replace("###RowsList###",$ListRowsHtml,$ListFrame);
            
	    // Set all list html to main template
	    //var_dump($ListFrame);
	    
    	    $template->SetTagValue("list_result", $ListFrame);
	}
	else
	{
           	// Put message  message if no records founded
		$template->SetTagValue("comment", "Twoja lista jest jeszcze pusta");
           	$template->SetTagValue("alert",'warning');

           	$template->SetTagValue("list_result",'');
    	}
    }	

    $template->ReplaceTags();     
    $template->ReplaceAllTags();   
    print $template->Show(); 
    unset($per_page);
    session_write_close();
?>