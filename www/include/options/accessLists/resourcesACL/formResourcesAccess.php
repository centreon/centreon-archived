<?php
/*
 * Copyright 2005-2015 Centreon
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

if (!isset($centreon)) {
    exit();
}

/*
 * Database retrieve information for LCA
 */
if ($o === 'c' || $o === 'w') {
    /*
     * Set base value
     */
    $statement = $pearDB->prepare("SELECT * FROM acl_resources WHERE acl_res_id = :aclId LIMIT 1");
    $statement->bindValue(':aclId', $aclId, \PDO::PARAM_INT);
    $statement->execute();
    $acl = array_map("myDecode", $statement->fetch());

    /*
     * Set Poller relations
     */
    $statement = $pearDB->prepare("SELECT poller_id FROM acl_resources_poller_relations WHERE acl_res_id = :aclId");
    $statement->bindValue(':aclId', $aclId, \PDO::PARAM_INT);
    $statement->execute();
    while ($poller = $statement->fetch()) {
        $acl["acl_pollers"][] = $poller["poller_id"];
    }

    /*
     * Set Hosts relations
     */
    $statement = $pearDB->prepare("SELECT host_host_id FROM acl_resources_host_relations WHERE acl_res_id = :aclId");
    $statement->bindValue(':aclId', $aclId, \PDO::PARAM_INT);
    $statement->execute();
    while ($host = $statement->fetch()) {
        $acl["acl_hosts"][] = $host["host_host_id"];
    }

    /*
     * Set Hosts exludes relations
     */
    $statement = $pearDB->prepare("SELECT host_host_id FROM acl_resources_hostex_relations WHERE acl_res_id = :aclId");
    $statement->bindValue(':aclId', $aclId, \PDO::PARAM_INT);
    $statement->execute();
    while ($host = $statement->fetch()) {
        $acl["acl_hostexclude"][] = $host["host_host_id"];
    }

    /*
     * Set Hosts Groups relations
     */
    $statement = $pearDB->prepare("SELECT hg_hg_id FROM acl_resources_hg_relations WHERE acl_res_id = :aclId");
    $statement->bindValue(':aclId', $aclId, \PDO::PARAM_INT);
    $statement->execute();
    while ($hostgroup = $statement->fetch()) {
        $acl["acl_hostgroup"][] = $hostgroup["hg_hg_id"];
    }

    /*
     * Set Groups relations
     */
    $statement = $pearDB->prepare(
        "SELECT DISTINCT acl_group_id FROM acl_res_group_relations WHERE acl_res_id = :aclId"
    );
    $statement->bindValue(':aclId', $aclId, \PDO::PARAM_INT);
    $statement->execute();
    while ($group = $statement->fetch()) {
        $acl["acl_groups"][] = $group["acl_group_id"];
    }

    /*
     * Set Service Categories relations
     */
    $statement = $pearDB->prepare("SELECT DISTINCT sc_id FROM acl_resources_sc_relations WHERE acl_res_id = :aclId");
    $statement->bindValue(':aclId', $aclId, \PDO::PARAM_INT);
    $statement->execute();
    while ($sc = $statement->fetch()) {
        $acl["acl_sc"][] = $sc["sc_id"];
    }

    /*
     * Set Host Categories
     */
    $statement = $pearDB->prepare("SELECT DISTINCT hc_id FROM acl_resources_hc_relations WHERE acl_res_id = :aclId");
    $statement->bindValue(':aclId', $aclId, \PDO::PARAM_INT);
    $statement->execute();
    while ($hc = $statement->fetch()) {
        $acl["acl_hc"][] = $hc["hc_id"];
    }

    /*
     * Set Service Groups relations
     */
    $statement = $pearDB->prepare("SELECT DISTINCT sg_id FROM acl_resources_sg_relations WHERE acl_res_id = :aclId");
    $statement->bindValue(':aclId', $aclId, \PDO::PARAM_INT);
    $statement->execute();
    while ($sg = $statement->fetch()) {
        $acl["acl_sg"][] = $sg["sg_id"];
    }

    /*
     * Set Meta Services relations
     */
    $statement = $pearDB->prepare(
        "SELECT DISTINCT meta_id FROM acl_resources_meta_relations WHERE acl_res_id = :aclId"
    );
    $statement->bindValue(':aclId', $aclId, \PDO::PARAM_INT);
    $statement->execute();
    while ($ms = $statement->fetch()) {
        $acl["acl_meta"][] = $ms["meta_id"];
    }
}

$groups = [];
$DBRESULT = $pearDB->query("SELECT acl_group_id, acl_group_name FROM acl_groups ORDER BY acl_group_name");
while ($group = $DBRESULT->fetch()) {
    $groups[$group["acl_group_id"]] = CentreonUtils::escapeSecure(
        $group["acl_group_name"],
        CentreonUtils::ESCAPE_ALL
    );
}
$DBRESULT->closeCursor();

