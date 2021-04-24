
<?php

class Dieta 
{
    #region Crontab Class attributes    
    private $DB;                        //connection handle to DB
    private $CFG;                       //global config
    private $counter = [];              //counter table for ingredients
    public  $phase = '0';               //phase read from DB
    public  $receipt_name = '';         //receipt name
    #endregion  

    #region Dieta Class Public and construct area
    public function __construct($receipt)
    {
        global $CFG;
        $this->CFG = $CFG;

        $this->DB = new mysqli('localhost', 'dieta', 'Dieta123', 'dieta');
        $this->get_config();
        $this->receipt_name = $receipt;
    }

    public function __destruct()
    {
        $this->DB->close();
    }
    #endregion 

    #region Dieta Class Public
    public function submit_handle($submit_value)
    {

        switch ($submit_value) {
            case 'CreateList':
                $Return = $this->submit_create_list_v2();
                break;
            case 'ClearDatabase':
                $Return = $this->submit_clear_database();
                break;    
            case 'AddIgnoredPhrase':
                $Return = $this->submit_add_phrase($_POST['ignoredPhrase'], 'I');
                break;
            case 'AddIngredientPhrase':
                $Return = $this->submit_add_phrase($_POST['ingredientPhrase'], 'S');
                break;               
            default:
                // $Return = $this->submit_create_list();
                break;
        }
        return $Return;
    }

    public function submit_action($get)
    {
        switch ($get['action']) {
            case 'DeletePhrase':
                $Return = $this->delete_phrase($get['phrase']);
                break;
            case 'PhaseUpdate':
                $Return = $this->phase_update_db($get['phase']);
            break;
            case 'DeleteSentance':
                $Return = $this->sentance_update_db($get['id'], '');
                break;   
            case 'MoveSentance':
                $Return = $this->sentance_update_db($get['id'], $get['status']);
                break;
            default:
                # code...
                break;
        }
        return $Return;
    }

    public function generate_sentances_in_html($status = '')
    {
        global $CFG; 
        require_once($CFG['ClassPath'].'Template.php');
        $template = new Template($CFG['TemplatePath']);
        switch ($status) {
            case 'I':
                $RowTemplate =  $template->ReadTemplate($CFG['TemplatePath'].'dieta_index_sentance_row.html');
                $sort = 'DESC';
                $short = TRUE;
                break;
            case 'C':
                $RowTemplate =  $template->ReadTemplate($CFG['TemplatePath'].'dieta_index_sentance_row_right.html');
                $sort = 'DESC';
                $short = FALSE;
                break;

            default:
                $RowTemplate =  $template->ReadTemplate($CFG['TemplatePath'].'dieta_index_text_sentances_row_v2.html');
                $sort = 'ASC';
                $short = FALSE;
                break;
        }
        
        $TagsFromRowTemplate = $template->GetTagsNames($RowTemplate,'0');
        $ListRowsHtml = '';

        $receipts = $this->generate_sentances_array($status, $sort, $short);
        foreach($receipts as $Row)
        {
            // replace links
            $CurrRowHtml = $RowTemplate;

            //make short version of the sentance
            if($short)
                $Row['sentance'] = substr($Row['sentance'], 0, 50).' ...';

            // replace tags
            foreach ($TagsFromRowTemplate as $Name)
            {
                $CurrRowHtml = preg_replace("/###".$Name."###/",$Row[$Name],$CurrRowHtml);
            }
            // put bufor to ListRows
            $ListRowsHtml .= $CurrRowHtml;
        }
      
        return $ListRowsHtml;    
    }

    public function generate_ingredients_table_in_html()
    {
        //$Type is I - Ignore or S - Skladnik / Ingredient
        global $CFG; 
        require_once($CFG['ClassPath'].'Template.php');
        $template = new Template($CFG['TemplatePath']);
        $RowTemplate =  $template->ReadTemplate($CFG['TemplatePath'].'dieta_index_ingredient_row.html');
        $TagsFromRowTemplate = $template->GetTagsNames($RowTemplate,'0');

        ksort($this->counter);

        $ListRowsHtml = '';
        foreach($this->counter as $ingredient => $count)
        {
            // replace links
            $CurrRowHtml = $RowTemplate;
         
            // replace tags
            $CurrRowHtml = preg_replace("/###".'ingredient'."###/",$ingredient,$CurrRowHtml);
            $CurrRowHtml = preg_replace("/###".'count'."###/",$count,$CurrRowHtml);
         
           // put bufor to ListRows
            $ListRowsHtml .= $CurrRowHtml;
        }
      
        return $ListRowsHtml;
    }

