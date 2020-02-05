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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

if (!isset($oreon)) {
    exit();
}

require_once _CENTREON_PATH_ . 'www/class/centreonLDAP.class.php';

$attrsText = array("size" => "80");
$attrsText2 = array("size" => "5");

/*
 * Form begin
 */
$form = new HTML_QuickFormCustom('Form', 'post', "?p=" . $p);
$form->addElement('header', 'title', _("LDAP Import"));

/*
 * Command information
 */
$form->addElement('header', 'options', _("LDAP Servers"));

$form->addElement('text', 'ldap_search_filter', _("Search Filter"), $attrsText);
$form->addElement('header', 'result', _("Search Result"));
$form->addElement('header', 'ldap_search_result_output', _("Result"));

$link = "LdapSearch()";
$form->addElement("button", "ldap_search_button", _("Search"), array("class" => "btc bt_success", "onClick" => $link));

$form->addElement('hidden', 'contact_id');
$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$tpl->assign(
    'ldap_search_filter_help',
    _("Active Directory :") . " (&(objectClass=user)(samaccounttype=805306368)(objectCategory=person)(cn=*))<br />" .
    _("Lotus Domino :") . " (&(objectClass=person)(cn=*))<br />" . _("OpenLDAP :") . " (&(objectClass=person)(cn=*))"
);
$tpl->assign('ldap_search_filter_help_title', _("Filter Examples"));
$tpl->assign(
    'javascript',
    '<script type="text/javascript" src="./include/common/javascript/ContactAjaxLDAP/ajaxLdapSearch.js"></script>'
);

$query = "SELECT ar.ar_id, ar_name, REPLACE(ari_value, '%s', '*') as filter " .
    "FROM auth_ressource ar " .
    "LEFT JOIN auth_ressource_info ari ON ari.ar_id = ar.ar_id " .
    "WHERE ari.ari_name = 'user_filter' AND ar.ar_enable = '1' " .
    "ORDER BY ar_name";
$res = $pearDB->query($query);
$ldapConfList = "";
while ($row = $res->fetch()) {
    if ($res->rowCount() == 1) {
        $ldapConfList .= "<input type='checkbox' name='ldapConf[" . $row['ar_id'] . "]'/ checked='true'> " .
            $row['ar_name'];
    } else {
        $ldapConfList .= "<input type='checkbox' name='ldapConf[" . $row['ar_id'] . "]'/> " . $row['ar_name'];
    }
    $ldapConfList .= "<br/>";
    $ldapConfList .= _('Filter') . ": <input size='80' type='text' value='" . $row['filter'] .
        "' name='ldap_search_filter[" . $row['ar_id'] . "]'/>";
    $ldapConfList .= "<br/><br/>";
}


/*
 * List available contacts to choose which one we want to import
 */
if ($o == "li") {
    $subA = $form->addElement('submit', 'submitA', _("Import"), array("class" => "btc bt_success"));
}

$valid = false;
if ($form->validate()) {
    if (isset($_POST["contact_select"]["select"]) && $form->getSubmitValue("submitA")) {
        // extracting the chosen contacts Id from the POST
        $selectedUsers = $_POST["contact_select"]['select'];
        unset($_POST["contact_select"]['select']);

        // removing the useless data sent
        $arrayToReturn = array();
        foreach ($_POST["contact_select"] as $key => $subKey) {
            $arrayToReturn[$key] = array_intersect_key($_POST["contact_select"][$key], $selectedUsers);
        }

        // restoring the filtered $_POST['contact_select']['select'] as it's needed in some DB-Func.php functions
        $arrayToReturn['select'] = $selectedUsers;
        $_POST['contact_select'] = $arrayToReturn;
        unset($selectedUsers);
        unset($arrayToReturn);

        insertLdapContactInDB($_POST["contact_select"]);
    }
    $form->freeze();
    $valid = true;
}

if ($valid) {
    require_once($path . "listContact.php");
} else {
    /*
     * Apply a template definition
     */
    $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
    $form->accept($renderer);
    $tpl->assign('ldapServers', _('Import from LDAP servers'));
    $tpl->assign('ldapConfList', $ldapConfList);
    $tpl->assign('form', $renderer->toArray());
    $tpl->assign('o', $o);
    $tpl->display("ldapImportContact.ihtml");
}