$pollers = [];
$DBRESULT = $pearDB->query("SELECT id, name FROM nagios_server ORDER BY name");
while ($poller = $DBRESULT->fetch()) {
    $pollers[$poller["id"]] = $poller["name"];
}
$DBRESULT->closeCursor();

$service_categories = [];
$pearDB->query("CREATE INDEX IF NOT EXISTS service_categories_index_query ON service_categories(sc_id, sc_name)");
$DBRESULT = $pearDB->query("SELECT sc_id, sc_name FROM service_categories ORDER BY sc_name");
while ($sc = $DBRESULT->fetchRow()) {
    $service_categories[$sc["sc_id"]] = $sc["sc_name"];
}
$DBRESULT->closeCursor();

$host_categories = [];
$DBRESULT = $pearDB->query("SELECT hc_id, hc_name FROM hostcategories ORDER BY hc_name");
while ($hc = $DBRESULT->fetchRow()) {
    $host_categories[$hc["hc_id"]] = $hc["hc_name"];
}
$DBRESULT->closeCursor();

$service_groups = [];
$DBRESULT = $pearDB->query("SELECT sg_id, sg_name FROM servicegroup ORDER BY sg_name");
while ($sg = $DBRESULT->fetchRow()) {
    $service_groups[$sg["sg_id"]] = $sg["sg_name"];
}
$DBRESULT->closeCursor();

$meta_services = [];
$DBRESULT = $pearDB->query("SELECT meta_id, meta_name FROM meta_service ORDER BY meta_name");
while ($ms = $DBRESULT->fetchRow()) {
    $meta_services[$ms["meta_id"]] = $ms["meta_name"];
}
$DBRESULT->closeCursor();

/*
 * Var information to format the element
 */
$attrsText = array("size" => "30");
$attrsText2 = array("size" => "60");
$attrsAdvSelect = array("style" => "width: 300px; height: 220px;");
$attrsTextarea = array("rows" => "3", "cols" => "80");
$eTemplate = '<table><tr><td><div class="ams">{label_2}</div>{unselected}</td><td align="center">{add}<br />' .
'<br /><br />{remove}</td><td><div class="ams">{label_3}</div>{selected}</td></tr></table>';

/*
 * Form begin
 */
$form = new HTML_QuickFormCustom('Form', 'POST', "?p=" . $p);
if ($o == "a") {
    $form->addElement('header', 'title', _("Add an ACL"));
} elseif ($o == "c") {
    $form->addElement('header', 'title', _("Modify an ACL"));
} elseif ($o == "w") {
    $form->addElement('header', 'title', _("View an ACL"));
}

/*
 * LCA basic information
 */
$form->addElement('header', 'information', _("General Information"));
$form->addElement('header', 'hostgroups', _("Hosts Groups Shared"));
$form->addElement('header', 'services', _("Filters"));
$form->addElement('text', 'acl_res_name', _("Access list name"), $attrsText);
$form->addElement('text', 'acl_res_alias', _("Description"), $attrsText2);

$tab = array();
$tab[] = $form->createElement('radio', 'acl_res_activate', null, _("Enabled"), '1');
$tab[] = $form->createElement('radio', 'acl_res_activate', null, _("Disabled"), '0');
$form->addGroup($tab, 'acl_res_activate', _("Status"), '&nbsp;');
$form->setDefaults(array('acl_res_activate' => '1'));

/*
 * All ressources
 */
$allHosts[] = $form->createElement(
    'checkbox',
    'all_hosts',
    '&nbsp;',
    "",
    array('id' => 'all_hosts', 'onClick' => 'toggleTableDeps(this)')
);
$form->addGroup($allHosts, 'all_hosts', _("Include all hosts"), '&nbsp;&nbsp;');

$allHostgroups[] = $form->createElement(
    'checkbox',
    'all_hostgroups',
    '&nbsp;',
    "",
    array('id' => 'all_hostgroups', 'onClick' => 'toggleTableDeps(this)')
);
$form->addGroup($allHostgroups, 'all_hostgroups', _("Include all hostgroups"), '&nbsp;&nbsp;');

$allServiceGroups[] = $form->createElement(
    'checkbox',
    'all_servicegroups',
    '&nbsp;',
    "",
    array('id' => 'all_servicegroups', 'onClick' => 'toggleTableDeps(this)')
);
$form->addGroup($allServiceGroups, 'all_servicegroups', _("Include all servicegroups"), '&nbsp;&nbsp;');

/*
 * Contact implied
 */
$form->addElement('header', 'contacts_infos', _("People linked to this Access list"));

