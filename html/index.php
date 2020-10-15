<?php
    require_once('/var/www/config/config.php');
    require_once($CFG['ClassPath'].'Template.php');
  
    $template = new Template($CFG['TemplatePath']);
    $template->Start('index.html');
  
/***********************************************************
*	Get plugins to $Plugins array
***********************************************************/
    $DBconn = new mysqli($_DB['servername'], $_DB['username'], $_DB['password'], $_DB['dbname']);

    $sql = "SELECT * FROM plugins WHERE plugin_active = 'X' ORDER BY plugin_position ASC";
    $DBresult = $DBconn->query($sql);

    while($Row = $DBresult->fetch_assoc())
    {
	$Plugins[$Row['plugin_id']] = $Row;
	if ((include $Row['plugin_path']) == TRUE) 			//load plugin class
	{
	    $classObject = new $Row['plugin_id']();			//create plugin class object dynamically
	    $Plugins[$Row['plugin_id']]['class'] = $classObject;	//add object to Plugins array
//
	    unset($classObject);					//free object memory for next plugin
	}
    }
    $DBconn->close();
/******************************************************/
    $PluginsTemplate =  $template->ReadTemplate($CFG['TemplatePath'].'index_plugins.html');
    $PluginsItemTemplate = $template->ReadTemplate($CFG['TemplatePath'].'index_plugins_item.html');

    $TagsFromItemTemplate = $template->GetTagsNames($PluginsItemTemplate,'0');
    $PluginsHTML = '';
    foreach($Plugins as $plugin_name => $plugin)
    {
	// put row tempalte in bufor 
        $CurrItemHtml = $PluginsItemTemplate;

	$row['title']   = $plugin['plugin_title'];
	$row['content'] = $plugin['class']->content;
	$row['style_color'] = $plugin['class']->style_color;
	$row['plugin_link'] = $plugin['plugin_link'];

	// replace tags 
        foreach ($TagsFromItemTemplate as $Name)
        {
              $CurrItemHtml = preg_replace("/###".$Name."###/",$row[$Name],$CurrItemHtml);
        }
	$PluginsHTML .= $CurrItemHtml;
    }
    //replace tag PluginsItems with all plugins html in Plugins Template 
    $PluginsTemplate = str_replace("###PluginsItems###",$PluginsHTML,$PluginsTemplate);
    // Set all Plugins html to main template 
    $template->SetTagValue("Plugins",$PluginsTemplate);

    $template->ReplaceTags();
    $template->ReplaceAllTags();
    echo $template->Show();
?>