    public function generate_phrases_table_in_html($Type = null)
    {
        //$Type is I - Ignore or S - Skladnik / Ingredient
        global $CFG; 
        require_once($CFG['ClassPath'].'Template.php');
        $template = new Template($CFG['TemplatePath']);
        $RowTemplate =  $template->ReadTemplate($CFG['TemplatePath'].'dieta_index_phrase_row.html');
        $TagsFromRowTemplate = $template->GetTagsNames($RowTemplate,'0');

        $ListRowsHtml = '';
        foreach($this->generate_phrases_array($Type) as $key => $row)
        {
            
            // replace links
            $CurrRowHtml = $RowTemplate;
            //            $row['monday_switch_link']      = $this->a_href_generation('DEL', 'phrase'     $row['id'], $row['monday']);
 
            // replace tags
            foreach ($TagsFromRowTemplate as $Name)
            {
                $CurrRowHtml = preg_replace("/###".$Name."###/",$row[$Name],$CurrRowHtml);
            }
            // put bufor to ListRows
            $ListRowsHtml .= $CurrRowHtml;
        }
      
        return $ListRowsHtml;
    }

    public function generate_phrases_text_in_html()
    {
        $phrases = $this->generate_sentances_array('C', 'ASC', FALSE);
        $text = '';
        foreach($phrases as $phrase)
        {
            $text .= ' '.$phrase['sentance'];
        }
        $text .= '. ';
        
        //get I and S phrases from text 
        $phrases['I'] = $this->generate_phrases_array('I');
        $phrases['S'] = $this->generate_phrases_array('S');
        
        #region sort
        foreach($phrases['I'] as $phrase_array)
        {
            $phrases_to_sort_I[] = $phrase_array['phrase'];
        }
        array_multisort(array_map('strlen', $phrases_to_sort_I), $phrases_to_sort_I);
        $phrases_to_sort_I = array_reverse($phrases_to_sort_I);

        foreach($phrases['S'] as $phrase_array)
        {
            $phrases_to_sort_S[] = $phrase_array['phrase'];
        }
        array_multisort(array_map('strlen', $phrases_to_sort_S), $phrases_to_sort_S);
        $phrases_to_sort_S = array_reverse($phrases_to_sort_S);
        #endregion

        $regex_opt_start = "/";
        $regex_opt_end = "\W?\s/i";

        //remove I phrases from text
        foreach($phrases_to_sort_I as $phrase)
        {
            $phrase = preg_quote($phrase);
            $phrase = preg_replace("/\//",'\/',$phrase);
            $text = preg_replace("$regex_opt_start"."$phrase"."$regex_opt_end", '', $text);           
            // var_dump($text);
        }

        //remove S phrases from text
        foreach($phrases_to_sort_S as $phrase)
        {
            $phrase_orig = $phrase;
            $phrase = preg_quote($phrase);
            $phrase = preg_replace("/\//",'\/',$phrase);
            $text = preg_replace("$regex_opt_start"."$phrase"."$regex_opt_end", '', $text, -1, $count);
            //count how many ingredients were found, use it later
            $this->counter[$phrase_orig] = $count;         
        }

        //count S phrases from text

        return $text;     
    }
    #endregion
    
    #region Crontab Class Private Submit area   
    private function submit_create_list_v2()
    {
        //clear database
        $this->submit_clear_database();

        //get Receipt text
        $text = $_POST['pdf_text'];

        //get phrases to replace (to avoid unnecessary dots ".")
        $text = $this->replace_predefined_phrases($text);

        //split text into sentances and add to DB
        $sentances = $this->split_text_and_add_to_db($text);
        
    } 

    private function submit_clear_database()
    {
        $sql = "TRUNCATE TABLE `receipts` ";
        if (mysqli_query($this->DB, $sql)) 
        {
            if(mysqli_affected_rows($this->DB) == 0)
            {
                $Return['message'] =  "All Receipts are cleard";
                $Return['ok_code'] = '0';    
            }
            else
            {
                $Return['message'] =  "No records deleted / other error";
                $Return['ok_code'] = '8';
            }
        } 
        else 
        {
            $Return['message'] = 'ERROR: '.mysqli_error($this->DB);
            $Return['ok_code'] = '8';
        }
        return $Return;   
    }

    private function submit_add_phrase($Value = null, $Type = null)
    {
        $sql = "INSERT INTO `phrases` (`phrase`, `type`) VALUES ('$Value', '$Type') ";
        if (mysqli_query($this->DB, $sql)) 
        {
            if(mysqli_affected_rows($this->DB) > 0)
            {
                $Return['message'] =  "Record added successfully";
                $Return['ok_code'] = '0';    
            }
            else
            {
                $Return['message'] =  "No records added / other error";
                $Return['ok_code'] = '8';
            }
        } 
        else 
        {
            $Return['message'] = 'ERROR: '.mysqli_error($this->DB);
            $Return['ok_code'] = '8';
        }
        return $Return;       
    }

