<?php
/*
 * Copyright 2005-2012 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even 7the implied warranty of MERCHANTABILITY or FITNESS FOR A 
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

/**
 * Get toolbar action list
 * 
 * @param string $domName
 * @return array
 */
function getActionList($domName) {
    $tab = array(
                    'onchange'=>"javascript: " .
                    "if (this.form.elements['$domName'].selectedIndex == 1 && confirm('"._("Do you confirm the deletion ?")."')) {" .
                    " 	setA(this.form.elements['$domName'].value); submit();} " .
                    "else if (this.form.elements['$domName'].selectedIndex == 2) {" .
                    " 	setA(this.form.elements['$domName'].value); submit();} " .
                    "else if (this.form.elements['$domName'].selectedIndex == 3) {" .
                    " 	setA(this.form.elements['$domName'].value); submit();} " .
                    "this.form.elements['$domName'].selectedIndex = 0");
    return $tab;
}

include "./include/common/autoNumLimit.php";
$advanced_search = 0;
include_once "./include/common/quickSearch.php";

$labels = array('name'        => _('Name'),
                'description' => _('Description'),
                'status'      => _('Status'),
                'enabled'     => _('Enabled'),
                'disabled'    => _('Disabled'));

$ldapConf = new CentreonLdapAdmin($pearDB);
$searchLdap = "";
if (isset($search) && $search) {
    $searchLdap = $search;
}
$list = $ldapConf->getLdapConfigurationList($searchLdap);
$rows = count($list);

$enableLdap = 0;
foreach ($list as $k => $v) {
    if ($v['ar_enable']) {
        $enableLdap = 1;
    }
}
$pearDB->query("UPDATE options SET `value` = $enableLdap WHERE `key` = 'ldap_auth_enable'");

include "./include/common/checkPagination.php";
$list = $ldapConf->getLdapConfigurationList($searchLdap, ($num * $limit), $limit);
$tpl = initSmartyTpl($path.'ldap/', $tpl);

$form = new HTML_QuickForm('select_form', 'POST', "?o=ldap&p=".$p);

$tpl->assign('list', $list);
$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=ldap&new=1", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

$form->addElement('select', 'o1', NULL, array(NULL=>_("More actions..."), "d"=>_("Delete"), "ms"=>_("Enable"), "mu"=>_("Disable")), getActionList('o1'));
$form->setDefaults(array('o1' => NULL));

$form->addElement('select', 'o2', NULL, array(NULL => _("More actions..."), "d"=>_("Delete"), "ms"=>_("Enable"), "mu"=>_("Disable")), getActionList('o2'));
$form->setDefaults(array('o2' => NULL));

$o1 = $form->getElement('o1');
$o1->setValue(NULL);
$o1->setSelected(NULL);
$o2 = $form->getElement('o2');
$o2->setValue(NULL);
$o2->setSelected(NULL);
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('limit', $limit);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('labels', $labels);
$tpl->display("list.ihtml");
?>
<script type="text/javascript">
function setA(_i) {
    document.forms['form'].elements['a'].value = _i;
}
</script>