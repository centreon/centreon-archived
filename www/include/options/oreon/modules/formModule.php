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

	$tpl->assign("headerMenu_title", $lang["mod_menu_modInfos"]);
	$tpl->assign("headerMenu_title2", $lang['mod_menu_upgradeInfos']);
	$tpl->assign("headerMenu_rname", $lang["mod_menu_module_rname"]);
	$tpl->assign("headerMenu_release", $lang["mod_menu_module_release"]);
	$tpl->assign("headerMenu_release_from", $lang["mod_menu_module_release_from"]);
	$tpl->assign("headerMenu_release_to", $lang["mod_menu_module_release_to"]);
	$tpl->assign("headerMenu_author", $lang["mod_menu_module_author"]);
	$tpl->assign("headerMenu_infos", $lang["mod_menu_module_additionnals_infos"]);
	$tpl->assign("headerMenu_isinstalled", $lang["mod_menu_module_is_installed"]);
	$tpl->assign("headerMenu_isvalid", $lang["mod_menu_module_is_validUp"]);

	if ($name)	{
		$flag = false;
		include_once("./modules/".$name."/conf.php");
		$tpl->assign("module_rname", $module_conf[$name]["rname"]);	
		$tpl->assign("module_release", $module_conf[$name]["release"]);
		$tpl->assign("module_author", $module_conf[$name]["author"]);
		$tpl->assign("module_infos", $module_conf[$name]["infos"]);
		$tpl->assign("module_isinstalled", $lang["no"]);
		
		$form1 = new HTML_QuickForm('Form', 'post', "?p=".$p);
		if ($form1->validate())	{
			# Insert Module in DB
			$flag = insertModuleInDB($name, $module_conf[$name]);
			if ($flag)	{
				$tpl->assign("output1", $lang["mod_menu_output1"]);
				# SQL insert if need
				$sql_file = "install.sql";
				$sql_file_path = "./modules/".$name."/sql/".$sql_file;
				if ($module_conf[$name]["sql_files"] && file_exists($sql_file_path))	{
					$tpl->assign("output2", $lang["mod_menu_output2"]);
					$sql_stream = file($sql_file_path);
		            $str = NULL;
		            for ($i = 0; $i <= count($sql_stream) - 1; $i++)	{
			            $line = $sql_stream[$i];
			            if ($line[0] != '#')    {
			                $pos = strrpos($line, ";");
			                if ($pos != false)      {
			                    $str .= $line;
			                    $str = chop ($str);
			                    $DBRESULT =& $pearDB->query($str);
			                    if (PEAR::isError($DBRESULT))
									print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			                    $str = NULL;
			                }
			                else
			                	$str .= $line;
			            }
		            }
				}
				# PHP execution if need
				$php_file = "install.php";
				$php_file_path = "./modules/".$name."/php/".$php_file;
				if ($module_conf[$name]["php_files"] && file_exists($php_file_path))	{
					$tpl->assign("output3", $lang["mod_menu_output3"]);
					include_once($php_file_path);
				}
			}
			else
				$tpl->assign("output4", $lang["mod_menu_output4"]);				
		}
		if (!$flag)
			$form1->addElement('submit', 'install', $lang["mod_menu_listAction_install"]);
		$redirect =& $form1->addElement('hidden', 'o');
		$redirect->setValue("i");		
		$hid_name =& $form1->addElement('hidden', 'name');
		$hid_name->setValue($name);
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$form1->accept($renderer);
		$tpl->assign('form1', $renderer->toArray());
	}
	else if ($id)	{
		$moduleinfo = getModuleInfoInDB(NULL, $id);
		$tpl->assign("module_rname", $moduleinfo["rname"]);
		$tpl->assign("module_release", $moduleinfo["release"]);
		$tpl->assign("module_author", $moduleinfo["author"]);
		$tpl->assign("module_infos", $moduleinfo["infos"]);
		$tpl->assign("module_isinstalled", $lang["yes"]);
		
		# Option de suppression
		# Option de upgrade
		if (is_dir("./modules/".$moduleinfo["name"]."/UPGRADE"))	{
			$handle = opendir("./modules/".$moduleinfo["name"]."/UPGRADE");
			$i = 0;
			$elemArr = array();
			while (false !== ($filename = readdir($handle)))	{
				if ($filename != "." && $filename != ".." && strstr($filename, $moduleinfo["name"]."-"))	{
					include_once("./modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/conf.php");
					$elemArr[$i] = array("upgrade_rname" => $upgrade_conf[$moduleinfo["name"]]["rname"],
							"upgrade_release_from" => $upgrade_conf[$moduleinfo["name"]]["release_from"],
							"upgrade_release_to" => $upgrade_conf[$moduleinfo["name"]]["release_to"],
							"upgrade_author" => $upgrade_conf[$moduleinfo["name"]]["author"],
							"upgrade_infos" => $upgrade_conf[$moduleinfo["name"]]["infos"],
							"upgrade_is_validUp" => $moduleinfo["release"] == $upgrade_conf[$moduleinfo["name"]]["release_from"] ? $lang["yes"] : $lang["no"],
							"upgrade_choice" => $moduleinfo["release"] == $upgrade_conf[$moduleinfo["name"]]["release_from"] ? true : false);
					$i++;
					if ($moduleinfo["release"] == $upgrade_conf[$moduleinfo["name"]]["release_from"])	{
						$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
						$form->addElement('submit', 'upgrade', $lang["mod_menu_listAction_upgrade"]);
						$redirect =& $form->addElement('hidden', 'o');
						$redirect->setValue("u");		
						$hid_id =& $form->addElement('hidden', 'id');
						$hid_id->setValue($id);
						$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
						$form->accept($renderer);
						$tpl->assign('form', $renderer->toArray());
					}
				}
			}
			closedir($handle);
			$tpl->assign("elemArr", $elemArr);
		}
	}
	#
	##Apply a template definition
	#
	$tpl->display("formModule.ihtml");
?>