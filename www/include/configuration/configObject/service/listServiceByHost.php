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

	$pagination = "maxViewConfiguration";
	
	# set limit
	$res =& $pearDB->query("SELECT maxViewConfiguration FROM general_opt LIMIT 1");
	if (PEAR::isError($res))
		print "Mysql Error : ".$res->getMessage();

	$gopt = array_map("myDecode", $res->fetchRow());		
	!isset ($_GET["limit"]) ? $limit = $gopt["maxViewConfiguration"] : $limit = $_GET["limit"];

	isset ($_GET["num"]) ? $num = $_GET["num"] : $num = 0;
	isset ($_GET["search"]) ? $search = $_GET["search"] : $search = NULL;
	isset ($_GET["search_type_service"]) ? $search_type_service = $_GET["search_type_service"] : $search_type_service = NULL;
	isset ($_GET["search_type_host"]) ? $search_type_host = $_GET["search_type_host"] : $search_type_host = NULL;

	$rows = 0;
	$tmp = NULL;
	# Due to Description maybe in the Template definition, we have to search if the description could match for each service with a Template.
	if ($search)	{
		if ($search_type_service) {
			if ($oreon->user->admin || !$isRestreint)
				$res = & $pearDB->query("SELECT service_id, service_description, service_template_model_stm_id FROM service sv, host_service_relation hsr WHERE sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL");
			else
				$res = & $pearDB->query("SELECT service_id, service_description, service_template_model_stm_id FROM service sv, host_service_relation hsr WHERE sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL AND hsr.host_host_id IN (".$lcaHostStr.")");	
			if (PEAR::isError($res))
				print "Mysql Error : ".$res->getMessage();
			while ($res->fetchInto($service))
				if (!$service["service_description"])	{
					$service["service_description"] = getMyServiceName($service['service_template_model_stm_id']);
					if (stristr($service["service_description"], htmlentities($search, ENT_QUOTES)))	{
						$rows++;
						$tmp ? $tmp .= ", ".$service["service_id"] : $tmp = $service["service_id"];
					}
				} else if (stristr($service["service_description"], htmlentities($search, ENT_QUOTES)))	{
					$rows++;
					$tmp ? $tmp .= ", ".$service["service_id"] : $tmp = $service["service_id"];
				}
		}
		if ($search_type_host)	{
			if ($oreon->user->admin || !$isRestreint)
				$locale_query = "SELECT service_id, service_description, service_template_model_stm_id FROM service sv, host_service_relation hsr, host WHERE host_name like '%".$search."%' AND hsr.host_host_id=host.host_id AND sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL";
			else
				$locale_query = "SELECT service_id, service_description, service_template_model_stm_id FROM service sv, host_service_relation hsr, host WHERE host_name like '%".$search."%' AND hsr.host_host_id=host.host_id AND sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL AND hsr.host_host_id IN (".$lcaHostStr.")";				
			$res = & $pearDB->query($locale_query);
			if (PEAR::isError($res))
				print "Mysql Error : ".$res->getMessage();
			while ($res->fetchInto($service)) {			         
				$tmp ? $tmp .= ", ".$service["service_id"] : $tmp = $service["service_id"];			          
				$rows++;				
			}
		}
    } else {
    	if ($oreon->user->admin || !$isRestreint)
			$res =& $pearDB->query("SELECT service_description FROM service sv, host_service_relation hsr WHERE service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.hostgroup_hg_id IS NULL");
		else
			$res =& $pearDB->query("SELECT service_description FROM service sv, host_service_relation hsr WHERE service_register = '1' AND hsr.service_service_id = sv.service_id AND hsr.host_host_id IN (".$lcaHostStr.") AND hsr.hostgroup_hg_id IS NULL");
		if (PEAR::isError($res))
			print "Mysql Error : ".$res->getMessage();
		$rows = $res->numRows();
	}
	
	# start quickSearch form
	include_once("./include/common/quickSearch.php");
	# end quickSearch form

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# start header menu
	$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
	$tpl->assign("headerMenu_name", $lang['h']);
	$tpl->assign("headerMenu_desc", $lang['sv']);
	$tpl->assign("headerMenu_parent", $lang['sv_parent']);
	$tpl->assign("headerMenu_status", $lang['status']);
	$tpl->assign("headerMenu_options", $lang['options']);
	# end header menu
	#Host/service list
	$oreon->user->admin || !$isRestreint ? $strLCA = "" : $strLCA = "AND host.host_id IN (".$lcaHostStr.") "; 
	if ($search)
		$rq = "SELECT @nbr:=(SELECT COUNT(*) FROM host_service_relation WHERE service_service_id = sv.service_id GROUP BY service_id) AS nbr, sv.service_id, sv.service_description, sv.service_activate, sv.service_template_model_stm_id, host.host_id, host.host_name, host.host_template_model_htm_id FROM service sv, host, host_service_relation hsr WHERE sv.service_id IN (".($tmp ? $tmp : 'NULL').") AND sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND host.host_id = hsr.host_host_id $strLCA AND host.host_register = '1' ORDER BY host.host_name, service_description LIMIT ".$num * $limit.", ".$limit;
	else
		$rq = "SELECT @nbr:=(SELECT COUNT(*) FROM host_service_relation WHERE service_service_id = sv.service_id GROUP BY service_id) AS nbr, sv.service_id, sv.service_description, sv.service_activate, sv.service_template_model_stm_id, host.host_id, host.host_name, host.host_template_model_htm_id FROM service sv, host, host_service_relation hsr WHERE sv.service_register = '1' AND hsr.service_service_id = sv.service_id AND host.host_id = hsr.host_host_id $strLCA AND host.host_register = '1' ORDER BY host.host_name, service_description LIMIT ".$num * $limit.", ".$limit;
	$res = & $pearDB->query($rq);
	if (PEAR::isError($res))
		print "Mysql Error : ".$res->getMessage();
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
	#Different style between each lines
	$style = "one";
	#Fill a tab with a mutlidimensionnal Array we put in $tpl
	$elemArr = array();
	$fgHost = array("value"=>NULL, "print"=>NULL);
	for ($i = 0; $res->fetchInto($service); $i++) {
		# If the name of our Host is in the Template definition, we have to catch it, whatever the level of it :-)
		if (!$service["host_name"])
			$service["host_name"] = getMyHostName($service['host_template_model_htm_id']);
		$fgHost["value"] != $service["host_name"] ? ($fgHost["print"] = true && $fgHost["value"] = $service["host_name"]) : $fgHost["print"] = false;
		$selectedElements =& $form->addElement('checkbox', "select[".$service['service_id']."]");	
		$moptions = "<a href='oreon.php?p=".$p."&service_id=".$service['service_id']."&o=w&search=".$search."'><img src='img/icones/16x16/view.gif' border='0' alt='".$lang['view']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&service_id=".$service['service_id']."&o=c&search=".$search."'><img src='img/icones/16x16/document_edit.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
		$moptions .= "<a href='oreon.php?p=".$p."&service_id=".$service['service_id']."&o=d&select[".$service['service_id']."]=1&num=".$num."&limit=".$limit."&search=".$search."' onclick=\"return confirm('".$lang['confirm_removing']."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='".$lang['delete']."'></a>&nbsp;&nbsp;";
		if ($service["service_activate"])
			$moptions .= "<a href='oreon.php?p=".$p."&service_id=".$service['service_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='".$lang['disable']."'></a>&nbsp;&nbsp;";
		else
			$moptions .= "<a href='oreon.php?p=".$p."&service_id=".$service['service_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icones/16x16/element_next.gif' border='0' alt='".$lang['enable']."'></a>&nbsp;&nbsp;";
		$moptions .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		$moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$service['service_id']."]'></input>";
		# If the description of our Service is in the Template definition, we have to catch it, whatever the level of it :-)
		if (!$service["service_description"])
			$service["service_description"] = getMyServiceName($service['service_template_model_stm_id']);
		$elemArr[$i] = array("MenuClass"=>"list_".($service["nbr"]>1 ? "three" : $style), 
						"RowMenu_select"=>$selectedElements->toHtml(),
						"RowMenu_name"=>$service["host_name"],
						"RowMenu_link"=>"?p=60101&o=w&host_id=".$service['host_id'],
						"RowMenu_link2"=>"?p=".$p."&o=w&service_id=".$service['service_id'],
						"RowMenu_parent"=>$service["service_template_model_stm_id"] ? $lang["yes"] : $lang["no"],
						"RowMenu_desc"=>$service["service_description"],
						"RowMenu_status"=>$service["service_activate"] ? $lang['enable'] : $lang['disable'],
						"RowMenu_options"=>$moptions);
		$fgHost["print"] ? NULL : $elemArr[$i]["RowMenu_name"] = NULL;
		$style != "two" ? $style = "two" : $style = "one";	}
	$tpl->assign("elemArr", $elemArr);
	#Different messages we put in the template
	$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));


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
	$attrs = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"");	  
        $form->addElement('select', 'o1', NULL, array(NULL=>$lang["lgd_more_actions"], "m"=>$lang['dup'], "d"=>$lang['delete']/*, "mc"=>$lang['mchange']*/), $attrs);
	$form->setDefaults(array('o1' => NULL));
			$o1 =& $form->getElement('o1');
		$o1->setValue(NULL);
	
	$attrs = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('".$lang['confirm_duplication']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('".$lang['confirm_removing']."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"");
    $form->addElement('select', 'o2', NULL, array(NULL=>$lang["lgd_more_actions"], "m"=>$lang['dup'], "d"=>$lang['delete']/*, "mc"=>$lang['mchange']*/), $attrs);
	$form->setDefaults(array('o2' => NULL));

		$o2 =& $form->getElement('o2');
		$o2->setValue(NULL);
	
	$tpl->assign('limit', $limit);



	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("listService.ihtml");

?>