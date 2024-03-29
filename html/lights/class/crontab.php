<?php

/* crontab backup 2022-04-30

# DO NOT EDIT THIS FILE - edit the master and reinstall.
# (/tmp/crontab.mYDAgB/crontab installed on Fri Apr 29 19:12:51 2022)
# (Cron version -- $Id: crontab.c,v 2.13 1994/01/17 03:20:37 vixie Exp $)
# m h  dom mon dow   command
0 * * * * php -f /home/pi/public_html/cod/script.php
* * * * * /var/www/html/internet/script
#31 18 * * * /home/pi/bin/lights_on
#17 20 * * * /home/pi/bin/lights_off

#crontab4lights#
*/


/*
TODO
[X] - refresh
[X] - list of the lights / add to schedule
[X] - group the lights
[X] - set on off periods
[ ] - use random times - to calculate start and end time in the on periods
[ ] - continous sequence
[X] - list edit, deletes, etc
*/

class Crontab 
{
    #region Crontab Class attributes    
    private $DB;                        //connection handle to DB
    private $CFG;                       //global config
    private $config = [];               //config read from DB
    private $CrontabSeparator = "#crontab4lights"; //separator in crontab file to split user specific and lights specific entries
    #endregion  

    #region Crontab Class Public area       
    public function __construct()
    {
        global $CFG;
        $this->CFG = $CFG;

        $this->DB = new mysqli('localhost', 'lights', 'Lights123', 'lights');
        $this->get_config();
    }

    public function __destruct()
    {
        $this->DB->close();
    }

    public function submit_handle($submit_value)
    {
        $SubmitArray = explode("_", $submit_value);
        switch ($SubmitArray[0]) {
            case 'Append':
                return $this->submit_append();
                break;
            case 'Cleanup':
                return $this->submit_cleanup();
                break;
            case 'AddLight':
                return $this->submit_add_light();
                break;
            case 'EditLight':
                return $this->submit_edit_light();
                break;
            case 'DeleteLight':
                return $this->submit_delete_light($SubmitArray[1]);
                break;
            case 'MoveUpLight':
                return $this->submit_move_up_light($SubmitArray[1]);
                break;
            case 'MoveDownLight':
                return $this->submit_move_down_light($SubmitArray[1]);
                break;        
            default:
                $this->header_go_to_main_page();
                break;
        }
    }

    public function schedule_table_in_html()
    {
        global $CFG; 
        require_once($CFG['ClassPath'].'Template.php');
        $template = new Template($CFG['TemplatePath']);
        $RowTemplate =  $template->ReadTemplate($CFG['TemplatePath'].'schedule_row.html');
        $TagsFromRowTemplate = $template->GetTagsNames($RowTemplate,'0');

        $ListRowsHtml = '';
        foreach($this->generate_schedule_lines() as $key => $row)
        {
            // replace links
            $CurrRowHtml = $RowTemplate;
            $row['monday_switch_link']      = $this->a_href_generation('SWITCH', 'schedule', 'monday',      $row['id'], $row['monday']);
            $row['tuesday_switch_link']     = $this->a_href_generation('SWITCH', 'schedule', 'tuesday',     $row['id'], $row['tuesday']);
            $row['wednesday_switch_link']   = $this->a_href_generation('SWITCH', 'schedule', 'wednesday',   $row['id'], $row['wednesday']);
            $row['thursday_switch_link']    = $this->a_href_generation('SWITCH', 'schedule', 'thursday',    $row['id'], $row['thursday']);
            $row['friday_switch_link']      = $this->a_href_generation('SWITCH', 'schedule', 'friday',      $row['id'], $row['friday']);
            $row['saturday_switch_link']    = $this->a_href_generation('SWITCH', 'schedule', 'saturday',    $row['id'], $row['saturday']);
            $row['sunday_switch_link']      = $this->a_href_generation('SWITCH', 'schedule', 'sunday',      $row['id'], $row['sunday']);
            $row['time_on_switch_link']     = $this->a_href_generation('SWITCH', 'schedule', 'time_on',     $row['id'], $row['time_on']);
            $row['time_off_switch_link']    = $this->a_href_generation('SWITCH', 'schedule', 'timne_off',   $row['id'], $row['time_off']);
            $row['active_switch_link']      = $this->a_href_generation('SWITCH', 'schedule', 'active',      $row['id'], $row['active']);
 
            // replace links btn-color
            if($row['monday'] == 'X')
                $row['btn_color_monday']      = 'success';
            else
                $row['btn_color_monday']      = 'danger';

            if($row['tuesday'] == 'X')
                $row['btn_color_tuesday']     = 'success';
            else            
                $row['btn_color_tuesday']     = 'danger';

            if($row['wednesday'] == 'X')
                $row['btn_color_wednesday']   = 'success';
            else
                $row['btn_color_wednesday']   = 'danger';

            if($row['thursday'] == 'X')
                $row['btn_color_thursday']    = 'success';
            else
                $row['btn_color_thursday']    = 'danger';

            if($row['friday'] == 'X')
                $row['btn_color_friday']      = 'success';
            else
                $row['btn_color_friday']      = 'danger';

            if($row['saturday'] == 'X')
                $row['btn_color_saturday']    = 'success';
            else                
                $row['btn_color_saturday']    = 'danger';

            if($row['sunday'] == 'X')
                $row['btn_color_sunday']      = 'success';
            else
                $row['btn_color_sunday']      = 'danger';

            // $row['btn_color_time_on']     = 'success';
            // $row['btn_color_time_off']    = 'success';

            if($row['active'] == 'X')
                $row['btn_color_active']      = 'success';
            else
                $row['btn_color_active']      = 'danger';

            
            // replace tags
            foreach ($TagsFromRowTemplate as $Name)
            {
                if($row[$Name] == '' || $row[$Name] == 'X')
                {
                    $row[$Name] = '&nbsp;&nbsp;';
                }
                $CurrRowHtml = preg_replace("/###".$Name."###/",$row[$Name],$CurrRowHtml);
            }
            // put bufor to ListRows
            $ListRowsHtml .= $CurrRowHtml;
        }
      
        // $RowTemplate = str_replace("###crontab_panel_title###",'Generated Crottab / from active DB entries',$RowTemplate);  //schedule table
        return $ListRowsHtml;

    }

