<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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

	# "Name" case, it's not a module which is installed
	if ($name)	{
		$flag = false;
		include_once("./modules/".$name."/conf.php");
		$tpl->assign("module_rname", $module_conf[$name]["rname"]);	
		$tpl->assign("module_release", $module_conf[$name]["mod_release"]);
		$tpl->assign("module_author", $module_conf[$name]["author"]);
		$tpl->assign("module_infos", $module_conf[$name]["infos"]);
		if (is_dir("./modules/".$name."/infos") && is_file("./modules/".$name."/infos/infos.txt"))	{
			$infos_streams = file("./modules/".$name."/infos/infos.txt");
			$infos_streams = implode("<br>", $infos_streams);
			$tpl->assign("module_infosTxt", $infos_streams);
		}
		else
			$tpl->assign("module_infosTxt", false);		

		$form1 = new HTML_QuickForm('Form', 'post', "?p=".$p);
		if ($form1->validate())	{
			# Insert Module in DB
			$insert_ok = insertModuleInDB($name, $module_conf[$name]);
			if ($insert_ok)	{
				$tpl->assign("output1", $lang["mod_menu_output1"]);
				# SQL insert if need
				$sql_file = "install.sql";
				$sql_file_path = "./modules/".$name."/sql/";
				if ($module_conf[$name]["sql_files"] && file_exists($sql_file_path.$sql_file))	{
					$tpl->assign("output2", $lang["mod_menu_output2"]);
					execute_sql_file($sql_file, $sql_file_path);	
				}
				# PHP execution if need
				$php_file = "install.php";
				$php_file_path = "./modules/".$name."/php/".$php_file;
				if ($module_conf[$name]["php_files"] && file_exists($php_file_path))	{
					$tpl->assign("output3", $lang["mod_menu_output3"]);
					include_once($php_file_path);
				}
				
			# Load module lang file without re-login
		    $lang_file_path = "./modules/".$name."/lang/". $oreon->user->get_lang().".php";
				if ($module_conf[$name]["lang_files"] && file_exists($lang_file_path))	{
					include_once($lang_file_path);
				}
				
			}
			else
				$tpl->assign("output4", $lang["mod_menu_output4"]);
		}
		else	{
			$form1->addElement('submit', 'install', $lang["mod_menu_listAction_install"]);
			$redirect =& $form1->addElement('hidden', 'o');
			$redirect->setValue("i");
		}
		$form1->addElement('submit', 'list', $lang["back"]);
		$hid_name =& $form1->addElement('hidden', 'name');
		$hid_name->setValue($name);
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$form1->accept($renderer);
		$tpl->assign('form1', $renderer->toArray());
	}
	# "ID" case, it's an installed module
	else if ($id)	{
		$moduleinfo = getModuleInfoInDB(NULL, $id);
		$elemArr = array();
		if (is_dir("./modules/".$moduleinfo["name"]."/UPGRADE"))	{
			$handle = opendir("./modules/".$moduleinfo["name"]."/UPGRADE");
			$i = 0;
			$elemArr = array();
			while (false !== ($filename = readdir($handle)))	{
				if ($filename != "." && $filename != ".." && strstr($filename, $moduleinfo["name"]."-"))	{
					include_once("./modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/conf.php");
					if ($moduleinfo["mod_release"] == $upgrade_conf[$moduleinfo["name"]]["release_from"])	{
						$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
						$upgrade_ok = false;			
						# Upgrade
						if ($form->validate())	{
							# DB Upgrade
							$upgrade_ok = upgradeModuleInDB($id, $upgrade_conf[$moduleinfo["name"]]);
							if ($upgrade_ok)	{
								$tpl->assign("output1", $lang["mod_menu_output1"]);
								# SQL update if need
								$sql_file = "upgrade.sql";
								$sql_file_path = "./modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/sql/";
								if ($upgrade_conf[$moduleinfo["name"]]["sql_files"] && file_exists($sql_file_path.$sql_file))	{
									$tpl->assign("output2", $lang["mod_menu_output2"]);
									execute_sql_file($sql_file, $sql_file_path);	
								}
								# PHP update if need
								$php_file = "upgrade.php";
								$php_file_path = "./modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/php/".$php_file;
								if ($upgrade_conf[$moduleinfo["name"]]["php_files"] && file_exists($php_file_path))	{
									$tpl->assign("output3", $lang["mod_menu_output3"]);
									include_once($php_file_path);
								}
							}
							else
								$tpl->assign("output4", $lang["mod_menu_output4"]);
						}
						if (!$upgrade_ok)	{						
							$form->addElement('submit', 'upgrade', $lang["mod_menu_listAction_upgrade"]);
							$redirect =& $form->addElement('hidden', 'o');
							$redirect->setValue("u");							
						}
						if (is_dir("./modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/infos") && is_file("./modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/infos/infos.txt"))	{
							$infos_streams = file("./modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/infos/infos.txt");
							$infos_streams = implode("<br>", $infos_streams);
							$upgrade_infosTxt = $infos_streams;
						}
						else
							$upgrade_infosTxt = false;	
						$elemArr[$i] = array("upgrade_rname" => $upgrade_conf[$moduleinfo["name"]]["rname"],
							"upgrade_release_from" => $upgrade_conf[$moduleinfo["name"]]["release_from"],
							"upgrade_release_to" => $upgrade_conf[$moduleinfo["name"]]["release_to"],
							"upgrade_author" => $upgrade_conf[$moduleinfo["name"]]["author"],
							"upgrade_infos" => $upgrade_conf[$moduleinfo["name"]]["infos"],
							"upgrade_infosTxt" => $upgrade_infosTxt,
							"upgrade_is_validUp" => $moduleinfo["mod_release"] == $upgrade_conf[$moduleinfo["name"]]["release_from"] ? $lang["yes"] : $lang["no"],
							"upgrade_choice" => $moduleinfo["mod_release"] == $upgrade_conf[$moduleinfo["name"]]["release_from"] ? true : false);	
						$i++;
						$hid_id =& $form->addElement('hidden', 'id');
						$hid_id->setValue($id);
						$up_name =& $form->addElement('hidden', 'filename');
						$up_name->setValue($filename);
						$form->addElement('submit', 'list', $lang["back"]);					
						$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
						$form->accept($renderer);
						$tpl->assign('form', $renderer->toArray());
					}
				}
			}
			closedir($handle);	
		}
		$moduleinfo = array();
		$moduleinfo = getModuleInfoInDB(NULL, $id);
		$tpl->assign("module_rname", $moduleinfo["rname"]);
		$tpl->assign("module_release", $moduleinfo["mod_release"]);
		$tpl->assign("module_author", $moduleinfo["author"]);
		$tpl->assign("module_infos", $moduleinfo["infos"]);
		$tpl->assign("module_isinstalled", $lang["yes"]);
		$tpl->assign("elemArr", $elemArr);
		$form2 = new HTML_QuickForm('Form', 'post', "?p=".$p);
		$form2->addElement('submit', 'list', $lang["back"]);					
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$form2->accept($renderer);
		$tpl->assign('form2', $renderer->toArray());
	}
	#
	##Apply a template definition
	#
	$tpl->display("formModule.ihtml");
?>