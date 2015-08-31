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