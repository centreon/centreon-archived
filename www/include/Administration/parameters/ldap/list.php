<?php
/*
 * Copyright 2005-2012 Centreon
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


include "./include/common/autoNumLimit.php";
require_once dirname(__FILE__) . "/listFunction.php";

$labels = array('name'        => _('Name'),
                'description' => _('Description'),
                'status'      => _('Status'),
                'enabled'     => _('Enabled'),
                'disabled'    => _('Disabled'));

$ldapConf = new CentreonLdapAdmin($pearDB);
$searchLdap = "";
if (isset($_POST['searchLdap']) && $_POST['searchLdap']) {
    $searchLdap = $_POST['searchLdap'];
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
$tpl->assign(
    'msg',
    array("addL"=>"?p=".$p."&o=ldap&new=1", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?"))
);

$form->addElement(
    'select',
    'o1',
    null,
    array(null=>_("More actions..."), "d"=>_("Delete"), "ms"=>_("Enable"), "mu"=>_("Disable")),
    getActionList('o1')
);
$form->setDefaults(array('o1' => null));

$form->addElement(
    'select',
    'o2',
    null,
    array(null => _("More actions..."), "d"=>_("Delete"), "ms"=>_("Enable"), "mu"=>_("Disable")),
    getActionList('o2')
);
$form->setDefaults(array('o2' => null));

$o1 = $form->getElement('o1');
$o1->setValue(null);
$o1->setSelected(null);
$o2 = $form->getElement('o2');
$o2->setValue(null);
$o2->setSelected(null);
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('limit', $limit);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('labels', $labels);
$tpl->assign('searchLdap', $searchLdap);
$tpl->assign('p', $p);
$tpl->display("list.ihtml");
?>
<script type="text/javascript">
function setA(_i) {
    document.forms['form'].elements['a'].value = _i;
}
</script>