$ams1 = $form->addElement(
    'advmultiselect',
    'acl_groups',
    array(_("Linked Groups"), _("Available"), _("Selected")),
    $groups,
    $attrsAdvSelect,
    SORT_ASC
);
$ams1->setButtonAttributes('add', array('value' => _("Add"), "class" => "btc bt_success"));
$ams1->setButtonAttributes('remove', array('value' => _("Remove"), "class" => "btc bt_danger"));
$ams1->setElementTemplate($eTemplate);
echo $ams1->getElementJs(false);

$form->addElement('header', 'Host_infos', _("Shared Resources"));
$form->addElement('header', 'help', _("Help"));
$form->addElement(
    'header',
    'HSharedExplain',
    _("<b><i>Help :</i></b> Select hosts and hostgroups that can be seen by associated users. " .
        "You also have the possibility to exclude host(s) from selected hostgroup(s).")
);
$form->addElement(
    'header',
    'SSharedExplain',
    _("<b><i>Help :</i></b> Select services that can be seen by associated users.")
);
$form->addElement(
    'header',
    'MSSharedExplain',
    _("<b><i>Help :</i></b> Select meta services that can be seen by associated users.")
);
$form->addElement(
    'header',
    'FilterExplain',
    _("<b><i>Help :</i></b> Select the filter(s) you want to apply to the " .
        "resource definition for a more restrictive view.")
);

/*
 * Pollers
 */
$ams0 = $form->addElement(
    'advmultiselect',
    'acl_pollers',
    array(_("Poller Filter"), _("Available"), _("Selected")),
    $pollers,
    $attrsAdvSelect,
    SORT_ASC
);
$ams0->setButtonAttributes('add', array('value' => _("Add"), "class" => "btc bt_success"));
$ams0->setButtonAttributes('remove', array('value' => _("Remove"), "class" => "btc bt_danger"));
$ams0->setElementTemplate($eTemplate);
echo $ams0->getElementJs(false);

/*
 * Hosts
 */
$hostRoute = './api/internal.php?object=centreon_configuration_host&action=list';
$attrHosts = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $hostRoute,
    'multiple' => true,
    'linkedObject' => 'centreonHost'
);
$attrsAdvSelect['id'] = 'hostAdvancedSelect';
$form->addElement(
    'select2',
    'acl_hosts',
    _("Hosts"),
    [],
    $attrHosts,
);

/*
 * Host Groups
 */
$hostgroupsRoute = './api/internal.php?object=centreon_configuration_hostgroup&action=list';
$attrHostgroups = array(
    'datasourceOrigin' => 'ajax',
    'availableDatasetRoute' => $hostgroupsRoute,
    'multiple' => true,
    'linkedObject' => 'centreonHostgroups'
);
$attrsAdvSelect['id'] = 'hostgroupAdvancedSelect';
$ams2 = $form->addElement(
    'select2',
    'acl_hostgroup',
    _("Host Groups"),
    [],
    $attrHostgroups,
);

unset($attrsAdvSelect['id']);


$form->addElement(
    'select2',
    'acl_hostexclude',
    _("Exclude hosts from selected host groups"),
    [],
    $attrHosts,
);


/*
 * Service Filters
 */
$ams2 = $form->addElement(
    'advmultiselect',
    'acl_sc',
    array(_("Service Category Filter"), _("Available"), _("Selected")),
    $service_categories,
    $attrsAdvSelect,
    SORT_ASC
);
$ams2->setButtonAttributes('add', array('value' => _("Add"), "class" => "btc bt_success"));
$ams2->setButtonAttributes('remove', array('value' => _("Remove"), "class" => "btc bt_danger"));
$ams2->setElementTemplate($eTemplate);
echo $ams2->getElementJs(false);

/*
 * Host Filters
 */
$ams2 = $form->addElement(
    'advmultiselect',
    'acl_hc',
    array(_("Host Category Filter"), _("Available"), _("Selected")),
    $host_categories,
    $attrsAdvSelect,
    SORT_ASC
);
$ams2->setButtonAttributes('add', array('value' => _("Add"), "class" => "btc bt_success"));
$ams2->setButtonAttributes('remove', array('value' => _("Remove"), "class" => "btc bt_danger"));
$ams2->setElementTemplate($eTemplate);
echo $ams2->getElementJs(false);

/*
 * Service Groups Add
 */
$attrsAdvSelect['id'] = 'servicegroupAdvancedSelect';
$ams2 = $form->addElement(
    'advmultiselect',
    'acl_sg',
    array(_("Service Groups"), _("Available"), _("Selected")),
    $service_groups,
    $attrsAdvSelect,
    SORT_ASC
);
$ams2->setButtonAttributes('add', array('value' => _("Add"), "class" => "btc bt_success"));
$ams2->setButtonAttributes('remove', array('value' => _("Remove"), "class" => "btc bt_danger"));
$ams2->setElementTemplate($eTemplate);
echo $ams2->getElementJs(false);
unset($attrsAdvSelect['id']);

