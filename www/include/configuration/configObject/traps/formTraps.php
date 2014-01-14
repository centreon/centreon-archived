<?php
/*
 * Copyright 2005-2014 MERETHIS
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
 *
 */

	if (!isset($oreon))
		exit();

	/**
	 * Database retrieve information for Trap
	 */
        function testTrapExistence() {
            global $trapObj;

            return $trapObj->testTrapExistence();
        }

	function myDecodeTrap($arg)	{
		$arg = html_entity_decode($arg, ENT_QUOTES, "UTF-8");
		return($arg);
	}

	function myReplace()	{
		global $form;
		return (str_replace(" ", "_", $form->getSubmitValue("traps_name")));
	}

	$trap = array();
	$mnftr = array(NULL=>NULL);
	$mnftr_id = -1;
        $initialValues = array();
        $hServices = array();
	if (($o == "c" || $o == "w") && $traps_id) {
		$DBRESULT = $pearDB->query("SELECT * FROM traps WHERE traps_id = '".$traps_id."' LIMIT 1");
		# Set base value
		$trap = array_map("myDecodeTrap", $DBRESULT->fetchRow());
                $trap['severity'] = $trap['severity_id'];
		$DBRESULT->free();
                
                /**
                 * ACL
                 */
                if (!$centreon->user->admin) {
                    $aclSql = "SELECT hsr.host_host_id, hsr.service_service_id
                        FROM traps_service_relations tsr, $aclDbName.centreon_acl acl, host_service_relation hsr
                        WHERE tsr.traps_id = '".$trapsId."'
                        AND tsr.service_id = hsr.service_service_id
                        AND hsr.host_host_id = acl.host_id
                        AND acl.service_id = tsr.service_id
                        AND acl.group_id IN (".$acl->getAccessGroupsString().")";
                    $aclRes = $pearDB->query($aclSql);
                    $aclHs = array();
                    while ($aclRow = $aclRes->fetchRow()) {
                        $aclHs[$aclRow['host_host_id']."-".$aclRow['service_service_id']] = true;
                    }
                }
                $DBRESULT = $pearDB->query("SELECT tsr.service_id, hsr.host_host_id, h.host_name, s.service_description
                        FROM traps_service_relation tsr, host_service_relation hsr, host h, service s
                        WHERE h.host_id = hsr.host_host_id
                        AND hsr.service_service_id = s.service_id
                        AND s.service_register = '1'
                        AND hsr.service_service_id = tsr.service_id
                        AND tsr.traps_id = '".$pearDB->escape($traps_id)."'");
                for ($i = 0; $hs = $DBRESULT->fetchRow(); $i++) {
                    $hkey = $hs["host_host_id"]."-".$hs["service_id"];
                    if (isset($aclHs) && !isset($aclHs[$hkey])) {
                        $initialValues['services'][] = $hkey;
                    } else {
                        $hServices[$hkey] = $hs["host_name"]."&nbsp;-&nbsp;".$hs['service_description'];
                        $trap["services"][$i] = $hkey;
                    }
                }

                if ($centreon->user->admin) {
                    $res = $pearDB->query("SELECT s.service_id 
                                FROM traps_service_relation tsr, service s
                                WHERE tsr.service_id = s.service_id
                                AND s.service_register = '0'
                                AND tsr.traps_id = " . $pearDB->escape($traps_id ));
                    $trap['service_templates'] = array();
                    while ($row = $res->fetchRow()) {
                        $trap['service_templates'][] = $row['service_id'];
                    }
                }
                
                $cdata = CentreonData::getInstance();
                /*
                 * Preset values of preexec commands
                 */
                $preexecArray = $trapObj->getPreexecFromTrapId($traps_id);
                $cdata->addJsData('clone-values-preexec', htmlspecialchars(
                        json_encode($preexecArray), 
                        ENT_QUOTES
                    )
                );
                $cdata->addJsData('clone-count-preexec', count($preexecArray));
                
                /*
                 * Preset values of matching rules
                 */
                $mrulesArray = $trapObj->getMatchingRulesFromTrapId($traps_id);
                $cdata->addJsData('clone-values-matchingrules', htmlspecialchars(
                        json_encode($mrulesArray), 
                        ENT_QUOTES
                    )
                );
                $cdata->addJsData('clone-count-matchingrules', count($mrulesArray));
	}
	$DBRESULT = $pearDB->query("SELECT id, alias FROM traps_vendor ORDER BY alias");
	while ($rmnftr = $DBRESULT->fetchRow()){
            $mnftr[$rmnftr["id"]] = $rmnftr["alias"];
	}
	$DBRESULT->free();

	$attrsText 		= array("size"=>"50");
	$attrsLongText 	= array("size"=>"120");
	$attrsTextarea 	= array("rows"=>"10", "cols"=>"120");
        $attrsAdvSelect 	= array("style" => "width: 270px; height: 100px;");
        $eTemplate	= '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br /><br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

	/*
	 * Form begin
	 */
	$form = new HTML_QuickForm('Form', 'post', "?p=".$p);
        $trapObj->setForm($form);
	if ($o == "a") {
		$form->addElement('header', 'title', _("Add a Trap definition"));
	} else if ($o == "c") {
		$form->addElement('header', 'title', _("Modify a Trap definition"));
	} else if ($o == "w") {
		$form->addElement('header', 'title', _("View a Trap definition"));
	}

    /**
     * Initializes nbOfInitialRows
     */
    $query = "SELECT MAX(tmo_order) FROM traps_matching_properties WHERE trap_id = '".$traps_id."' ";
    $res = $pearDB->query($query);
    if ($res->numRows()) {
        $row = $res->fetchRow();
        $nbOfInitialRows = $row['MAX(tmo_order)'];
    } else {
        $nbOfInitialRows = 0;
    }

	/*
	 * Command information
	 */
	$form->addElement('text', 'traps_name', _("Trap name"), $attrsText);
	$form->addElement('select', 'manufacturer_id', _("Vendor Name"), $mnftr);
	$form->addElement('textarea', 'traps_comments', _("Comments"), $attrsTextarea);

	/**
	 * Generic fields
	 */
	$form->addElement('text', 'traps_oid', _("OID"), $attrsText);
	$form->addElement('select', 'traps_status', _("Default Status"), array(0=>_("Ok"), 1=>_("Warning"), 2=>_("Critical"), 3=>_("Unknown")), array('id' => 'trapStatus'));
	$severities = $severityObj->getList(null, "level", 'ASC', null, null, true);
        $severityArr = array(null=>null);
        foreach($severities as $severity_id => $severity) {
            $severityArr[$severity_id] = $severity['sc_name'].' ('.$severity['level'].')';
        }
        $form->addElement('select', 'severity', _("Default Severity"), $severityArr);
        $form->addElement('text', 'traps_args', _("Output Message"), $attrsText);
	$form->addElement('checkbox', 'traps_advanced_treatment', _("Advanced matching mode"), null, array('id' => 'traps_advanced_treatment'));
	$form->setDefaults(0);

	/* *******************************************************************
	 * Three possibilities : 	- submit result
	 * 							- execute a special command
	 * 							- resubmit a scheduling force
	 */

	/*
	 * submit result
	 */
	$cbt = $form->addElement('checkbox', 'traps_submit_result_enable', _("Submit result"));
	$form->setDefaults(array('traps_submit_result_enable' => '1'));

	/*
	 * Schedule svc check forced
	 */
	$form->addElement('checkbox', 'traps_reschedule_svc_enable', _("Reschedule associated services"));

	/*
	 * execute commande
	 */
	$form->addElement('text', 'traps_execution_command', _("Special Command"), $attrsLongText);
	$form->addElement('checkbox', 'traps_execution_command_enable', _("Execute special command"));

	/*
	 * Further informations
	 */
	$form->addElement('hidden', 'traps_id');
	$redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);

	$tab = array();
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("List"), '1');
	$tab[] = HTML_QuickForm::createElement('radio', 'action', null, _("Form"), '0');
	$form->addGroup($tab, 'action', _("Post Validation"), '&nbsp;');
	$form->setDefaults(array('action'=>'1'));

        /*
         * Service relations
         */
        $hostFilter = array(null => null,
                            0    => sprintf('__%s__', _('ALL')));
        $hostFilter = ($hostFilter + $acl->getHostAclConf(null,
                                                         $oreon->broker->getBroker(),
                                                         array('fields'  => array('host.host_id', 'host.host_name'),
                                                              'keys'    => array('host_id'),
                                                              'get_row' => 'host_name',
                                                              'order'   => array('host.host_name')),
                                                         true));
        $form->addElement('select', 'host_filter', _('Host'), $hostFilter, array('onChange' => 'hostFilterSelect(this, "services");'));
        $ams = $form->addElement('advmultiselect', 'services', array(_("Linked Services"), _("Available"), _("Selected")), $hServices, $attrsAdvSelect, SORT_ASC);
	$ams->setButtonAttributes('add', array('value' =>  _("Add")));
	$ams->setButtonAttributes('remove', array('value' => _("Remove")));
	$ams->setElementTemplate($eTemplate);
	echo $ams->getElementJs(false);
        
        if ($centreon->user->admin) {
            $svcObj = new CentreonService($pearDB);
            $ams = $form->addElement('advmultiselect', 'service_templates', array(_("Linked services templates"), _("Available"), _("Selected")), $svcObj->getServiceTemplateList(), $attrsAdvSelect, SORT_ASC);
            $ams->setButtonAttributes('add', array('value' =>  _("Add")));
            $ams->setButtonAttributes('remove', array('value' => _("Remove")));
            $ams->setElementTemplate($eTemplate);
            echo $ams->getElementJs(false);
        }
        
        /*
         * Routing 
         */
        $form->addElement(
                'text', 
                'traps_routing_value', 
                _('Route definition'), 
                $attrsLongText
                );
        $form->addElement('checkbox', 'traps_routing_mode', _("Enable routing"));
        
        /*
         * Matching rules
         */
        $cloneSetMaching = array();
        $cloneSetMaching[] = $form->addElement(
                'text', 
                'rule[#index#]', 
                _("String"), 
                array(
                    "size"=>"50", 
                    "id" => "rule_#index#",
                    "value" => "@OUTPUT@"
                    )
                );
        $cloneSetMaching[] = $form->addElement(
                'text', 
                'regexp[#index#]', 
                _("Regexp"), 
                array(
                    "size"=>"50", 
                    "id" => "regexp_#index#",
                    "value" => "//"
                    )
                );
        $cloneSetMaching[] = $form->addElement(
                'select', 
                'rulestatus[#index#]', 
                _("Status"), 
                array(
                  0 => _('OK'),
                  1 => _('Warning'),
                  2 => _('Critical'),
                  3 => _('Unknown')
                ),
                array(
                    "id" => "rulestatus_#index#",
                    "type" => "select-one"
                    )
                );
        $cloneSetMaching[] = $form->addElement(
                'select', 
                'ruleseverity[#index#]', 
                _("Severity"), 
                $severityArr,
                array(
                    "id" => "ruleseverity_#index#",
                    "type" => "select-one"
                    )
                );
        
        $form->addElement(
                'text', 
                'traps_timeout', 
                _("Timeout"),
                array('size' => 5)
                );
        
        $form->addElement(
                'text',
                'traps_exec_interval',
                _('Execution interval'),
                array('size' => 5)
                );
        
        $form->addElement(
                'checkbox',
                'traps_log',
                _('Enable log')
                );
        
        $form->addElement(
                'checkbox',
                'traps_advanced_treatment_default',
                _('Disable submit result if no matched rules')
                );
        
        $excecution_type[] = HTML_QuickForm::createElement('radio', 'traps_exec_interval_type', null, _("None"), '0');
	$excecution_type[] = HTML_QuickForm::createElement('radio', 'traps_exec_interval_type', null, _("By OID"), '1');
	$excecution_type[] = HTML_QuickForm::createElement('radio', 'traps_exec_interval_type', null, _("By OID and Host"), '2');
	$form->addGroup($excecution_type, 'traps_exec_interval_type', _("Execution type"), '&nbsp;');
        
        $excecution_method[] = HTML_QuickForm::createElement('radio', 'traps_exec_method', null, _("Parallel"), '0');
	$excecution_method[] = HTML_QuickForm::createElement('radio', 'traps_exec_method', null, _("Sequential"), '1');
	$form->addGroup($excecution_method, 'traps_exec_method', _("Execution method"), '&nbsp;');
        
        /*
         * Pre exec 
         */
        $cloneSet = array();
        $cloneSet[] = $form->addElement(
                'text', 
                'preexec[#index#]', 
                _("Preexec definition"), 
                array(
                    "size"=>"50", 
                    "id" => "preexec_#index#"
                    )
                );
        
	/*
	 * Form Rules
	 */
	$form->applyFilter('__ALL__', 'myTrim');
	$form->applyFilter('traps_name', 'myReplace');
	$form->addRule('traps_name', _("Compulsory Name"), 'required');
	$form->addRule('traps_oid', _("Compulsory Name"), 'required');
	$form->addRule('manufacturer_id', _("Compulsory Name"), 'required');
	$form->addRule('traps_args', _("Compulsory Name"), 'required');
	$form->registerRule('exist', 'callback', 'testTrapExistence');
	$form->addRule('traps_oid', _("The same OID element already exists"), 'exist');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	$tpl->assign('trap_adv_args', _("Advanced matching rules"));

	$tpl->assign("helpattr", 'TITLE, "'._("Help").'", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify"' );
	# prepare help texts
	$helptext = "";
	include_once("help.php");
	foreach ($help as $key => $text) {
		$helptext .= '<span style="display:none" id="help:'.$key.'">'.$text.'</span>'."\n";
	}
	$tpl->assign("helptext", $helptext);

	if ($o == "w")	{
		# Just watch a Command information
		if ($centreon->user->access->page($p) != 2)
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&traps_id=".$traps_id."'"));
	    $form->setDefaults($trap);
		$form->freeze();
	} else if ($o == "c")	{
		# Modify a Command information
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($trap);
	} else if ($o == "a")	{
		# Add a Command information
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	}

	$valid = false;
	if ($form->validate())	{
		$trapObj = new Centreon_Traps($centreon, $pearDB, $form);
                $trapParam = $form->getElement('traps_id');
		if ($form->getSubmitValue("submitA"))
			$trapParam->setValue($trapObj->insert());
		else if ($form->getSubmitValue("submitC"))
			$trapObj->update($trapParam->getValue());
		$o = NULL;
		$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&traps_id=".$trapParam->getValue()."'"));
		$form->freeze();
		$valid = true;
	}

	$action = $form->getSubmitValue("action");
	if ($valid && $action["action"]["action"]) {
		require_once($path."listTraps.php");
	} else {
		# Apply a template definition
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
		$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
		$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
		$form->accept($renderer);
		$tpl->assign('form', $renderer->toArray());
		$tpl->assign('o', $o);

                $tpl->assign('tabTitle_1', _('Main'));
                $tpl->assign('tabTitle_2', _('Relations'));
                $tpl->assign('tabTitle_3', _('Advanced'));
		$tpl->assign('subtitle0', _("Main information"));
		$tpl->assign('subtitle0', _("Convert Trap information"));
		$tpl->assign('subtitle1', _("Action 1 : Submit result to Monitoring Engine"));
		$tpl->assign('subtitle2', _("Action 2 : Force rescheduling of service check"));
		$tpl->assign('subtitle3', _("Action 3 : Execute a Command"));
		$tpl->assign('subtitle4', _("Trap description"));
                $tpl->assign('routingDefTxt', _('Route parameters'));
                $tpl->assign('resourceTxt', _('Resources'));
                $tpl->assign('preexecTxt', _('Pre execution commands'));
                $tpl->assign('serviceTxt', _('Linked services'));
                $tpl->assign('serviceTemplateTxt', _('Linked service templates'));
                $tpl->assign('admin', $centreon->user->admin);
                $tpl->assign('centreon_path', $centreon->optGen['oreon_path']);
                $tpl->assign('cloneSet', $cloneSet);
                $tpl->assign('cloneSetMaching', $cloneSetMaching);
                $tpl->assign('preexeccmd_str', _('SNMPTT PREEXEC command'));
		$tpl->display("formTraps.ihtml");
	}

    require_once $path . '/javascript/trapJs.php';
?>