    private function submit_reload()
    {
        // var_dump('crontab '.$this->config['crontab_file']);exit;
        exec('crontab '.$this->config['crontab_file']);
    }
    #endregion  

    #region Crontab Class Private area    
    private function generate_sentances_array($status = null, $sort = 'ASC', $short = FALSE)
    {
        $sql = "SELECT * FROM `receipts` WHERE `status` = '$status' ORDER BY `id` $sort ";
        $DBresult = $this->DB->query($sql);
        while($Row = $DBresult->fetch_assoc())
        {
            $Return[$Row['id']] = $Row;
        }
        return $Return;
    }
    private function generate_phrases_array($Type = null)
    {
        //$Type is I - Ignore or S - Skladnik / Ingredient
        $sql = "SELECT * FROM `phrases` WHERE type = '$Type' ORDER BY `phrase` ASC ";

        $DBresult = $this->DB->query($sql);
        while($Row = $DBresult->fetch_assoc())
        {
            $Table[] = $Row;
        }
        return $Table;
    }
    
    private function replace_predefined_phrases($text = null)
    {
        $sql = "SELECT * FROM `phrases_replace` ";
        $DBresult = $this->DB->query($sql);
        while($Row = $DBresult->fetch_assoc())
        {
            $PhrasesReplace[$Row['phrase']] = $Row['phrase_replace'];
        }
        foreach($PhrasesReplace as $Phrase => $PhraseReplace)
        {
            $phrase = preg_quote($Phrase);
            $text = preg_replace("/$phrase/i", $PhraseReplace, $text);   
        }
        return $text;
    }

    private function split_text_and_add_to_db($text = null)
    {
        $sentances = explode('.', $text);
        foreach($sentances as $sentance)
        {
            $sql = "INSERT INTO `receipts` (`id`, `receipt`, `sentance`, `status`, `undo`) VALUES (NULL, '', '$sentance', '', '0') ";
            if($this->DB->query($sql))
            {
                $last_id = $this->DB->insert_id;
                // $Return[$last_id] = array( 'id' => $last_id, 
                //                             'sentance' => $sentance,
                //                             'status' => '',
                //                             'undo' => '0'
                // );
            }
        }
        // return $Return;
    }

    private function delete_phrase($phrase)
    {
        $sql = "DELETE FROM `phrases` WHERE `phrases`.`phrase` = '$phrase' ";
        if (mysqli_query($this->DB, $sql)) 
        {
            if(mysqli_affected_rows($this->DB) > 0)
            {
                $Return['message'] =  "Record deleted successfully";
                $Return['ok_code'] = '0';    
            }
            else
            {
                $Return['message'] =  "No records found for deletion";
                $Return['ok_code'] = '4';
            }
        } 
        else 
        {
            $Return['message'] = 'ERROR: '.mysqli_error($this->DB);
            $Return['ok_code'] = '8';
        }
        return $Return;
    }

    private function phase_update_db($phase)
    {
        $sql = "UPDATE `phase` SET `phase` = '$phase' ";
        if (mysqli_query($this->DB, $sql)) 
        {
            if(mysqli_affected_rows($this->DB) > 0)
            {
                $Return['message'] =  "Phase updated successfully";
                $Return['ok_code'] = '0';    
            }
            else
            {
                $Return['message'] =  "No Phase found for update";
                $Return['ok_code'] = '4';
            }
        } 
        else 
        {
            $Return['message'] = 'ERROR: '.mysqli_error($this->DB);
            $Return['ok_code'] = '8';
        }
        return $Return; 
    }
    private function sentance_update_db($id = null, $status = '')
    {
        $sql = "UPDATE `receipts` SET `status` = '$status' WHERE `receipts`.`id` = $id ";
        if (mysqli_query($this->DB, $sql)) 
        {
            if(mysqli_affected_rows($this->DB) > 0)
            {
                $Return['message'] =  "Record deleted successfully";
                $Return['ok_code'] = '0';    
            }
            else
            {
                $Return['message'] =  "No records found for deletion";
                $Return['ok_code'] = '4';
            }
        } 
        else 
        {
            $Return['message'] = 'ERROR: '.mysqli_error($this->DB);
            $Return['ok_code'] = '8';
        }
        return $Return;
    }
    #endregion

    #region Crontab Class Private internal usage area    

    public function get_config()
    {
        $sql = "SELECT * FROM phase ";

        $DBresult = $this->DB->query($sql);
        while($Row = $DBresult->fetch_assoc())
        {
            $this->phase = $Row["phase"];
        }
    }
    #endregion  
}

?>