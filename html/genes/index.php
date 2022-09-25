<?php 
    $CFG = array(
        'ClassPath'		=> '/var/www/config/',
        'TemplatePath'	=> '/var/www/html/genes/templates/',
        'PageURL'       => 'http://raspberrypi/',
        '' 			    => ''
    );

    require_once('class/tree.php');
    require_once('class/tree_core.php');
    require_once('class/tree_person.php');
    require_once($CFG['ClassPath'].'Template.php');
    

    $template = new Template($CFG['TemplatePath']);
    $template->Start('index.html');
  
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


    $template->SetTagValue("nodes_html", $Tree->nodes_in_html());
    $template->SetTagValue("family_tree",$Tree->html_generate_family_tree());
    

    $template->ReplaceTags();
    $template->ReplaceAllTags();
    echo $template->Show();
?>

