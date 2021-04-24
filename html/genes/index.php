<?php 
    $CFG = array(
        'ClassPath'		=> '/var/www/config/',
        'TemplatePath'	=> '/var/www/html/genes/templates/',
        'PageURL'       => 'http://10.0.0.10/',
        '' 			    => ''
    );

    require_once($CFG['ClassPath'].'Template.php');
    

    $template = new Template($CFG['TemplatePath']);
    $template->Start('index.html');
  
    $Crontab = new Crontab();

    #region ok_code and message return
    if(isset($_POST['submit'])) {    $Crontab->submit_handle($_POST['submit']);   }

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
    $template->SetTagValue("crontab_html",$Crontab->crontab_in_html());
    $template->SetTagValue("gen_crontab_html",$Crontab->generated_crontab_in_html());
    $template->SetTagValue("schedule_rows",$Crontab->schedule_table_in_html());

    $template->ReplaceTags();
    $template->ReplaceAllTags();
    echo $template->Show();
?>

