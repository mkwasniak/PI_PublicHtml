<?php 
    $CFG = array(
        'ClassPath'		=> '/var/www/config/',
        'TemplatePath'	=> '/var/www/html/genes/templates/',
        'PageURL'       => 'http://10.0.0.10/',
        '' 			    => ''
    );

    require_once('class/tree.php');
    require_once($CFG['ClassPath'].'Template.php');

	if ($_POST != NULL) {
//		var_dump($_POST); 
		$_GET['message'] = 'Dodano osobe';
		$_GET['ok_code'] = 0;
	}

    $template = new Template($CFG['TemplatePath']);
    $template->Start('add_person.html');
  
    #region ok_code and message return
    // if(isset($_POST['submit'])) {    $Crontab->submit_handle($_POST['submit']);   }

    $template->SetTagValue("message", $_GET['message']);
    if(isset($_GET['ok_code']))
    {
        $template->SetTagValue("alert_hidden", '');
        if($_GET['ok_code'] > 0)
        {
            $template->SetTagValue("alert_color", 'danger');
        }
        else
        {
            $template->SetTagValue("alert_color", 'success');
        }
    }
    else
    {
        $template->SetTagValue("alert_hidden", 'hidden');
    }
    #endregion

    $Tree = new Tree();

    $template->SetTagValue("select_fatherID",$Tree->html_select_get_persons_all());
    $template->SetTagValue("select_motherID",$Tree->html_select_get_persons_all());
    $template->SetTagValue("select_partnerID",$Tree->html_select_get_persons_all());

    $template->ReplaceTags();
    $template->ReplaceAllTags();
    echo $template->Show();
?>

