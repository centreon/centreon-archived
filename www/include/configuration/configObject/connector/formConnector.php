<?php
/*
 * Copyright 2005-2011 MERETHIS
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


try
{
    $tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
    
    
    if (($o == "c" || $o == "w") && $connector_id)
    {
        $cnt = $connectorObj->read((int)$connector_id);
        $cnt['connector_name'] = $cnt['name'];
        $cnt['connector_description'] = $cnt['description'];
        $cnt['connector_status'] = $cnt['enabled'];
        $cnt['connector_id'] = $cnt['id'];
        
        unset($cnt['name']);
        unset($cnt['description']);
        unset($cnt['status']);
        unset($cnt['id']);
    }
    
    $form = new HTML_QuickForm('Form', 'post', "?p=".$p);
    
    
    if ($o == "a")
		$form->addElement('header', 'title', _("Add a Connector"));
	else if ($o == "c")
		$form->addElement('header', 'title', _("Modify a Connector"));
	else if ($o == "w")
		$form->addElement('header', 'title', _("View a Connector"));
    
    $attrsText 		= array("size"=>"35");
	$attrsTextarea 	= array("rows"=>"9", "cols"=>"65", "id"=>"command_line");

    
    $form->addElement('text', 'connector_name', _("Connector Name"), $attrsText);
    $form->addElement('text', 'connector_description', _("Connector Description"), $attrsText);
	$form->addElement('textarea', 'command_line', _("Command Line"), $attrsTextarea);
    
    $connectorStatus = array();
    $connectorStatus[] = HTML_QuickForm::createElement('radio', 'connector_status', null, _("Enabled"), '1');
    $connectorStatus[] = HTML_QuickForm::createElement('radio', 'connector_status', null, _("Disabled"), '0');
	$form->addGroup($connectorStatus, 'connector_status', _("Connector Status"), '&nbsp;&nbsp;');
    
    if (isset($cnt['connector_status']) && $cnt['connector_status'] != "")
		$form->setDefaults(array('connector_status' => $cnt['connector_status']));
	else
		$form->setDefaults(array('connector_status' => '0'));
    
    if ($o == "w")
    {
		if ($centreon->user->access->page($p) != 2)
			$form->addElement("button", "change", _("Modify"), array("onClick"=>"javascript:window.location.href='?p=".$p."&o=c&connector_id=".$connector_id."&status=".$status."'"));
	    $form->setDefaults($cmd);
		$form->freeze();
	}
    else if ($o == "c")
    {
		$subC = $form->addElement('submit', 'submitC', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	    $form->setDefaults($cnt);
	}
    else if ($o == "a")
    {
		$subA = $form->addElement('submit', 'submitA', _("Save"));
		$res = $form->addElement('reset', 'reset', _("Reset"));
	}
    
    
    $form->addRule('connector_name', _("Name"), 'required');
	$form->addRule('command_line', _("Command Line"), 'required');
	$form->setRequiredNote("<font style='color: red;'>*</font>&nbsp;". _("Required fields"));
    $form->addElement('hidden', 'connector_id');
    $redirect = $form->addElement('hidden', 'o');
	$redirect->setValue($o);
    
    $valid = false;
	if ($form->validate())
    {
		$cntObj = new CentreonConnector($pearDB);
        $tab = $form->getSubmitValues();
        $connectorValues = array();
        $connectorValues['name'] = $tab['connector_name'];
        $connectorValues['description'] = $tab['connector_description'];
        $connectorValues['command_line'] = $tab['command_line'];
        $connectorValues['enabled'] = (int)$tab['connector_status'];
        $connectorId = $tab['connector_id'];
        
        if ($form->getSubmitValue("submitA"))
            $connectorId = $cntObj->create($connectorValues, true);
        elseif ($form->getSubmitValue("submitC"))
            $cntObj->update((int)$connectorId, $connectorValues);
        
		$valid = true;
	}
    
    
	if ($valid)
		require_once($path."listConnector.php");
	else
    {
        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
        $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
        $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
        $form->accept($renderer);
        $tpl->assign('form', $renderer->toArray());
        $tpl->assign('o', $o);
        $tpl->display("formConnector.ihtml");
    }
}
catch(Exception $e)
{
    echo "Erreur n°".$e->getCode()." : ".$e->getMessage();
}

?>
