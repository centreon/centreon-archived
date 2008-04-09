<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@oreon-project.org
 */

	if (!isset($oreon))
		exit();

	include("./include/common/autoNumLimit.php");

	/*
	 * start quickSearch form
	 */
	$advanced_search = 0;
	include_once("./include/common/quickSearch.php");
	
	/*
	 * Access list activation
	 */
	$LCATool = "";
	if (!$is_admin)
		$LCATool = "host_id IN (".$lcaHoststr.") AND";
	
	/*
	 * Search active
	 */	
	$SearchTool = "";
	if (isset($search) && $search)
		$SearchTool = "(host_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' OR host_alias LIKE '%".htmlentities($search, ENT_QUOTES)."%') AND ";
		
	/*
	 * Launch Request
	 */	
	$DBRESULT =& $pearDB->query("SELECT COUNT(*) FROM host WHERE $SearchTool $LCATool host_register = '1'");
	
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";	
	$tmp =& $DBRESULT->fetchRow();
	$rows = $tmp["COUNT(*)"];
	
	include("./include/common/checkPagination.php");

	/*
	 * Smarty template Init
	 */
	 
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * start header menu
	 */
	 
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Name"));
	$tpl->assign("headerMenu_desc", _("Description"));
	$tpl->assign("headerMenu_address", _("IP Address / DNS"));
	$tpl->assign("headerMenu_poller", _("Poller"));
	$tpl->assign("headerMenu_parent", _("Parent Template"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	/*
	 * Host list
	 */
	 
	$DBRESULT =& $pearDB->query("SELECT ns.name, host_id, host_name, host_alias, host_address, host_activate, host_template_model_htm_id FROM host h, nagios_server ns , ns_host_relation nhr WHERE $SearchTool $LCATool host_register = '1' AND ns.id = nhr.nagios_server_id AND nhr.host_host_id = h.host_id ORDER BY host_name LIMIT ".$num * $limit.", ".$limit); 
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";	

	$search = tidySearchKey($search, $advanced_search);

	/*
	 * Init Formulary
	 */
	 
	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	
	/*
	 * Different style between each lines
	 */
	
	$style = "one";
	
	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	 
	$elemArr = array();
	for ($i = 0; $host =& $DBRESULT->fetchRow(); $i++) {

		$selectedElements =& $form->addElement('checkbox', "select[".$host['host_id']."]");	
		
		if ($host["host_activate"])
			$moptions = "<a href='oreon.php?p=".$p."&host_id=".$host['host_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		else
			$moptions = "<a href='oreon.php?p=".$p."&host_id=".$host['host_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
		
		$moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$host['host_id']."]'></input>";
		
		if (!$host["host_name"])
			$host["host_name"] = getMyHostField($host['host_id'], "host_name");
		
		/*
		 * TPL List
		 */
		 
		$tplArr = array();
		$tplStr = "";
		$tplArr = getMyHostTemplateModels($host["host_template_model_htm_id"]);
		
		/*
		 * Create Template topology
		 */
		
		if (count($tplArr))
			foreach($tplArr as $key =>$value)
				$tplStr .= "&nbsp;->&nbsp;<a href='oreon.php?p=60103&o=c&host_id=".$key."'>".$value."</a>";
		
		/*
		 * Create Array Data for template list
		 */
		
		$elemArr[$i] = array("MenuClass"=>"list_".$style, 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$host["host_name"],
						"RowMenu_link"=>"?p=".$p."&o=c&host_id=".$host['host_id'],
						"RowMenu_desc"=>$host["host_alias"],
						"RowMenu_address"=>$host["host_address"],
						"RowMenu_poller"=>$host["name"],
						"RowMenu_parent"=>$tplStr,
						"RowMenu_status"=>$host["host_activate"] ? _("Enabled") : _("Disabled"),
						"RowMenu_options"=>$moptions);
		$style != "two" ? $style = "two" : $style = "one";
	}
	
	/*
	 * Header title for same name - Ajust pattern lenght with (0, 4) param
	 */
	
	$pattern = NULL;
	for ($i = 0; $i < count($elemArr); $i++)	{
		# Searching for a pattern wich n+1 elem
		if (isset($elemArr[$i+1]["RowMenu_name"]) && strstr($elemArr[$i+1]["RowMenu_name"], substr($elemArr[$i]["RowMenu_name"], 0, 4)) && !$pattern)	{
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
			if (isset($elemArr[$i+1]["RowMenu_name"]) && strstr($elemArr[$i+1]["RowMenu_name"], substr($elemArr[$i]["RowMenu_name"], 0, 4)) && !$pattern)	{
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
	
	/*
	 * Different messages we put in the template
	 */
	
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
	
	/*
	 * Toolbar select $lang["lgd_more_actions"]
	 */
	?>
	<script type="text/javascript">
	function setO(_i) {
		document.forms['form'].elements['o'].value = _i;
	}
	</SCRIPT>
	<?php
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3 || this.form.elements['o1'].selectedIndex == 4 ||this.form.elements['o1'].selectedIndex == 5){" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
    $form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs1);
	
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3 || this.form.elements['o2'].selectedIndex == 4 ||this.form.elements['o2'].selectedIndex == 5){" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"this.form.elements['o2'].selectedIndex = 0");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable")), $attrs2);

	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	
	$tpl->assign('limit', $limit);

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);

	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listHost.ihtml");
?>