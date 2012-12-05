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

include_once "./include/common/autoNumLimit.php";
    
// So what we get drunk; So what we don't sleep; We're just having Fun and we d'ont car who sees
try
{
    $connectorsList = $connectorObj->getList(false, (int)$num, (int)$limit);
    
    $tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
    
    $form = new HTML_QuickForm('Form', 'post', "?p=".$p);
    
    $tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));
    
    /*
	 * Toolbar select 
	 */
	$attrs1 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o1'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"else if (this.form.elements['o1'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o1'].value); submit();} " .
				"this.form.elements['o1'].selectedIndex = 0");

	$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs1);
	$form->setDefaults(array('o1' => NULL));
		
	$attrs2 = array(
		'onchange'=>"javascript: " .
				"if (this.form.elements['o2'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"else if (this.form.elements['o2'].selectedIndex == 3) {" .
				" 	setO(this.form.elements['o2'].value); submit();} " .
				"this.form.elements['o2'].selectedIndex = 0");

    $form->addElement('select', 'o2', NULL, array(NULL=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")), $attrs2);
	$form->setDefaults(array('o2' => NULL));

	$o1 = $form->getElement('o1');
	$o1->setValue(NULL);
	$o1->setSelected(NULL);

	$o2 = $form->getElement('o2');
	$o2->setValue(NULL);
	$o2->setSelected(NULL);
    
    $elemArr = array();
    $j = 0;
    $attrsText = array("size"=>"2");
    $nbConnectors = count($connectorsList);
    for ($i = 0; $i < $nbConnectors; $i++)
    {
        $result = $connectorsList[$i];
        $moptions = "";
        $MyOption = $form->addElement('text', "options[".$result['id']."]", _("Options"), $attrsText);
        $form->setDefaults(array("options[".$result['id']."]" => '1'));
        $selectedElements = $form->addElement('checkbox', "select[".$result['id']."]");
        if ($result)
        {
            if ($result['enabled'])
            {
                $moptions = "<a href='main.php?p=".$p."&id=".$result['id']."&o=u&limit=".$limit."&num=".$num."'><img src='img/icones/16x16/element_previous.gif' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
                $result['enabled'] = "enabled";
            }
            else
            {
                $moptions = "<a href='main.php?p=".$p."&id=".$result['id']."&o=s&limit=".$limit."&num=".$num."'><img src='img/icones/16x16/element_next.gif' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
                $result['enabled'] = "disabled";
            }
            
			$moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='options[".$result['id']."]'></input>";
            $moptions .= "&nbsp;&nbsp;";
            //$moptions .= $MyOption->toHtml();
            
            //echo $moptions;
            
            $elemArr[$j] = array("RowMenu_select"         => $selectedElements->toHtml(),
                                 "RowMenu_link"           => "?p=".$p."&o=c&id=".$result['id'],
                                 "RowMenu_name"           => $result["name"],
                                 "RowMenu_description"    => $result['description'],
                                 "RowMenu_command_line"   => $result['command_line'],
                                 "RowMenu_enabled"        => $result['enabled'],
                                 "RowMenu_options"        => $moptions
                                );
        }
        $j++;
    }
    
    /**
     * @todo implement
     */
    $rows = $connectorObj->count(false);
    
    include_once "./include/common/checkPagination.php";
    
    $tpl->assign("elemArr", $elemArr);
    $tpl->assign('p', $p);
    $tpl->assign('connectorsList', $connectorsList);
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
    $tpl->assign('limit', $limit);
    $tpl->display("listConnector.ihtml");
}
 catch (Exception $e)
 {
     echo "Erreur n°".$e->getCode().
          " : ".$e->getMessage();
 }
 
?>
