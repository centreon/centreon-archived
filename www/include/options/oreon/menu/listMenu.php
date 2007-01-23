<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/
	if (!isset($oreon))
		exit();	
		
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", $lang["menu_listName"]);
	$tpl->assign("headerMenu_longname", $lang["menu_listLongName"]);	
	$tpl->assign("headerMenu_dir", $lang["menu_listDir"]);
	$tpl->assign("headerMenu_gen", $lang["menu_listGen"]);
	$tpl->assign("headerMenu_lang", $lang["menu_listLang"]);
	$tpl->assign("headerMenu_sql", $lang["menu_listSql"]);
	$tpl->assign("headerMenu_action", $lang["menu_listAction"]);
	# end header menu
	# Grab Modules
	$oreon->modules = array();
	$handle = opendir("./modules");	
	while (false !== ($filename = readdir($handle)))	{
		if ($filename != "." && $filename != "..")	{
			$moduleinfo = array();
			$moduleinfo = getModuleInfoInDB($filename);
			
			$oreon->modules[$filename]["name"] = $filename;
			
			if (is_dir("./modules/".$filename."/generate_files/"))
				$oreon->modules[$filename]["gen"] = true;
			else
				$oreon->modules[$filename]["gen"] = false;
				
			if (is_dir("./modules/".$filename."/sql/"))
				$oreon->modules[$filename]["sql"] = true;
			else
				$oreon->modules[$filename]["sql"] = false;
				
			if (is_dir("./modules/".$filename."/lang/"))
				$oreon->modules[$filename]["lang"] = true;
			else
				$oreon->modules[$filename]["lang"] = false;
				
			if (is_file("./modules/".$filename."/conf.php")) {
				include_once("./modules/".$filename."/conf.php");
				
			/*	if ( isset($module_conf[$filename]["is_removeable"]) && ($module_conf[$filename]["is_removeable"] == "1"))
					$oreon->modules[$filename]["action_del"] = "1";
				else
					$oreon->modules[$filename]["action_del"] = "0"; */

				// If installed and removeable then permit uninstall			
				if (  ( isset($moduleinfo["is_removeable"]) && ($moduleinfo["is_removeable"] == "1"))  && ( isset($moduleinfo["is_installed"]) && ($moduleinfo["is_installed"] == "1")) )
					$oreon->modules[$filename]["action_del"] = "0";
				else 
					$oreon->modules[$filename]["action_del"] = "1";				
				
				// If not found in modules table  then permit install			
				if ( isset($moduleinfo["is_installed"]) && ($moduleinfo["is_installed"] == "1"))
					$oreon->modules[$filename]["action_install"] = "1";
				else
					$oreon->modules[$filename]["action_install"] = "0";
					
				$oreon->modules[$filename]["longname"] = $module_conf[$filename]["name"];
			} else { # for built-in modules
				$oreon->modules[$filename]["action_del"] = "1";
				$oreon->modules[$filename]["action_install"] = "1";	
				$oreon->modules[$filename]["longname"] = "-";
			}
				
		}
	}
	closedir($handle);
	
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	$i = 0;	foreach ($oreon->modules as $mod) {
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_name"=>$mod["name"],
						"RowMenu_longname"=>$mod["longname"],
						"RowMenu_link"=>"?p=".$p."&o=w&name=".$mod["name"],
						"RowMenu_modules_list_link"=>"?p=".$p,
						"RowMenu_dir"=>"./modules/".$mod["name"],
						"RowMenu_gen"=>$mod["gen"],
						"RowMenu_lang"=>$mod["lang"],
						"RowMenu_sql"=>$mod["sql"],
						"RowMenu_action"=>$mod["actions"],
						"RowMenu_action_del"=>$mod["action_del"],
						"RowMenu_action_del_link"=>"?p=".$p."&o=d&name=".$mod["name"],
						"RowMenu_action_install"=>$mod["action_install"],
						"RowMenu_action_install_link"=>"?p=".$p."&o=i&name=".$mod["name"]);
		$style != "two" ? $style = "two" : $style = "one";
		$i++;	}
	$tpl->assign("elemArr", $elemArr);
	$tpl->assign("yes", $lang["yes"]);
	$tpl->assign("no", $lang["no"]);
	$tpl->assign("Action_uninstall", $lang["menu_listAction_del"]);
	$tpl->assign("Action_install", $lang["menu_listAction_install"]);
	
	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$tpl->display("listMenu.ihtml");
	
	/*end menu*/
?>