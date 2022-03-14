<?php

/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

include_once "./class/centreonUtils.class.php";

include_once "./include/common/autoNumLimit.php";


// restoring the pagination if we stay on this menu
$num = 0;
if ($centreon->historyLastUrl === $url && isset($_GET['num'])) {
    $num = $_GET['num'];
}

try {
    $connectorsList = $connectorObj->getList(false, (int)$num, (int)$limit);

    $tpl = new Smarty();
    $tpl = initSmartyTpl($path, $tpl);

    $tpl->assign('mode_access', $lvl_access);

    $form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);

    $tpl->assign(
        'msg',
        array(
            "addL" => "main.php?p=" . $p . "&o=a",
            "addT" => _("Add"),
            "delConfirm" => _("Do you confirm the deletion ?")
        )
    );

    /*
     * Toolbar select
     */
    foreach (array('o1', 'o2') as $option) {
        $attrs1 = array(
            'onchange' => "javascript: " .
                " var bChecked = isChecked(); " .
                " if (this.form.elements['" . $option . "'].selectedIndex != 0 && !bChecked) {" .
                " alert('" . _("Please select one or more items") . "'); return false;} " .
                "if (this.form.elements['" . $option . "'].selectedIndex == 1 && confirm('"
                . _("Do you confirm the duplication ?") . "')) {" .
                "   setO(this.form.elements['" . $option . "'].value); submit();} " .
                "else if (this.form.elements['" . $option . "'].selectedIndex == 2 && confirm('"
                . _("Do you confirm the deletion ?") . "')) {" .
                "   setO(this.form.elements['" . $option . "'].value); submit();} " .
                "else if (this.form.elements['" . $option . "'].selectedIndex == 3) {" .
                "   setO(this.form.elements['" . $option . "'].value); submit();} " .
                "this.form.elements['" . $option . "'].selectedIndex = 0"
        );

        $form->addElement(
            'select',
            $option,
            null,
            array(
                null => _("More actions..."),
                "m" => _("Duplicate"),
                "d" => _("Delete")
            ),
            $attrs1
        );
        $form->setDefaults(array($option => null));
        $o1 = $form->getElement($option);
        $o1->setValue(null);
        $o1->setSelected(null);
    }

    $elemArr = array();
    $j = 0;
    $attrsText = array("size" => "2");
    $nbConnectors = count($connectorsList);
    $centreonToken = createCSRFToken();

    for ($i = 0; $i < $nbConnectors; $i++) {
        $result = $connectorsList[$i];
        $moptions = "";
        $MyOption = $form->addElement('text', "options[" . $result['id'] . "]", _("Options"), $attrsText);
        $form->setDefaults(array("options[" . $result['id'] . "]" => '1'));
        $selectedElements = $form->addElement('checkbox', "select[" . $result['id'] . "]");
        if ($result) {
            if ($lvl_access == "w") {
                if ($result['enabled']) {
                    $moptions = "<a href='main.php?p="
                        . $p . "&id=" . $result['id'] . "&o=u&limit=" . $limit . "&num=" . $num .
                        "&centreon_token=" . $centreonToken .
                        "'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='ico-14-disabled margin_right' viewBox='0 0 22 22' >
                                <path d='M0 0h24v24H0z' fill='none'/>
                                <path d='M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8 0-1.85.63-3.55 1.69-4.9L16.9 18.31C15.55 19.37 13.85 20 12 20zm6.31-3.1L7.1 5.69C8.45 4.63 10.15 4 12 4c4.42 0 8 3.58 8 8 0 1.85-.63 3.55-1.69 4.9z'/>
                            </svg>
                        </a>&nbsp;&nbsp;";
                } else {
                    $moptions = "<a href='main.php?p="
                        . $p . "&id=" . $result['id'] . "&o=s&limit=" . $limit . "&num=" . $num .
                        "&centreon_token=" . $centreonToken .
                        "'>
                            <svg xmlns='http://www.w3.org/2000/svg' class='ico-14-enabled margin_right' viewBox='0 0 24 24' >
                                <path d='M0 0h24v24H0z' fill='none'/>
                                <path d='M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z'/>
                            </svg>
                        </a>&nbsp;&nbsp;";
                }
                $moptions .= "&nbsp;"
                    . "<input onKeypress=\"if(event.keyCode > 31 "
                    . "&& (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false;"
                    . " if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\""
                    . " maxlength=\"3\" size=\"3\" value='1'"
                    . " style=\"margin-bottom:0px;\" name='options[" . $result['id'] . "]'></input>";
                $moptions .= "&nbsp;&nbsp;";
            } else {
                $moptions = "&nbsp;";
            }

            $elemArr[$j] = array(
                "RowMenu_select" => $selectedElements->toHtml(),
                "RowMenu_link" => "main.php?p=" . $p . "&o=c&id=" . $result['id'],
                "RowMenu_name" => CentreonUtils::escapeSecure($result["name"]),
                "RowMenu_description" => CentreonUtils::escapeSecure($result['description']),
                "RowMenu_command_line" => CentreonUtils::escapeSecure($result['command_line']),
                "RowMenu_enabled" => $result['enabled'] ? _("Enabled") : _("Disabled"),
                "RowMenu_badge" => $result['enabled'] ? "service_ok" : "service_critical",
                "RowMenu_options" => $moptions
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
} catch (Exception $e) {
    echo "Erreur n°" . $e->getCode() . " : " . $e->getMessage();
}
