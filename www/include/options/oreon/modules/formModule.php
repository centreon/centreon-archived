<?php
/*
 * Copyright 2005-2011 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

	if (!isset($oreon))
		exit();

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	$tpl->assign("headerMenu_title", _("Module Information"));
	$tpl->assign("headerMenu_title2", _("Upgrade Information"));
	$tpl->assign("headerMenu_rname", _("Real name"));
	$tpl->assign("headerMenu_release", _("Release"));
	$tpl->assign("headerMenu_release_from", _("Base release"));
	$tpl->assign("headerMenu_release_to", _("Final release"));
	$tpl->assign("headerMenu_author", _("Author"));
	$tpl->assign("headerMenu_infos", _("Additionnal Information"));
	$tpl->assign("headerMenu_isinstalled", _("Installed"));
	$tpl->assign("headerMenu_isvalid", _("Valid for an upgrade"));

	/*
	 * "Name" case, it's not a module which is installed
	 */
	if ($name)	{
		$flag = false;
		include_once($centreon_path . "www/modules/".$name."/conf.php");
		$tpl->assign("module_rname", $module_conf[$name]["rname"]);
		$tpl->assign("module_release", $module_conf[$name]["mod_release"]);
		$tpl->assign("module_author", $module_conf[$name]["author"]);
		$tpl->assign("module_infos", $module_conf[$name]["infos"]);
		if (is_dir($centreon_path . "www/modules/".$name."/infos") && is_file("./modules/".$name."/infos/infos.txt"))	{
			$infos_streams = file($centreon_path . "www/modules/".$name."/infos/infos.txt");
			$infos_streams = implode("<br />", $infos_streams);
			$tpl->assign("module_infosTxt", $infos_streams);
		} else
			$tpl->assign("module_infosTxt", false);

		$form1 = new HTML_QuickForm('Form', 'post', "?p=".$p);
		if ($form1->validate())	{
			/*
			 * Insert Module in DB
			 */
			$insert_ok = insertModuleInDB($name, $module_conf[$name]);
			if ($insert_ok)	{
				$tpl->assign("output1", _("Module installed and registered"));
				/*
				 * SQL insert if need
				 */
				$sql_file = "install.sql";
				$sql_file_path = "./modules/".$name."/sql/";
				if ($module_conf[$name]["sql_files"] && file_exists($sql_file_path.$sql_file))	{
					$tpl->assign("output2", _("SQL file included"));
					execute_sql_file($sql_file, $sql_file_path);
				}
				/*
				 * PHP execution if need
				 */
				$php_file = "install.php";
				$php_file_path = $centreon_path . "www/modules/".$name."/php/".$php_file;
				if ($module_conf[$name]["php_files"] && file_exists($php_file_path))	{
					$tpl->assign("output3", _("PHP file included"));
					include_once($php_file_path);
				}

				/*
				 *  Rebuilds modules in oreon object
				 */
				$oreon->creatModuleList($pearDB);

			} else
				$tpl->assign("output4", _("Unable to install module"));
		} else {
			$form1->addElement('submit', 'install', _("Install Module"));
			$redirect = $form1->addElement('hidden', 'o');
			$redirect->setValue("i");
		}
		$form1->addElement('submit', 'list', _("Back"));
		$hid_name = $form1->addElement('hidden', 'name');
		$hid_name->setValue($name);
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$form1->accept($renderer);
		$tpl->assign('form1', $renderer->toArray());
	} else if ($id)	{

		/*
		 * "ID" case, it's an installed module
		 */

		$moduleinfo = getModuleInfoInDB(NULL, $id);
		$elemArr = array();
		if (is_dir($centreon_path . "www/modules/".$moduleinfo["name"]."/UPGRADE"))	{
			$handle = opendir($centreon_path . "www/modules/".$moduleinfo["name"]."/UPGRADE");
			$i = 0;
			$elemArr = array();
			while (false !== ($filename = readdir($handle)))	{
				if (substr($filename, 0, 1) != "." && strstr($filename, $moduleinfo["name"]."-"))	{
					include_once($centreon_path . "www/modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/conf.php");
					if ($moduleinfo["mod_release"] == $upgrade_conf[$moduleinfo["name"]]["release_from"])	{
						$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
						$upgrade_ok = false;
						# Upgrade
						if ($form->validate())	{
							# DB Upgrade
							$upgrade_ok = upgradeModuleInDB($id, $upgrade_conf[$moduleinfo["name"]]);
							if ($upgrade_ok)	{
								$tpl->assign("output1", _("Module installed and registered"));
								# SQL update if need
								$sql_file = "upgrade.sql";
								$sql_file_path = $centreon_path . "www/modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/sql/";
								if ($upgrade_conf[$moduleinfo["name"]]["sql_files"] && file_exists($sql_file_path.$sql_file))	{
									$tpl->assign("output2", _("SQL file included"));
									execute_sql_file($sql_file, $sql_file_path);
								}
								# PHP update if need
								$php_file = "upgrade.php";
								$php_file_path = $centreon_path . "www/modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/php/".$php_file;
								if ($upgrade_conf[$moduleinfo["name"]]["php_files"] && file_exists($php_file_path))	{
									$tpl->assign("output3", _("PHP file included"));
									include_once($php_file_path);
								}
								$oreon->creatModuleList($pearDB);
							}
							else
								$tpl->assign("output4", _("Unable to install module"));
						}
						if (!$upgrade_ok)	{
							$form->addElement('submit', 'upgrade', _("Upgrade"));
							$redirect = $form->addElement('hidden', 'o');
							$redirect->setValue("u");
						}

						if (is_dir($centreon_path . "www/modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/infos") && is_file("./modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/infos/infos.txt"))	{
							$infos_streams = file($centreon_path . "www/modules/".$moduleinfo["name"]."/UPGRADE/".$filename."/infos/infos.txt");
							$infos_streams = implode("<br />", $infos_streams);
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
							"upgrade_is_validUp" => $moduleinfo["mod_release"] === $upgrade_conf[$moduleinfo["name"]]["release_from"] ? _("Yes") : _("No"),
							"upgrade_choice" => $moduleinfo["mod_release"] === $upgrade_conf[$moduleinfo["name"]]["release_from"] ? true : false);
						$i++;
						$hid_id = $form->addElement('hidden', 'id');
						$hid_id->setValue($id);
						$up_name = $form->addElement('hidden', 'filename');
						$up_name->setValue($filename);
						$form->addElement('submit', 'list', _("Back"));
						$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
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
		$tpl->assign("module_isinstalled", _("Yes"));
		$tpl->assign("elemArr", $elemArr);
		$form2 = new HTML_QuickForm('Form', 'post', "?p=".$p);
		$form2->addElement('submit', 'list', _("Back"));
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$form2->accept($renderer);
		$tpl->assign('form2', $renderer->toArray());
	}

	$tpl->display("formModule.ihtml");
?>