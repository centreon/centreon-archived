<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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
	$tpl->assign("headerMenu_dir", $lang["menu_listDir"]);
	$tpl->assign("headerMenu_gen", $lang["menu_listGen"]);
	$tpl->assign("headerMenu_lang", $lang["menu_listLang"]);
	$tpl->assign("headerMenu_sql", $lang["menu_listSql"]);
	# end header menu
	# Grab Modules
	$oreon->modules = array();
	$handle = opendir("./modules");	
	while (false !== ($filename = readdir($handle)))	{
		if ($filename != "." && $filename != "..")	{
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
						"RowMenu_link"=>"?p=".$p."&o=w&name=".$mod["name"],
						"RowMenu_dir"=>"./modules/".$mod["name"],
						"RowMenu_gen"=>$mod["gen"],
						"RowMenu_lang"=>$mod["lang"],
						"RowMenu_sql"=>$mod["sql"]);
		$style != "two" ? $style = "two" : $style = "one";
		$i++;	}
	$tpl->assign("elemArr", $elemArr);
	$tpl->assign("yes", $lang["yes"]);
	$tpl->assign("no", $lang["no"]);
	
	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$tpl->display("listMenu.ihtml");
	
	/*end menu*/
?>