    public function generated_crontab_in_html()
    {
        global $CFG; 
        require_once($CFG['ClassPath'].'Template.php');
        $template = new Template($CFG['TemplatePath']);
        $CrontabTemplate =  $template->ReadTemplate($CFG['TemplatePath'].'crontab_panel.html');

        $CrontabHTML = implode('<br />',$this->generate_crontab_lines());
      
        $CrontabTemplate = str_replace("###crontab_panel_title###",'<span class="glyphicon glyphicon-info-sign"></span> Generated Crontab / from active DB entries',$CrontabTemplate);  //panel title
        $CrontabTemplate = str_replace("###crontab_html###",$CrontabHTML,$CrontabTemplate);                                         //panel content
        return $CrontabTemplate;
    }

    public function crontab_in_html()
    {
        global $CFG; 
        require_once($CFG['ClassPath'].'Template.php');
        $template = new Template($CFG['TemplatePath']);
        $CrontabTemplate =  $template->ReadTemplate($CFG['TemplatePath'].'crontab_panel.html');
        
        $CrontabHTML = $this->get_crontab_for_user();
        $CrontabHTML = str_replace("\n","<br />", $CrontabHTML);
            
        $CrontabTemplate = str_replace("###crontab_panel_title###",'<span class="glyphicon glyphicon-time"></span> Actual Crontab',$CrontabTemplate);    
        $CrontabTemplate = str_replace("###crontab_html###",$CrontabHTML,$CrontabTemplate);
        return $CrontabTemplate;
    }

    public function update_fields_from_link($Array = null)
    {
        extract($Array);
        switch ($Action) {
            case 'EDIT':
                // currently no modification of values planned
                break;
            
            case 'SWITCH':
                if ($Value == 'X')
                    $Value = '';
                else
                    $Value = 'X'; 
                break;
            
            default:
                # code...
                break;
        }
		//array(4) { ["Action"]=> string(6) "SWITCH" ["Table"]=> string(8) "schedule" ["Field"]=> string(6) "monday" ["Value"]=> string(1) "X" }
        $sql = "UPDATE `$Table` SET `$Field` = '$Value' WHERE `$Table`.`id` = $ID;";
        if ($this->DB->query($sql) === TRUE) 
        {
            $Return['message'] =  "Record updated successfully";
            $Return['ok_code'] = '0';
        } 
        else 
        {
            $Return['message'] = $conn->error;
            $Return['ok_code'] = '4';
        }
        return $Return;
    }
    #endregion  

    #region Crontab Class Private Submit area    
    private function header_go_to_main_page()
    {
        header('Location: '.$this->CFG["PageURL"].'lights');exit;
    }

    private function get_crontab_for_user($case = "")
    {
        $CrontabShell = shell_exec('sudo -u pi crontab -l');
        $CrontabLights = FALSE;
        //loop crontab lines to find lights section starting with #$$$
        foreach(preg_split("/((\r?\n)|(\r\n?))/", $CrontabShell) as $line){
            if ($line == $this->CrontabSeparator)
                $CrontabLights = TRUE;
            if ($CrontabLights)
                $CrontabLightsSpecific .= $line."\n";
            else
                $CrontabUserSpecific .= $line."\n";
        }
        switch ($case) {
            case 'array':
                $CrontabArray['user']   = $CrontabUserSpecific;
                $CrontabArray['lights'] = $CrontabLightsSpecific;
                return $CrontabArray;
                break;
            default:
                return $CrontabLightsSpecific;
                break;
        }
    }
    
