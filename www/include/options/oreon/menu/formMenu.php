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

	#
	## Database retrieve information for differents elements list we need on the page
	#
	#
	# End of "database-retrieved" information
	##########################################################
	
	
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
	
	if ($o == "w"  && $name)	{	
		if (is_file("./modules/".$name."/conf.php")) 
			include_once("./modules/".$name."/conf.php");
		
		# start header menu

		$tpl->assign("headerMenu_Title",$lang["menu_Module_Title"] );
		$tpl->assign("headerMenu_name", $lang["menu_Module_Name"]);
		$tpl->assign("headerMenu_version", $lang["menu_Module_Version"]);	
		$tpl->assign("headerMenu_author", $lang["menu_Module_Author"]);
		$tpl->assign("headerMenu_infos", $lang["menu_Module_additionnals_infos"]);

		# end header menu

		$tpl->assign("Menu_name", isset($module_conf[$name]["name"]) ? $module_conf[$name]["name"] : $name );	
		$tpl->assign("Menu_author",  htmlentities(isset($module_conf[$name]["author"]) ? $module_conf[$name]["author"] : "Oreon Team"), ENT_QUOTES);
		$tpl->assign("Menu_infos", isset($module_conf[$name]["info"]) ? $module_conf[$name]["info"] : "");		
	
	}
	
	
	
	#
	##Apply a template definition
	#
	$tpl->assign("o", $o);
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$tpl->display("formMenu.ihtml");
	
	
	

?>