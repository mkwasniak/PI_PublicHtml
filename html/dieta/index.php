<?php 
    $CFG = array(
        'ClassPath'		=> '/var/www/config/',
        'TemplatePath'	=> '/var/www/html/dieta/templates/',
        'PageURL'       => 'http://10.0.0.10/',
        '' 			    => ''
    );

    require_once('dieta.php');
    $Dieta = new Dieta($_GET['receipt']);

    require_once($CFG['ClassPath'].'Template.php');
    

    $template = new Template($CFG['TemplatePath']);
    $template->Start('index.html');
  
    #region ok_code and message return
    $alert_hidden = 'hidden';
    if(isset($_POST['submit'])) 
    {    
        $Return = $Dieta->submit_handle($_POST['submit']);   
        // extract($Return);
    }
    if(isset($_GET['action']))  
    {   
        $Return = $Dieta->submit_action($_GET);              
    }

    $ok_code = $Return['ok_code'];
    if($ok_code !== NULL)
    {
        $message = $Return['message'];
        $alert_hidden = '';
        switch ($ok_code) 
        {
            case '0':
                $alert_color = 'success';
                break;
            case '4':
                $alert_color = 'warning';
                break;
            case '8':
                $alert_color = 'danger';
                break;
                        
            default:
                # code...
                break;
        }
    }

    #endregion

    $Dieta->get_config();
    switch ($Dieta->phase) {
        case '1':
            $panel_phase_1 = 'in';
            break;
        case '2':
            $panel_phase_2 = 'in';
            break;
        case '3':
            $panel_phase_3 = 'in';
            break;
        default:
            $panel_phase_4 = 'in';
            break;
    }

    //phase I
    $template->SetTagValue('pdf_text_after_process_v2',     $Dieta->generate_sentances_in_html(''));
    $template->SetTagValue('table_phrases_ignored_v2',      $Dieta->generate_sentances_in_html('I'));
    $template->SetTagValue('table_phrases_ingredients_v2',  $Dieta->generate_sentances_in_html('C'));

    //Phase II
    $template->SetTagValue("pdf_text_after_process",        $Dieta->generate_phrases_text_in_html());
    $template->SetTagValue("table_phrases_ignored",         $Dieta->generate_phrases_table_in_html('I'));
    $template->SetTagValue("table_phrases_ingredients",     $Dieta->generate_phrases_table_in_html('S'));
    
    //phase III
    $template->SetTagValue("ingredients_list_in_html",      $Dieta->generate_ingredients_table_in_html());

    //phase 0



    $template->ReplaceTags();
    $template->ReplaceAllTags();
    echo $template->Show();
?>

