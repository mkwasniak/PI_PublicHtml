<?php

class Crontab 
{
    #region Crontab Class attributes    
    private $DB;                        //connection handle to DB
    private $CFG;                       //global config
    private $config = [];               //config read from DB
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
        switch ($submit_value) {
            case 'Deploy':
                $this->submit_deploy();
                break;
            case 'Reload':
                $this->submit_reload();
                break;
                
            default:
                # code...
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
      
        $CrontabTemplate = str_replace("###crontab_panel_title###",'Generated Crottab / from active DB entries',$CrontabTemplate);  //panel title
        $CrontabTemplate = str_replace("###crontab_html###",$CrontabHTML,$CrontabTemplate);                                         //panel content
        return $CrontabTemplate;
    }

    public function crontab_in_html()
    {
        global $CFG; 
        require_once($CFG['ClassPath'].'Template.php');
        $template = new Template($CFG['TemplatePath']);
        $CrontabTemplate =  $template->ReadTemplate($CFG['TemplatePath'].'crontab_panel.html');
        
        $CrontabHTML = shell_exec('crontab -l');
        $CrontabHTML = str_replace("\n","<br />", $CrontabHTML);
            
        $CrontabTemplate = str_replace("###crontab_panel_title###",'Actual Crontab',$CrontabTemplate);    
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
    private function submit_deploy()
    {
        $lines = $this->generate_crontab_lines();
        file_put_contents($this->config['crontab_file'], implode(PHP_EOL, $lines));
        file_put_contents($this->config['crontab_file'], PHP_EOL, FILE_APPEND);
    }
    private function submit_reload()
    {
        // var_dump('crontab '.$this->config['crontab_file']);exit;
        exec('crontab '.$this->config['crontab_file']);
    }
    #endregion  

    #region Crontab Class Private generate area    
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
        $sql = 'SELECT s.*,t.topic AS mqtt_topic FROM schedule AS s join topics AS t ON t.light_name = s.light_name WHERE s.active = "X" ';
        $DBresult = $this->DB->query($sql);
    
        while($row = $DBresult->fetch_assoc())
        {
            $schedule[] = $this->generate_crontab_line($row,'ON');
            $schedule[] = $this->generate_crontab_line($row,'OFF');
        }
        return $schedule;
    }

    private function generate_schedule_lines()
    {
        $sql = 'SELECT s.*,t.topic AS mqtt_topic FROM schedule AS s join topics AS t ON t.light_name = s.light_name ';
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