    private function deploy_crontab_from_string($CrontabEchoString = "")
    {
        if ($CrontabEchoString == "")
        {
            $Return['message'] = 'Crontab not updated, given string is empty!';
            $Return['ok_code'] = '4';
            return $Return;
        }
        shell_exec("{ echo '$CrontabEchoString'; } | sudo -u pi crontab -u pi -");   
        $Return['message'] = 'Crontab updated!';
        $Return['ok_code'] = '0';
        return $Return;
    }

    private function submit_append()
    {
        $CrontabArray = $this->get_crontab_for_user('array');

        $CrontabEchoString = $CrontabArray['user'];
        $CrontabEchoString .= $CrontabArray['lights'];

        $CrontabGeneratedArray = $this->generate_crontab_lines();
        foreach($CrontabGeneratedArray as $line){
            //shell_exec("{ sudo -u pi crontab -u pi -l; echo '$line'; } | sudo -u pi crontab -u pi -");
            $CrontabEchoString .= $line."\n";
        }
        return $this->deploy_crontab_from_string($CrontabEchoString);
    }
    private function submit_cleanup()
    {
        $CrontabArray = $this->get_crontab_for_user('array');

        $CrontabEchoString = $CrontabArray['user'];
        $CrontabEchoString .= $this->CrontabSeparator;

        return $this->deploy_crontab_from_string($CrontabEchoString);
    }

