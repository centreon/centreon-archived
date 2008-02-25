<?php
/**
Centreon is developped with GPL Licence 2.0 :
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
**/

	if (!isset($oreon))
		exit();
		
	include("./include/common/autoNumLimit.php");
	
	if (isset ($_GET["search"]))
		$search = $_GET["search"];
	else if (isset($oreon->historySearch[$url]))
		$search = $oreon->historySearch[$url];
	else 
		$search = NULL;
	
	if (isset($_GET["search_type_service"])){
		$search_type_service = $_GET["search_type_service"];
		$oreon->search_type_service = $_GET["search_type_service"];
	} else if (isset($oreon->search_type_service))
		 $search_type_service = $oreon->search_type_service;
	else 
		$search_type_service = NULL;
		
	if (isset($_GET["search_type_host"])){
		$search_type_host = $_GET["search_type_host"];
		$oreon->search_type_host = $_GET["search_type_host"];
	} else if (isset($oreon->search_type_host))
		 $search_type_host = $oreon->search_type_host;
	else 
		$search_type_host = NULL;
	
	
	if (!isset($search_type_service) && !isset($search_type_host)){
		$search_type_host = 1;
		$oreon->search_type_host = 1;
		$search_type_service = 1;
		$oreon->search_type_service = 1;
	}
	
	$rows = 0;
	$tmp = NULL;
	# Due to Description maybe in the Template definition, we have to search if the description could match for each service with a Template.
	if (isset($search))	{
		$search = str_replace('/', "#S#", $search);
		$search = str_replace('\\', "#BS#", $search);
		if ($search_type_service) {
			if ($oreon->user->admin || !$isRestreint)
				$DBRESULT =& $pearDB->query("SELECT service_id, service_description, service_template_model_stm_id FROM service sv, host_service_relation hsr WHERE sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL");
			else
				$DBRESULT =& $pearDB->query("SELECT service_id, service_description, service_template_model_stm_id FROM service sv, host_service_relation hsr WHERE sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL AND hsr.host_host_id IN (".$lcaHostStr.")");	
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			while ($DBRESULT->fetchInto($service))
				if (!$service["service_description"])	{
					$service["service_description"] = getMyServiceAlias($service['service_template_model_stm_id']);
					if (stristr($service["service_description"], htmlentities($search, ENT_QUOTES)))	{
						$rows++;
						$tmp ? $tmp .= ", ".$service["service_id"] : $tmp = $service["service_id"];
					}
				} else if (isset($search) && $search && stristr($service["service_description"], htmlentities($search, ENT_QUOTES)))	{
					$rows++;
					$tmp ? $tmp .= ", ".$service["service_id"] : $tmp = $service["service_id"];
				}
		}
		if ($search_type_host)	{
			if ($oreon->user->admin || !$isRestreint)
				$locale_query = "SELECT service_id, service_description, service_template_model_stm_id FROM service sv, host_service_relation hsr, host WHERE host_name like '%".$search."%' AND hsr.host_host_id=host.host_id AND sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL";
			else
				$locale_query = "SELECT service_id, service_description, service_template_model_stm_id FROM service sv, host_service_relation hsr, host WHERE host_name like '%".$search."%' AND hsr.host_host_id=host.host_id AND sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL AND hsr.host_host_id IN (".$lcaHostStr.")";				
			$DBRESULT =& $pearDB->query($locale_query);
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
			while ($DBRESULT->fetchInto($service)) {			         
				$tmp ? $tmp .= ", ".$service["service_id"] : $tmp = $service["service_id"];			          
				$rows++;				
			}
		}
    } else {
    	if ($oreon->user->admin || !$isRestreint)
			$DBRESULT =& $pearDB->query("SELECT service_description FROM service sv, host_service_relation hsr WHERE service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL");
		else
			$DBRESULT =& $pearDB->query("SELECT service_description FROM service sv, host_service_relation hsr WHERE service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.host_host_id IN (".$lcaHostStr.") AND hsr.hostgroup_hg_id IS NULL");
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$rows = $DBRESULT->numRows();
	}
	
	# start quickSearch form
	$advanced_search = 0;
	include_once("./include/common/quickSearch.php");
	# end quickSearch form

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	include("./include/common/checkPagination.php");

	$tpl->assign("search_type_service", $search_type_service);
	$tpl->assign("search_type_host", $search_type_host);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", _("Host"));
	$tpl->assign("headerMenu_desc", _("Service"));
	$tpl->assign("headerMenu_retry", _("Scheduling"));
	$tpl->assign("headerMenu_parent", _("Parent Template"));
	$tpl->assign("headerMenu_status", _("Status"));
	$tpl->assign("headerMenu_options", _("Options"));
	# end header menu
	#Host/service list
	$oreon->user->admin || !$isRestreint ? $strLCA = "" : $strLCA = "AND host.host_id IN (".$lcaHostStr.") "; 
	if ($search)
		$rq = "SELECT @nbr:=(SELECT COUNT(*) FROM host_service_relation WHERE service_service_id = sv.service_id GROUP BY service_id ) AS nbr, sv.service_id, sv.service_description, sv.service_activate, sv.service_template_model_stm_id, host.host_id, host.host_name, host.host_template_model_htm_id, sv.service_normal_check_interval, sv.service_retry_check_interval, sv.service_max_check_attempts FROM service sv, host, host_service_relation hsr WHERE sv.service_id IN (".($tmp ? $tmp : 'NULL').") AND sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND host.host_id = hsr.host_host_id $strLCA AND host.host_register = '1' ORDER BY host.host_name, service_description LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT @nbr:=(SELECT COUNT(*) FROM host_service_relation WHERE service_service_id = sv.service_id GROUP BY service_id ) AS nbr, sv.service_id, sv.service_description, sv.service_activate, sv.service_template_model_stm_id, host.host_id, host.host_name, host.host_template_model_htm_id, sv.service_normal_check_interval, sv.service_retry_check_interval, sv.service_max_check_attempts FROM service sv, host, host_service_relation hsr WHERE sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND host.host_id = hsr.host_host_id $strLCA AND host.host_register = '1' ORDER BY host.host_name, service_description LIMIT ".$num * $limit.", ".$limit;
	$DBRESULT =& $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	#Different style between each lines
	$style = "one";
	
	/*
	 * Fill a tab with a mutlidimensionnal Array we put in $tpl
	 */
	
	$elemArr = array();
	$fgHost = array("value"=>NULL, "print"=>NULL);
	
	$time_min = $oreon->Nagioscfg['interval_length'] / 60;
	
	for ($i = 0; $DBRESULT->fetchInto($service); $i++) {
		
		/*
		 * If the name of our Host is in the Template definition, we have to catch it, whatever the level of it :-)
		 */
		
		$fgHost["value"] != $service["host_name"] ? ($fgHost["print"] = true && $fgHost["value"] = $service["host_name"]) : $fgHost["print"] = false;
		$selectedElements =& $form->addElement('checkbox', "select[".$service['service_id']."]");	
		$moptions = "";
		if ($service["service_activate"])
			$moptions .= "<a href='oreon.php?p=".$p."&service_id=".$service['service_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='oreon.php?p=".$p."&service_id=".$service['service_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";

		$moptions .= "&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$service['service_id']."]'></input>";

		/*
		 * If the description of our Service is in the Template definition, we have to catch it, whatever the level of it :-)
		 */
		 
		if (!$service["service_description"])
			$service["service_description"] = getMyServiceAlias($service['service_template_model_stm_id']);
		else	{
			$service["service_description"] = str_replace('#S#', "/", $service["service_description"]);
			$service["service_description"] = str_replace('#BS#', "\\", $service["service_description"]);			
		}
		
		/* 
		 * TPL List 
		 */
		 
		$tplArr = array();
		$tplStr = NULL;
		$tplArr = getMyServiceTemplateModels($service["service_template_model_stm_id"]);
		if (count($tplArr))
			foreach($tplArr as $key =>$value){
				$value = str_replace('#S#', "/", $value);
				$value = str_replace('#BS#', "\\", $value);			
				$tplStr .= "&nbsp;->&nbsp;<a href='oreon.php?p=60206&o=c&service_id=".$key."'>".$value."</a>";
			}
		$normal_check_interval = getMyServiceField($service['service_id'], "service_normal_check_interval") / $time_min;
		$retry_check_interval  = getMyServiceField($service['service_id'], "service_retry_check_interval") / $time_min;
		
		$elemArr[$i] = array("MenuClass"=>"list_".($service["nbr"]>1 ? "three" : $style), 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$service["host_name"],
						"RowMenu_link"=>"?p=60101&o=c&host_id=".$service['host_id'],
						"RowMenu_link2"=>"?p=".$p."&o=c&service_id=".$service['service_id'],
						"RowMenu_parent"=>$tplStr,
						"RowMenu_retry"=> $normal_check_interval . " min / ".$retry_check_interval." min",
						"RowMenu_attempts"=>getMyServiceField($service['service_id'], "service_max_check_attempts"),
						"RowMenu_desc"=>$service["service_description"],
						"RowMenu_status"=>$service["service_activate"] ? _("Enabled") : _("Disabled"),
						"RowMenu_options"=>$moptions);
		$fgHost["print"] ? NULL : $elemArr[$i]["RowMenu_name"] = NULL;
		$style != "two" ? $style = "two" : $style = "one";	
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
	<SCRIPT LANGUAGE="JavaScript">
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
				"else if (this.form.elements['o1'].selectedIndex == 6 && confirm('"._("Are you sure you want to detach the service ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3 || this.form.elements['o1'].selectedIndex == 4 ||this.form.elements['o1'].selectedIndex == 5){" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable"), "dv"=>_("Detach")), $attrs1);
		
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 6 && confirm('"._("Are you sure you want to detach the service ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3 || this.form.elements['o2'].selectedIndex == 4 ||this.form.elements['o2'].selectedIndex == 5){" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");
    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete"), "mc"=>_("Massive Change"), "ms"=>_("Enable"), "mu"=>_("Disable"), "dv"=>_("Detach")), $attrs2);

	$o1 =& $form->getElement('o1');
	$o1->setValue(NULL);

	$o2 =& $form->getElement('o2');
	$o2->setValue(NULL);
	
	$tpl->assign('limit', $limit);

	/*
	 * Apply a template definition
	 */
	 
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listService.ihtml");
?>