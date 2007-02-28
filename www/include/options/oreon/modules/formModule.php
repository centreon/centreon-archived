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
	
	/*
	 * TESTER QUE C POSSIBLE DE L'INSTALLER => lien pourri...
	if (($o == "i" || $o == "d" ) && $name)	{	
		$o == "i" ? $sql_file = "install.sql" :$sql_file = "uninstall.sql";
		$sql_file_path = "./modules/".$name."/sql/" . $sql_file ;		
		if (file_exists($sql_file_path )) {		
			$file_sql = file($sql_file_path);
		            $str = NULL;
		            for ($i = 0; $i <= count($file_sql) - 1; $i++){
			            $line = $file_sql[$i];
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
		$tpl->assign("operation_success", $lang["menu_listAction_" . $o]);
	}
	*/
	$tpl->assign("headerMenu_title",$lang["mod_menu_modInfos"]);
	$tpl->assign("headerMenu_rname", $lang["mod_menu_module_rname"]);
	$tpl->assign("headerMenu_release", $lang["mod_menu_module_release"]);	
	$tpl->assign("headerMenu_author", $lang["mod_menu_module_author"]);
	$tpl->assign("headerMenu_infos", $lang["mod_menu_module_additionnals_infos"]);
	$tpl->assign("headerMenu_isinstalled", $lang["mod_menu_module_is_intalled"]);

	if ($name)	{	
		include_once("./modules/".$name."/conf.php");
		$tpl->assign("module_rname", $module_conf[$name]["rname"]);	
		$tpl->assign("module_release", $module_conf[$name]["release"]);
		$tpl->assign("module_author", $module_conf[$name]["author"]);
		$tpl->assign("module_infos", $module_conf[$name]["infos"]);
		$tpl->assign("module_isinstalled", $lang["no"]);
	}
	else if ($id)	{
		$moduleinfo = getModuleInfoInDB(NULL, $id);
		$tpl->assign("module_rname", $moduleinfo["rname"]);
		$tpl->assign("module_release", $moduleinfo["release"]);
		$tpl->assign("module_author", $moduleinfo["author"]);
		$tpl->assign("module_infos", $moduleinfo["infos"]);
		$tpl->assign("module_isinstalled", $lang["yes"]);
	}
	#
	##Apply a template definition
	#
	$tpl->display("formModule.ihtml");
?>