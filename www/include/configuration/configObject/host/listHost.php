<?php
/*
 * Copyright 2005-2009 MERETHIS
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

	require_once ("./include/common/autoNumLimit.php");
	require_once ($centreon_path . "/www/class/centreonHost.class.php");

	$host_method = new CentreonHost($pearDB);

	/*
	 * Get Extended informations
	 */
	$ehiCache = array();
	$DBRESULT =& $pearDB->query("SELECT ehi_icon_image, host_host_id FROM extended_host_information");
	while ($ehi =& $DBRESULT->fetchRow()) {
		$ehiCache[$ehi["host_host_id"]] = $ehi["ehi_icon_image"];
	}
	$DBRESULT->free();

	if (isset($_POST["searchH"])) {
		$search = $_POST["searchH"];
		$_POST["search"] = $_POST["searchH"];
		$oreon->historySearch[$url] = $search;
	} else if (isset($oreon->historySearch[$url]))
		$search = $oreon->historySearch[$url];
	else
		$search = NULL;
	
	/*
	 * Get Poller -> used for poller section in host display list
	 */
	if (isset($_POST["poller"]))
		$poller = $_POST["poller"];
	else if (isset($_GET["poller"]))
		$poller = $_GET["poller"];
	else if (isset($oreon->poller) && $oreon->poller)
		$poller = $oreon->poller;
	else
		$poller = 0;

	if (isset($_POST["hostgroup"]))
	  $hostgroup = $_POST["hostgroup"];
	 else if (isset($_GET["hostgroup"]))
	   $hostgroup = $_GET["hostgroup"];
	 else if (isset($oreon->hostgroup) && $oreon->hostgroup)
	   $hostgroup = $oreon->hostgroup;
	 else
	   $hostgroup = 0;
	
	if (isset($_POST["template"]))
	  $template = $_POST["template"];
	 else if (isset($_GET["template"]))
	   $template = $_GET["template"];
	 else if (isset($oreon->template) && $oreon->template)
	   $template = $oreon->template;
	 else
	   $template = 0;
	
	/*
	 * set object history
	 */	
	$oreon->poller = $poller;
	$oreon->hostgroup = $hostgroup;
	$oreon->template = $template;
	
	/*
	 * Search active
	 */	
	$SearchTool = "";
	if (isset($search) && $search) {
		$search = str_replace('_', "\_", $search);
		$SearchTool = "(host_name LIKE '%".htmlentities($search, ENT_QUOTES)."%' OR host_alias LIKE '%".htmlentities($search, ENT_QUOTES)."%' OR host_address LIKE '%".htmlentities($search, ENT_QUOTES)."%') AND ";
	}
	
	if ($template) {
	  $templateFROM = ", host_template_relation htr ";
	  $templateWHERE = " htr.host_host_id = h.host_id AND htr.host_tpl_id = '$template' AND ";
	 } else {
	  $templateFROM = "";
	  $templateWHERE = "";
	}

	/*
	 * Launch Request
	 */	
	if ($hostgroup) {
	  if ($poller) 
	    $DBRESULT =& $pearDB->query("SELECT COUNT(*) FROM host h, ns_host_relation, hostgroup_relation hr $templateFROM WHERE $SearchTool $templateWHERE host_register = '1' AND host_id = ns_host_relation.host_host_id AND ns_host_relation.nagios_server_id = '$poller' AND h.host_id = hr.host_host_id AND hr.hostgroup_hg_id = '$hostgroup'");
	  else
	    $DBRESULT =& $pearDB->query("SELECT COUNT(*) FROM host h, hostgroup_relation hr $templateFROM WHERE $SearchTool $templateWHERE host_register = '1' AND h.host_id = hr.host_host_id AND hr.hostgroup_hg_id = '$hostgroup'");
	 } else {
	  if ($poller) 
	    $DBRESULT =& $pearDB->query("SELECT COUNT(*) FROM host h, ns_host_relation $templateFROM WHERE $SearchTool $templateWHERE host_register = '1' AND host_id = ns_host_relation.host_host_id AND ns_host_relation.nagios_server_id = '$poller'");
	  else
	    $DBRESULT =& $pearDB->query("SELECT COUNT(*) FROM host h $templateFROM WHERE $SearchTool $templateWHERE host_register = '1'");
	 }

	
	$tmp =& $DBRESULT->fetchRow();
	$DBRESULT->free();
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
	$tpl->assign("headerMenu_parent", _("Templates"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));
	
	/*
	 * Host list
	 */
	 
	$nagios_server = array();
	$DBRESULT =& $pearDB->query("SELECT ns.name, ns.id FROM nagios_server ns ORDER BY ns.name"); 
	while ($relation =& $DBRESULT->fetchRow()) {
		$nagios_server[$relation["id"]] = $relation["name"];
	}
	$DBRESULT->free();
	unset($relation);
	
	$tab_relation = array();
	$tab_relation_id = array();
	$DBRESULT =& $pearDB->query("SELECT nhr.host_host_id, nhr.nagios_server_id FROM ns_host_relation nhr"); 
	while ($relation =& $DBRESULT->fetchRow()) {
		$tab_relation[$relation["host_host_id"]] = $nagios_server[$relation["nagios_server_id"]];
		$tab_relation_id[$relation["host_host_id"]] = $relation["nagios_server_id"];
	}
	$DBRESULT->free();
	
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
	
	/*
	 * Select hosts 
	 */
	if ($hostgroup) {
	  if ($poller)
	    $DBRESULT =& $pearDB->query("SELECT host_id, host_name, host_alias, host_address, host_activate, host_template_model_htm_id FROM host h, ns_host_relation, hostgroup_relation hr $templateFROM WHERE $SearchTool $templateWHERE host_register = '1' AND host_id = ns_host_relation.host_host_id AND ns_host_relation.nagios_server_id = '$poller' AND h.host_id = hr.host_host_id AND hr.hostgroup_hg_id = '$hostgroup' ORDER BY host_name LIMIT ".$num * $limit.", ".$limit); 
	  else
	    $DBRESULT =& $pearDB->query("SELECT host_id, host_name, host_alias, host_address, host_activate, host_template_model_htm_id FROM host h, hostgroup_relation hr $templateFROM WHERE $SearchTool $templateWHERE host_register = '1' AND h.host_id = hr.host_host_id AND hr.hostgroup_hg_id = '$hostgroup' ORDER BY host_name LIMIT ".$num * $limit.", ".$limit); 
	 } else {
	  if ($poller)
	    $DBRESULT =& $pearDB->query("SELECT host_id, host_name, host_alias, host_address, host_activate, host_template_model_htm_id FROM host h, ns_host_relation $templateFROM WHERE $SearchTool $templateWHERE host_register = '1' AND host_id = ns_host_relation.host_host_id AND ns_host_relation.nagios_server_id = '$poller' ORDER BY host_name LIMIT ".$num * $limit.", ".$limit); 
	  else
	    $DBRESULT =& $pearDB->query("SELECT host_id, host_name, host_alias, host_address, host_activate, host_template_model_htm_id FROM host h $templateFROM WHERE $SearchTool $templateWHERE host_register = '1' ORDER BY host_name LIMIT ".$num * $limit.", ".$limit); 
	}

	$search = tidySearchKey($search, $advanced_search);
	 
	$elemArr = array();
	$search = str_replace('\_', "_", $search);
	for ($i = 0; $host =& $DBRESULT->fetchRow(); $i++) {
		if (!isset($poller) || $poller == 0 || ($poller != 0 && $poller == $tab_relation_id[$host["host_id"]])) {
			$selectedElements =& $form->addElement('checkbox', "select[".$host['host_id']."]");	
			
			if ($host["host_activate"])
				$moptions = "<a href='main.php?p=".$p."&host_id=".$host['host_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
			else
				$moptions = "<a href='main.php?p=".$p."&host_id=".$host['host_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
			
			$moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$host['host_id']."]'></input>";
			
			if (!$host["host_name"])
				$host["host_name"] = getMyHostField($host['host_id'], "host_name");
			
			/*
			 * TPL List
			 */
			 
			$tplArr = array();
			$tplStr = "";
					
			/*
			 * Create Template topology 
			 */		
			
			$tplArr = getMyHostMultipleTemplateModels($host['host_id']);
			if (count($tplArr)) { 
				$firstTpl = 1;
				foreach($tplArr as $key =>$value) {
					if ($firstTpl) {
						$tplStr .= "<a href='main.php?p=60103&o=c&host_id=".$key."'>".$value."</a>";
						$firstTpl = 0;
					} else
						$tplStr .= "&nbsp;|&nbsp;<a href='main.php?p=60103&o=c&host_id=".$key."'>".$value."</a>";
				}
			}
			
			/*
			 * Check icon
			 */
			if ((isset($ehiCache[$host["host_id"]]) && $ehiCache[$host["host_id"]])) {
				$host_icone = "./img/media/" . getImageFilePath($ehiCache[$host["host_id"]]);
			} else if ($icone = $host_method->replaceMacroInString($host["host_id"], getMyHostExtendedInfoImage($host["host_id"], "ehi_icon_image", 1))) {
				$host_icone = "./img/media/" . $icone;
			} else {
				$host_icone = "./img/icones/16x16/server_network.gif";
			}

			/*
			 * Create Array Data for template list
			 */
			
			$elemArr[$i] = array("MenuClass"=>"list_".$style, 
							"RowMenu_select"=>$selectedElements->toHtml(),
							"RowMenu_name"=>$host["host_name"],
							"RowMenu_icone"=> $host_icone,
							"RowMenu_link"=>"?p=".$p."&o=c&host_id=".$host['host_id'],
							"RowMenu_desc"=>$host["host_alias"],
							"RowMenu_address"=>htmlentities($host["host_address"]),
							"RowMenu_poller"=>htmlentities(isset($tab_relation[$host["host_id"]]) ? $tab_relation[$host["host_id"]] : ""),
							"RowMenu_parent"=>$tplStr,
							"RowMenu_status"=>$host["host_activate"] ? _("Enabled") : _("Disabled"),
							"RowMenu_options"=>$moptions);
			$style != "two" ? $style = "two" : $style = "one";
		}
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
	 * Toolbar select 
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

	$tpl->assign("search", $search);
	
	/*
	 * create Poller Select
	 */
	
	$options = "<option value='0'>"._("All Pollers")."</option>";
	foreach ($nagios_server as $key => $name)
		$options .= "<option value='$key' ".(($poller == $key) ? 'selected' : "").">$name</option>"; 
	 
	$tpl->assign("poller", $options);
	unset($options);


	$DBRESULT =& $pearDB->query("SELECT hg_id, hg_name FROM hostgroup ORDER BY hg_name");
	$options = "<option value='0'></options>";
	while ($data =& $DBRESULT->fetchRow()){
	  $options .= "<option value='".$data["hg_id"]."' ".(($hostgroup == $data["hg_id"]) ? 'selected' : "").">".$data["hg_name"]."</option>";
	 }
	
	$tpl->assign('hostgroup', $options);
	unset($options);
	
	$DBRESULT =& $pearDB->query("SELECT host_id, host_name FROM host WHERE host_register = '0' ORDER BY host_name");
	$options = "<option value='0'></options>";
	while ($data =& $DBRESULT->fetchRow()){
	  $options .= "<option value='".$data["host_id"]."' ".(($template == $data["host_id"]) ? 'selected' : "").">".$data["host_name"]."</option>";
	 }
	
	$tpl->assign('template', $options);
	unset($options);

	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('Hosts', _("Hosts"));
	$tpl->assign('Poller', _("Poller"));
	$tpl->assign('Hostgroup', _("Hostgroup"));
	$tpl->assign('Template', _("Template"));
	$tpl->assign('Search', _("Search"));
	$tpl->display("listHost.ihtml");
?>