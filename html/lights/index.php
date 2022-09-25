<?php 
    $CFG = array(
        'ClassPath'		=> '/var/www/config/',
        'TemplatePath'	=> '/var/www/html/lights/templates/',
        'PageURL'       => 'http://raspberrypi/',
        '' 			    => ''
    );

    require_once('class/crontab.php');
    require_once($CFG['ClassPath'].'Template.php');
    require_once($CFG['ClassPath'].'SelectHtml.php');
    require_once($CFG['ClassPath'].'DataBase.php');

    $DB = new DataBase('','lights','localhost','lights','Lights123','','');
    if(!$DB->DBConnect())
    {
        die("Can't connect db. DB Error : ".$DB->DBError());
    }
 
 
    $template = new Template($CFG['TemplatePath']);
    $template->Start('index.html');
  
    $select=new SelectHtml();

    $Crontab = new Crontab();

    #region ok_code and message
    if(isset($_POST['submit'])) {   

        $Return = $Crontab->submit_handle($_POST['submit']);   

        $ok_code = $Return['ok_code'];
        if($ok_code !== NULL)
        {
            $template->SetTagValue("message", $Return['message']);
            $template->SetTagValue("alert_hidden", '');    
            $template->SetTagValue("alert_hidden", '1');

            switch ($ok_code) 
            {
                case '0':
                    $template->SetTagValue("alert_color", 'success');
                    break;
                case '4':
                    $template->SetTagValue("alert_color",  'warning');
                    break;
                case '8':
                    $template->SetTagValue("alert_color",  'danger');
                    break; 
                default:
                    # code...
                    break;
            }
        }
    }
    else if(isset($_GET['ok_code']))
    {
        $template->SetTagValue("message", $_GET['message']);
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

    $template->SetTagValue("select_lights", 
        $select->Select("DB","select_light_name",'class="form-control" id="schedule-lightname"',"","", array( "[Choose]" => ""),
        "SELECT light_name, light_name FROM topics ", array($select_lights),""));

    $template->SetTagValue("crontab_html",$Crontab->crontab_in_html());
    $template->SetTagValue("gen_crontab_html",$Crontab->generated_crontab_in_html());
    $template->SetTagValue("schedule_rows",$Crontab->schedule_table_in_html());

    $template->ReplaceTags();
    $template->ReplaceAllTags();
    echo $template->Show();

?>

