<?php
  if ($CFG_Direct != 1) exit;

  require_once($CFG['IncludePath']."User.php");
  require_once($CFG['IncludePath']."SelectHtml.php");
  require_once($CFG['IncludePath']."Whishlist.php");

    $ErrHandle->Debug(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.") Create ParseData object ");
    $Whishlist = new Whishlist(); 

  function Update () 
  {
	global $DB, $ErrHandle, $Whishlist, $LNG, $r, $ra, $_UD, $Sys;
        global $CFG_DefaultAccessRights, $comment, $alert;
	
	if($r == '') return FALSE; 

        if (!$Whishlist->Add($_UD->SessionData('user'), $r['description'], $r['link']))
        {
    	    $comment = "Coś poszło nie tak, spróbuj ponownie albo zadzwoń do Maćka :)";
	    $alert = "danger"; //or success 
	    return;
        }	 
        $comment = "Dodano prezent do Twojej listy";
	$alert = 'success';
	
	header('LOCATION: '.$Sys->Link('User','Whishlist')."&previous=".urlencode($previous)."&alert=success&comment=".$comment);
	exit();
	
  }

if ($r['description'])
{

   Update();

}

    $ErrHandle->Debug(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.") Create template object ");  
    $template = new Template($CFG['CurrentTemplatePath']); 
    $template->Start('whishlist_add.html','0');        

 
  
    $ErrHandle->Debug(substr(__FILE__,strlen(getenv("DOCUMENT_ROOT")))."(".__LINE__.") Create SelectHtml object ");
    $status = new SelectHtml;                   
 
 
    // here I replace tag 'user_type'  placed in adminuser with select field 
    $template->SetTagValue("r[type]",$status->Select("VALUEKEY","r[type]","","","",'',$LNG['LIST_USER_TYPE'],array($r[type]),"",""));

    $r = $_UD->Get($_UD->SessionData('user'));

    $template->ReplaceTags();     
    $template->ReplaceAllTags();   
    print $template->Show(); 

?>