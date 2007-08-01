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
		
	include("./include/common/autoNumLimit.php");

	# start quickSearch form
	$advanced_search = 1;
	include_once("./include/common/quickSearch.php");
	# end quickSearch form
	
	if ($type)
		$type_str = " command_type = '".$type."'";
	else
		$type_str = "";
		
	if (isset($search)){
		if ($type_str)
			$type_str = " AND " . $type_str;
		$req = "SELECT COUNT(*) FROM command WHERE command_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' $type_str";
	} else if ($type)
		$req = "SELECT COUNT(*) FROM command WHERE $type_str";
	else
		$req ="SELECT COUNT(*) FROM command";

	$DBRESULT =& $pearDB->query($req);
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";

	$tmp = & $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];

	include("./include/common/checkPagination.php");

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", $lang['name']);
	$tpl->assign("headerMenu_desc", $lang['description']);
	$tpl->assign("headerMenu_type", $lang['cmd_type']);
	$tpl->assign("headerMenu_status", $lang['status']);
	$tpl->assign("headerMenu_options", $lang['options']);
	# end header menu

	#List of elements - Depends on different criteria
	if ($search)
		$rq = "SELECT command_id, command_name, command_line, command_type FROM command WHERE command_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' $type_str ORDER BY command_name LIMIT ".$num * $limit.", ".$limit;
	else if ($type)
		$rq = "SELECT command_id, command_name, command_line, command_type FROM command WHERE command_type = '".$type."' ORDER BY command_name LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT command_id, command_name, command_line, command_type FROM command ORDER BY command_name LIMIT ".$num * $limit.", ".$limit;
	$search = tidySearchKey($search, $advanced_search);

	$DBRESULT =& $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	
	$form = new HTML_QuickForm('form', 'POST', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	for ($i = 0; $DBRESULT->fetchInto($cmd); $i++) {
		$selectedElements =& $form->addElement('checkbox', "select[".$cmd['command_id']."]");	
		$moptions = "<a href='oreon.php?p=".$p."&command_id=".$cmd['command_id']."&o=w&type=".$cmd['command_type']."&search=".$search."'><img src='img/icones/16x16/view.gif' border='0' alt='".$lang['view']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&command_id=".$cmd['command_id']."&o=c&type=".$cmd['command_type']."&search=".$search."'><img src='img/icones/16x16/document_edit.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&command_id=".$cmd['command_id']."&o=d&type=".$cmd['command_type']."&select[".$cmd['command_id']."]=1&num=".$num."&limit=".$limit."&search=".$search."' onclick=\"return confirm('".$lang['confirm_removing']."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='".$lang['delete']."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$cmd['command_id']."]'></input>";
		$cmd["command_line"] = str_replace('#BR#', "\\n", $cmd["command_line"]);
		$cmd["command_line"] = str_replace('#T#', "\\t", $cmd["command_line"]);
		$cmd["command_line"] = str_replace('#R#', "\\r", $cmd["command_line"]);
		$cmd["command_line"] = str_replace('#S#', "/", $cmd["command_line"]);
		$cmd["command_line"] = str_replace('#BS#', "\\", $cmd["command_line"]);
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$cmd["command_name"],
						"RowMenu_link"=>"?p=".$p."&o=c&command_id=".$cmd['command_id']."&type=".$cmd['command_type'],
						"RowMenu_desc"=>substr($cmd["command_line"], 0, 40)."...",
						"RowMenu_type"=>$cmd["command_type"] == 2 ? $lang['cmd_checkShort'] : $lang['cmd_notifShort'],
						"RowMenu_status"=>$lang['enable'],
						"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";
	}
	# Header title for same name - Ajust pattern lenght with (0, 6) param
	$pattern = NULL;
	for ($i = 0; $i < count($elemArr); $i++)	{
		# Searching for a pattern wich n+1 elem
		if (isset($elemArr[$i+1]["RowMenu_name"]) && strstr($elemArr[$i+1]["RowMenu_name"], substr($elemArr[$i]["RowMenu_name"], 0, 11)) && !$pattern)	{
			for ($j = 0; isset($elemArr[$i]["RowMenu_name"][$j]); $j++)	{
				if (isset($elemArr[$i+1]["RowMenu_name"][$j]) && $elemArr[$i+1]["RowMenu_name"][$j] == $elemArr[$i]["RowMenu_name"][$j])
					;
				else
					break;
			}
			$pattern = substr($elemArr[$i]["RowMenu_name"], 0, $j);
		}
		if (strstr($elemArr[$i]["RowMenu_name"], $pattern))
			$elemArr[$i]["pattern"] = $pattern;
		else	{
			$elemArr[$i]["pattern"] = NULL;
			$pattern = NULL;
			if (isset($elemArr[$i+1]["RowMenu_name"]) && strstr($elemArr[$i+1]["RowMenu_name"], substr($elemArr[$i]["RowMenu_name"], 0, 11)) && !$pattern)	{
				for ($j = 0; isset($elemArr[$i]["RowMenu_name"][$j]); $j++)	{
					if (isset($elemArr[$i+1]["RowMenu_name"][$j]) && $elemArr[$i+1]["RowMenu_name"][$j] == $elemArr[$i]["RowMenu_name"][$j])
						;
					else
						break;
				}
				$pattern = substr($elemArr[$i]["RowMenu_name"], 0, $j);
				$elemArr[$i]["pattern"] = $pattern;
			}
		}
	}
	$tpl->assign("elemArr", $elemArr);
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));

	$redirectType = $form->addElement('hidden', 'type');
	$redirectType->setValue($type);

	#
	##Toolbar select $lang["lgd_more_actions"]
	#
	?>
	<SCRIPT LANGUAGE="JavaScript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");
	$form->addElement('select', 'o1', NULL, array(NULL=>$lang["lgd_more_actions"], "m"=>$lang['dup'], "d"=>$lang['delete']/*, "mc"=>$lang['mchange']*/), $attrs1);
	$form->setDefaults(array('o1' => NULL));
		
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
    $form->addElement('select', 'o2', NULL, array(NULL=>$lang["lgd_more_actions"], "m"=>$lang['dup'], "d"=>$lang['delete']/*, "mc"=>$lang['mchange']*/), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);
	
	$tpl->assign('limit', $limit);

	#
	##Apply a template definition
	#
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listCommand.ihtml");
?>