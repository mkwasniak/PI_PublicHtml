<?php

class Tree 
{
    #region Crontab Class attributes    
    private $DB;                        //connection handle to DB
    private $CFG;                       //global config
	private $config = [];               //config read from DB
	private $CP = [];					//Child Parents relation
	private $HW = [];					//Husband Wife relation
    private $TreePersons = array();     //persons details used to draw tree
    private $TreeCore;                //Tree Core instance
    private $StartX = 700;
    private $StartY = 500;
#endregion  

    #region Tree Class Public area       
    public function __construct()
    {
        global $CFG;
        $this->CFG = $CFG;
        $this->TreeCore = new TreeCore();


        $this->DB = new mysqli('localhost', 'genes', 'Genes!23', 'genes');
    }

    public function __destruct()
    {
        $this->DB->close();
    }

    public function submit_handle($submit_value)
    {
        switch ($submit_value) {
            case 'Deploy':
                // $this->submit_deploy();
                break;
            case 'Reload':
                // $this->submit_reload();
                break;
                
            default:
                # code...
                break;
        }
    }

    public function nodes_in_html()
    {
		global $CFG; 
        require_once($CFG['ClassPath'].'Template.php');
        $template = new Template($CFG['TemplatePath']);
        $NodeTemplate =  $template->ReadTemplate($CFG['TemplatePath'].'node_line.html');
		$Nodes = $this->generate_nodes_lines();
		// var_dump($Nodes);exit;
		$retTemplate = '';
		foreach($Nodes as $k => $v)
		{
			$currTemplate = $NodeTemplate;
			$currTemplate = str_replace("###id###", $v['id'], $currTemplate);
			$currTemplate = str_replace("###pid###", $v['pid'], $currTemplate);
			$currTemplate = str_replace("###name###", $v['name'], $currTemplate);
			$retTemplate .= $currTemplate;
		}
		return $retTemplate;
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
	private function generate_nodes_lines()
    {
        $sql = 'SELECT r.*, p.* FROM relations AS r JOIN persons AS p ON r.person_id_right = p.person_id ORDER BY r.relation_type ASC, r.person_id_left ASC, r.person_id_right ASC';
        // $sql = 'SELECT s.*,t.topic AS mqtt_topic FROM schedule AS s join topics AS t ON t.light_name = s.light_name ';
        $DBresult = $this->DB->query($sql);
    
        while($row = $DBresult->fetch_assoc())
        {
			switch ($row['relation_type']) {
				case 'CP':
					$this->CP[$row['person_id_left']][$row['person_id_right']] = $row;
					break;
				case 'HW':
					$this->HW[$row['person_id_left']][$row['person_id_right']] = $row;
					break;
				default:
					# code...
					break;
			}
		}
		$parents = $this->get_parents(1);
		$parents[] = array('id' => 1, 'pid' => 0, 'name' => 'Maciej');
		// var_dump($this->CP);
		return $parents;
    }
	private function get_parents($child_id)
	{
		$ret = [];
		if(array_key_exists($child_id, $this->CP))
		{
			foreach($this->CP[$child_id] as $parent_id => $parent_details)
			{
				// echo $child_id.'-'.$parent_id.'/';
				$ret2 = $this->get_parents($parent_id);
				if($ret2[0] != NULL)
				{
					$ret = array_merge($ret,$ret2);
				}
				$ret[] = array('id' => $parent_id, 'pid' => $child_id, 'name' => $parent_details['first_name']);
			}
			return $ret;
		}
	}
	
#endregion  

#region Select Options
    public function html_select_get_persons_all() 
    {
        $sql = 'SELECT * FROM persons ORDER BY last_name ASC, first_name ASC ';
        $DBresult = $this->DB->query($sql);

        $html_select = "<option selected value=''>Choose...</option>\n";
        while($row = $DBresult->fetch_assoc())
        {
            if ($row['birth_date'] == NULL)
                $html_select .= "<option value='{$row[person_id]}'>".$row['first_name']." ".$row['last_name']."</option> \n";
            else
                $html_select .= "<option value='{$row[person_id]}'>".$row['first_name']." ".$row['last_name']." - ur. ".$row['birth_date']."</option> \n";
        }
        return $html_select;
    }
    public function html_generate_family_tree()
    {
        $html = '';

        //draw 1st person with partner
        $this->TreePersons[1] = new TreePerson(TRUE, $this->StartX, $this->StartY, $this->TreeCore);
        $html .= $this->TreePersons[1]->draw_person_visit_card($this->TreeCore);
        $html .= $this->TreePersons[1]->draw_person_space_frame($this->TreeCore);

        //draw father with partner
        $this->TreePersons[2] = new TreePerson(TRUE, 0, 0, $this->TreeCore, $this->TreePersons[1]);
        $html .= $this->TreePersons[2]->draw_person_visit_card($this->TreeCore);


        // $html .= $this->TreeCore->draw_visit_card($this->StartX+300,$this->StartY+300);

        $html .= $this->TreeCore->draw_dot($this->StartX,$this->StartY);
    
        return $html;
    }
#endregion  
}

?>