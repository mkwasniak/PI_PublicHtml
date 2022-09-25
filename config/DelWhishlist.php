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

        if (!$Whishlist->Delete($_UD->SessionData('user'), $r['whishlist_id']))
        {
    	    $comment = "Coś poszło nie tak, spróbuj ponownie albo zadzwoń do Maćka :)";
	    $alert = "danger"; //or success 
	    header('LOCATION: '.$Sys->Link('User','Whishlist')."&alert=".$alert."&comment=".$comment);
	    exit();
        }	 
        $comment = "Usunięto prezent do Twojej listy";
	$alert = 'success';
	
	header('LOCATION: '.$Sys->Link('User','Whishlist')."&alert=".$alert."&comment=".$comment);
	exit();
	
  }
if($r['whishlist_id'])
{
   Update();

}
exit;
    header('LOCATION: '.$Sys->Link('User','Whishlist')."&previous=".urlencode($previous)."&alert=danger&comment=Wystąpił nieznany błąd");
    exit();

?>