    private function submit_add_light()
    {
        $LightName = $_POST['select_light_name'];
        $GroupName = $_POST['input_group_name'];

        $sql = "INSERT INTO `schedule` (`id`, `sort`, `light_name`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`, `sunday`, `time_on`, `time_off`, `group_name`, `active`)
                VALUES (NULL, 999999, '$LightName', '', '', '', '', '', '', '', NULL, NULL, '$GroupName', '') ";


        if (mysqli_query($this->DB, $sql)) 
        {
            if(mysqli_affected_rows($this->DB) > 0)
            {
                $sql = "UPDATE schedule SET sort = id WHERE id = '".mysqli_insert_id($this->DB)."' ";
                mysqli_query($this->DB, $sql);
                $Return['message'] = 'Light added to Schedule list!';
                $Return['ok_code'] = '0';    
            }
            else
            {
                $Return['message'] =  "Light not added / other error";
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

    private function submit_edit_light()
    {
        $r = $_POST[r];

        $sql = "UPDATE `schedule` SET `time_on` = '$r[input_timeon]', `time_off` = '$r[input_timeoff]' WHERE `schedule`.`id` = '$r[input_id]' ";

        if (mysqli_query($this->DB, $sql)) 
        {
            if(mysqli_affected_rows($this->DB) > 0)
            {
                $Return['message'] =  "Light updated successfully";
                $Return['ok_code'] = '0';    
            }
            else
            {
                $Return['message'] =  "No records found for update";
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

    private function submit_delete_light($lightID)
    {
        $sql = "DELETE FROM `schedule` WHERE `schedule`.`id` = '$lightID' ";
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

    private function submit_move_up_light($lightID)
    {
        $prev_next = $this->get_prev_and_next_lightID($lightID, 'prev');
        $Return = $this->update_up_down_id($prev_next);
                
        return $Return; 
    }

    private function submit_move_down_light($lightID)
    {
        $prev_next = $this->get_prev_and_next_lightID($lightID, 'next');
        $Return = $this->update_up_down_id($prev_next);

        return $Return;
    }
    #endregion  

    #region Crontab Class Private generate area    
    private function update_up_down_id($PrevNext)
    {
        if ($PrevNext !== false)
        {
            $sql =  "UPDATE `schedule` SET `sort` = '".$PrevNext['next_sort']."' WHERE `schedule`.`id` = '".$PrevNext['curr_id']."';";
            
            if (mysqli_query($this->DB, $sql)) 
            {
                if(mysqli_affected_rows($this->DB) > 0)
                {
                    $sql = "UPDATE `schedule` SET `sort` = '".$PrevNext['curr_sort']."' WHERE `schedule`.`id` = '".$PrevNext['next_id']."' ";
                    mysqli_query($this->DB, $sql);

                    $Return['message'] =  "Light updated successfully";
                    $Return['ok_code'] = '0';    
                }
                else
                {
                    $Return['message'] =  "No records found for update";
                    $Return['ok_code'] = '4';
                }
            } 
            else 
            {
                $Return['message'] = 'ERROR: '.mysqli_error($this->DB);
                $Return['ok_code'] = '8';
            }
        }
        else
        {
            $Return['message'] = 'It is already on first/last position';
            $Return['ok_code'] = '4';
        }
        return $Return;

    }

    private function get_prev_and_next_lightID($lightID, $direction)
    {
        $sql = "SELECT * FROM schedule ORDER BY sort ASC ";

        $DBresult = $this->DB->query($sql);
        while($Row = $DBresult->fetch_assoc())
        {
            $Lights[$Row['id']] = $Row['sort'];
        }

        switch ($direction) {
            case 'next':
                $Element = false;
                foreach ($Lights as $id => $sort) {
                    $Element = next($Lights);
                    if ($id == $lightID) {
                        break;
                    }
                }
                break;
            case 'prev':
                $Element = false;
                foreach ($Lights as $id => $sort) {
                    if ($id == $lightID) {
                        $Element = prev($Lights);
                        break;
                    }
                    $Element = next($Lights);
                }
                break;                
        }
        if($Element === false) {
            return false;
        }
        else {
            $LightsKeys = array_keys($Lights, $Element);
            return array(   'curr_id'   => $lightID,
                            'curr_sort' => $Lights[$lightID],
                            'next_id'   => $LightsKeys[0],
                            'next_sort' => $Element);    
        }
    }

    private function generate_crontab_line($row,$state)
    {
        // # 10 20 * * 1,2,3,4,5,6,0 mosquitto_pub -h 10.0.0.10 -p 1883 -u mqtt-loyd -P Loyd1982 -t "cmnd/livingroom_main/POWER" -m "ON"
        // #
        // # m h dom mon dow command
        
        switch ($state) {
            case 'ON':
                $time_on_off = 'time_on';
                break;
            case 'OFF':
                $time_on_off = 'time_off';
                break;
            default:
                echo 'shit, should not happen!';
                break;
        }
          
        preg_match('/(\d{2}):(\d{2}):\d{2}/',$row[$time_on_off],$match);
        $h = $match[1];
        $m = $match[2];
        $dom = '*';
        $mon = '*';
        $dow = $this->generate_dow_from_schedule_row($row);    
        $command = $this->generate_command($row['mqtt_topic'],$state);
        return $m.' '.$h.' '.$dom.' '.$mon.' '.$dow.' '.$command;
    }

    private function generate_command($topic, $state)
    {
        $command = $this->config['command'];
        $command = str_replace("###ip###", $this->config['ip'], $command);
        $command = str_replace("###port###", $this->config['port'], $command);
        $command = str_replace("###user###", $this->config['user'], $command);
        $command = str_replace("###password###", $this->config['password'], $command);
        $command = str_replace("###topic###", $topic, $command);
        $command = str_replace("###state###", $state, $command);

        return $command;
    }

    private function generate_crontab_lines()
    {
        $sql = 'SELECT s.*,t.topic AS mqtt_topic FROM schedule AS s join topics AS t ON t.light_name = s.light_name WHERE s.active = "X" ORDER BY s.sort ASC ';

        // $DBresult = $this->DB->query($sql);
        $DBresult = mysqli_query($this->DB, $sql);

        while($row = $DBresult->fetch_assoc())
        {
            $schedule[] = $this->generate_crontab_line($row,'ON');
            $schedule[] = $this->generate_crontab_line($row,'OFF');
        }
        return $schedule;
    }

    private function generate_schedule_lines()
    {
        $sql = 'SELECT s.*,t.topic AS mqtt_topic FROM schedule AS s join topics AS t ON t.light_name = s.light_name ORDER BY s.sort ASC ';
        $DBresult = $this->DB->query($sql);
    
        while($row = $DBresult->fetch_assoc())
        {
            $schedule[$row['id']] = $row;
        }
        return $schedule;
    }

    private function generate_dow_from_schedule_row($row)    
    {
        $string = [];
        if ($row['monday'] == 'X')
            $string[] = '1';
        if ($row['tuesday'] == 'X')
            $string[] = '2';
        if ($row['wednesday'] == 'X')
            $string[] = '3';
        if ($row['thursday'] == 'X')
            $string[] = '4';
        if ($row['friday'] == 'X')
            $string[] = '5';
        if ($row['saturday'] == 'X')
            $string[] = '6';
        if ($row['sunday'] == 'X')
            $string[] = '0';
        return implode(',', $string);
    }

    private function a_href_generation($Action = null, $Table = null, $Field = null, $ID = null, $Value = null)
    {
        $link = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/update.php";
        $link .= "?Action=$Action&Table=$Table&Field=$Field&ID=$ID&Value=$Value";
        
        return $link;
    }
#endregion  

    #region Crontab Class Private internal usage area    

    private function get_config()
    {
        $sql = "SELECT * FROM config ";

        $DBresult = $this->DB->query($sql);
        while($Row = $DBresult->fetch_assoc())
        {
            $this->config[$Row["id"]] = $Row["value"];
        }
    }
    #endregion  
}

?>