<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

include_once "./include/common/autoNumLimit.php";

// So what we get drunk; So what we don't sleep; We're just having Fun and we d'ont car who sees
try
{
    $connectorsList = $connectorObj->getList(false, (int)$num, (int)$limit);

    $tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

    /* Access level */
    ($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
    $tpl->assign('mode_access', $lvl_access);

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
