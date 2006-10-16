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

	$lcaHost = getLCAHostByID($pearDB);
	$lcaHostStr = getLCAHostStr($lcaHost["LcaHost"]);
	$lcaSGStr = getLCASGStr(getLCASG($pearDB));
	$lcaHGStr = getLCAHGStr($lcaHost["LcaHostGroup"]);
	$isRestreint = HadUserLca($pearDB);
	
	#
	## Database retrieve information for ServiceGroup
	#
	
	$sg = array();
	if (($o == "c" || $o == "w") && $sg_id)	{	
		if ($oreon->user->admin || !$isRestreint)		
			$res =& $pearDB->query("SELECT * FROM servicegroup WHERE sg_id = '".$sg_id."' LIMIT 1");
		else
			$res =& $pearDB->query("SELECT * FROM servicegroup WHERE sg_id = '".$sg_id."' AND sg_id IN (".$lcaSGStr.") LIMIT 1");
		if (PEAR::isError($res)) 
			print "Mysql Error : ".$res->getMessage();
		
		# Set base value
		$sg = array_map("myDecode", $res->fetchRow());
		
		# Set ServiceGroup Childs
		
		$res =& $pearDB->query(	"SELECT host_host_id, service_service_id " .
								"FROM servicegroup_relation " .
								"WHERE servicegroup_sg_id = '".$sg_id."' " .
								"AND host_host_id IS NOT NULL ORDER BY service_service_id");
		if (PEAR::isError($res))
			print "Mysql Error : ".$res->getMessage();
		$i = 0;
		while ($res->fetchInto($host)){
			$sg["sg_hServices"][$i] = $host["host_host_id"]."-".$host["service_service_id"];
			$i++;
		}	
		$res =& $pearDB->query(	"SELECT hostgroup_hg_id, service_service_id " .
								"FROM servicegroup_relation " .
								"WHERE servicegroup_sg_id = '".$sg_id."' " .
								"AND hostgroup_hg_id IS NOT NULL GROUP BY service_service_id");
		if (PEAR::isError($res))
			print "Mysql Error : ".$res->getMessage();
		for($i = 0; $res->fetchInto($services); $i++){
			print "hg";
			$sg["sg_hgServices"][$i] = $services["hostgroup_hg_id"]."-".$services["service_service_id"];
		}
		$res->free();

		# Set City name
		$res =& $pearDB->query("SELECT DISTINCT cny.country_id, cty.city_name FROM view_city cty, view_country cny WHERE cty.city_id = '".$sg["city_id"]."' AND cny.country_id = '".$sg["country_id"]."'");
		if (PEAR::isError($res)) 
			print "Mysql Error : ".$res->getMessage();
		$city = $res->fetchRow();
		$sg["city_name"] = $city["city_name"];
		$res->free();
	}
	#
	## Database retrieve information for differents elements list we need on the page
	#
	# Services comes from DB -> Store in $hServices Array and $hgServices
	$hServices = array();
	$hgServices = array();
	$initName = NULL;
	if ($oreon->user->admin || !$isRestreint)		
		$res =& $pearDB->query(	"SELECT host_name, host_id " .
								"FROM host " .
								"WHERE host_register = '1' " .
								"ORDER BY host_name");
	else
		$res =& $pearDB->query(	"SELECT host_name, host_id " .
								"FROM host " .
								"WHERE host_register = '1' " .
								"AND host_id IN (".$lcaHostStr.") " .
								"ORDER BY host_name");
		if (PEAR::isError($res))
			print "Mysql Error : ".$res->getMessage();
	while($res->fetchInto($host))	{
		$services = getMyHostServices($host["host_id"]);
		foreach ($services as $key => $s)
			$hServices[$host["host_id"]."-".$key] = $host["host_name"]."&nbsp;-&nbsp;".$s;
	}
	$res->free();

	# Host Group LCA
	$lcaHGStr ? $lcaHGStr = $lcaHGStr : $lcaHGStr =  '\'\'';
	if ($oreon->user->admin || !$isRestreint)		
		$res =& $pearDB->query(	"SELECT DISTINCT hg.hg_name, hg.hg_id, sv.service_description, sv.service_template_model_stm_id, sv.service_id " .
								"FROM host_service_relation hsr, service sv, hostgroup hg " .
								"WHERE sv.service_register = '1' " .
								"AND hsr.service_service_id = sv.service_id " .
								"AND hg.hg_id = hsr.hostgroup_hg_id " .
								"ORDER BY hg.hg_name, sv.service_description");
	else
		$res =& $pearDB->query(	"SELECT DISTINCT hg.hg_name, hg.hg_id, sv.service_description, sv.service_template_model_stm_id, sv.service_id " .
								"FROM host_service_relation hsr, service sv, hostgroup hg " .
								"WHERE sv.service_register = '1' " .
								"AND hsr.service_service_id = sv.service_id " .
								"AND hg.hg_id = hsr.hostgroup_hg_id " .
								"AND hg.hg_id IN (".$lcaHGStr.") " .
								"ORDER BY hg.hg_name, sv.service_description");
	
	if (PEAR::isError($res))
		print "Mysql Error : ".$res->getMessage();
	while($res->fetchInto($elem))	{
		# If the description of our Service is in the Template definition, we have to catch it, whatever the level of it :-)
		if (!$elem["service_description"])
			$elem["service_description"] = getMyServiceName($elem['service_template_model_stm_id']);
		$hgServices[$elem["hg_id"] . '-'.$elem["service_id"]] = $elem["hg_name"]."&nbsp;&nbsp;&nbsp;&nbsp;".$elem["service_description"];
	}
	$res->free();
	# Countries comes from DB -> Store in $countries Array
	$countries = array(NULL=>NULL);
	$res =& $pearDB->query("SELECT country_id, country_name FROM view_country ORDER BY country_name");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
	while($res->fetchInto($country))
		$countries[$country["country_id"]] = $country["country_name"];
	$res->free();
	#
	# End of "database-retrieved" information
	##########################################################
	##########################################################
	# Var information to format the element
	#
	$attrsText 		= array("size"=>"30");
	$attrsAdvSelect = array("style" => "width: 250px; height: 150px;");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
	$template 		= "<table><tr><td>{unselected}</td><td align='center'>{add}<br><br><br>{remove}</td><td>{selected}</td></tr></table>";

	#
	## Form begin
	#
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
	if ($o == "a")
		$form->addElement('header', 'title', $lang["sg_add"]);
	else if ($o == "c")
		$form->addElement('header', 'title', $lang["sg_change"]);
	else if ($o == "w")
		$form->addElement('header', 'title', $lang["sg_view"]);

	#
	## Contact basic information
	#
	$form->addElement('header', 'information', $lang['sg_infos']);
	$form->addElement('text', 'sg_name', $lang["sg_name"], $attrsText);
	$form->addElement('text', 'sg_alias', $lang["sg_alias"], $attrsText);
	$form->addElement('select', 'country_id', $lang['h_country'], $countries);
	$form->addElement('text', 'city_name', $lang['h_city'], array("id"=>"city_name", "size"=>"35", "autocomplete"=>"off"));
	
	##
	## Services Selection
	##
	$form->addElement('header', 'relation', $lang['sg_links']);
    $ams1 =& $form->addElement('advmultiselect', 'sg_hServices', $lang['sg_hostServiceMembers'], $hServices, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
	
	$form->addElement('header', 'relation', $lang['sg_links']);
    $ams1 =& $form->addElement('advmultiselect', 'sg_hgServices', $lang['sg_hostGroupServiceMembers'], $hgServices, $attrsAdvSelect);
	$ams1->setButtonAttributes('add', array('value' =>  $lang['add']));
	$ams1->setButtonAttributes('remove', array('value' => $lang['delete']));
	$ams1->setElementTemplate($template);
	echo $ams1->getElementJs(false);
		
	#
	## Further informations
	#
	$form->addElement('header', 'furtherInfos', $lang['further_infos']);
	$sgActivation[] = &HTML_QuickForm::createElement('radio', 'sg_activate', null, $lang["enable"], '1');
	$sgActivation[] = &HTML_QuickForm::createElement('radio', 'sg_activate', null, $lang["disable"], '0');
	$form->addGroup($sgActivation, 'sg_activate', $lang["status"], '&nbsp;');
	$form->setDefaults(array('sg_activate' => '1'));
	$form->addElement('textarea', 'sg_comment', $lang["comment"], $attrsTextarea);
	
	$tab = array();
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionList'], '1');
	$tab[] = &HTML_QuickForm::createElement('radio', 'action', null, $lang['actionForm'], '0');
	$form->addGroup($tab, 'action', $lang["action"], '&nbsp;');	
	$form->setDefaults(array('action' => '1'));
	
	$form->addElement('hidden', 'sg_id');
	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	
	#
	## Form Rules
	#
	function myReplace()	{
		global $form;
		$ret = $form->getSubmitValues();
		return (str_replace(" ", "_", $ret["sg_name"]));
	}
	$form->applyFilter('_ALL_', 'trim');
	$form->applyFilter('sg_name', 'myReplace');
	$form->addRule('sg_name', $lang['ErrName'], 'required');
	$form->addRule('sg_alias', $lang['ErrAlias'], 'required');
	$form->registerRule('exist', 'callback', 'testServiceGroupExistence');
	$form->addRule('sg_name', $lang['ErrAlreadyExist'], 'exist');
	$form->setRequiredNote($lang['requiredFields']);

	# 
	##End of form definition
	#
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	# Just watch a Service Group information
	if ($o == "w")	{
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&sg_id=".$sg_id."'"));
	    $form->setDefaults($sg);
		$form->freeze();
	}
	# Modify a Service Group information
	else if ($o == "c")	{
		$subC =& $form->addElement('submit', 'submitC', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	    $form->setDefaults($sg);
	}
	# Add a Service Group information
	else if ($o == "a")	{
		$subA =& $form->addElement('submit', 'submitA', $lang["save"]);
		$res =& $form->addElement('reset', 'reset', $lang["reset"]);
	}

	$tpl->assign('nagios', $oreon->user->get_version());

	
	$valid = false;
	if ($form->validate())	{
		$sgObj =& $form->getElement('sg_id');
		if ($form->getSubmitValue("submitA"))
			$sgObj->setValue(insertServiceGroupInDB());
		else if ($form->getSubmitValue("submitC"))
			updateServiceGroupInDB($sgObj->getValue());
		$o = "w";
		$form->addElement("button", "change", $lang['modify'], array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&sg_id=".$sgObj->getValue()."'"));
		$form->freeze();
		$valid = true;
	}
	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"])
		require_once($path."listServiceGroup.php");
	else	{
		#Apply a template definition
		$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);	
		$tpl->assign('form', $renderer->toArray());	
		$tpl->assign('o', $o);		
		$tpl->display("formServiceGroup.ihtml");
	}
?>