/*
 * Meta Services
 */
$ams2 = $form->addElement(
    'advmultiselect',
    'acl_meta',
    array(
        _("Meta Services"),
        _("Available"),
        _("Selected")
    ),
    $meta_services,
    $attrsAdvSelect,
    SORT_ASC
);
$ams2->setButtonAttributes('add', array('value' => _("Add"), "class" => "btc bt_success"));
$ams2->setButtonAttributes('remove', array('value' => _("Remove"), "class" => "btc bt_danger"));
$ams2->setElementTemplate($eTemplate);
echo $ams2->getElementJs(false);

/*
 * Further informations
 */
$form->addElement('header', 'furtherInfos', _("Additional Information"));
$form->addElement('textarea', 'acl_res_comment', _("Comments"), $attrsTextarea);

$form->addElement('hidden', 'acl_res_id');

$redirect = $form->addElement('hidden', 'o');
$redirect->setValue($o);

/*
 * Form Rules
 */
$form->applyFilter('__ALL__', 'myTrim');
$form->addRule('acl_res_name', _("Required"), 'required');
$form->registerRule('exist', 'callback', 'testExistence');
if ($o == "a" || $o == "c") {
    $form->addRule('acl_res_name', _("Already exists"), 'exist');
}
$form->setRequiredNote(_("Required field"));

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl(__DIR__, $tpl);

$formDefaults = $acl ?? [];
$formDefaults['all_hosts[all_hosts]'] = $formDefaults['all_hosts'] ?? '0';
$formDefaults['all_hostgroups[all_hostgroups]'] = $formDefaults['all_hostgroups'] ?? '0';
$formDefaults['all_servicegroups[all_servicegroups]'] = $formDefaults['all_servicegroups'] ?? '0';

if ($o == "w") {
    /*
     * Just watch a LCA information
     */
    $form->addElement("button", "change", _("Modify"), array(
        "onClick" => "javascript:window.location.href='?p=" . $p . "&o=c&acl_id=" . $aclId . "'",
        "class" => "btc bt_success"
    ));
    $form->setDefaults($formDefaults);
    $form->freeze();
} elseif ($o == "c") {
    /*
     * Modify a LCA information
     */
    $subC = $form->addElement('submit', 'submitC', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Delete"), array("class" => "btc bt_danger"));
    $form->setDefaults($formDefaults);
} elseif ($o == "a") {
    /*
     *  Add a LCA information
     */
    $subA = $form->addElement('submit', 'submitA', _("Save"), array("class" => "btc bt_success"));
    $res = $form->addElement('reset', 'reset', _("Delete"), array("class" => "btc bt_danger"));
}
$tpl->assign('msg', array("changeL" => "main.php?p=" . $p . "&o=c&lca_id=" . $aclId, "changeT" => _("Modify")));

// prepare help texts
$helptext = "";
include_once("help.php");
foreach ($help as $key => $text) {
    $helptext .= '<span style="display:none" id="help:' . $key . '">' . $text . '</span>' . "\n";
}
$tpl->assign("helptext", $helptext);

$valid = false;
if ($form->validate()) {
    $aclObj = $form->getElement('acl_res_id');
    if ($form->getSubmitValue("submitA")) {
        $aclObj->setValue(insertLCAInDB());
    } elseif ($form->getSubmitValue("submitC")) {
        updateLCAInDB($aclObj->getValue());
    }
    require_once("listsResourcesAccess.php");
} else {
    $action = $form->getSubmitValue("action");
    if ($valid && $action["action"]) {
        require_once("listsResourcesAccess.php");
    } else {
        /*
         * Apply a template definition
         */
        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl, true);
        $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
        $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
        $form->accept($renderer);
        $tpl->assign('form', $renderer->toArray());
        $tpl->assign('o', $o);
        $tpl->assign("sort1", _("General Information"));
        $tpl->assign("sort2", _("Hosts Resources"));
        $tpl->assign("sort3", _("Services Resources"));
        $tpl->assign("sort4", _("Meta Services"));
        $tpl->assign("sort5", _("Filters"));
        $tpl->display("formResourcesAccess.ihtml");
    }
}
?>
<script type='text/javascript'>
    function toggleTableDeps(element) {
        jQuery(element).parents('td.FormRowValue:first').children('table').toggle(
            !jQuery(element).is(':checked')
        );
    }

    jQuery(() => {
        toggleTableDeps(jQuery('#all_hosts'));
        toggleTableDeps(jQuery('#all_hostgroups'));
        toggleTableDeps(jQuery('#all_servicegroups'));
    });
</script>
