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

$calcType = array("AVE" => _("Average"), "SOM" => _("Sum"), "MIN" => _("Min"), "MAX" => _("Max"));

if (!isset($oreon)) {
    exit();
}

include_once("./class/centreonUtils.class.php");

include("./include/common/autoNumLimit.php");

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

/* Access level */
($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
$tpl->assign('mode_access', $lvl_access);

require_once("./class/centreonDB.class.php");
$pearDBO = new CentreonDB("centstorage");

$DBRESULT = $pearDB->prepare('SELECT * FROM meta_service WHERE meta_id = :meta_id');
$DBRESULT->bindValue(':meta_id', $meta_id, PDO::PARAM_INT);
$DBRESULT->execute();

$meta = $DBRESULT->fetchRow();
$tpl->assign("meta", array(
    "meta" => _("Meta Service"),
    "name" => $meta["meta_name"],
    "calc_type" => $calcType[$meta["calcul_type"]]
));
$DBRESULT->closeCursor();

/*
 * start header menu
 */
$tpl->assign("headerMenu_host", _("Host"));
$tpl->assign("headerMenu_service", _("Services"));
$tpl->assign("headerMenu_metric", _("Metrics"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));

$aclFrom = "";
$aclCond = "";
if (!$oreon->user->admin) {
    $aclFrom = ", $aclDbName.centreon_acl acl ";
    $aclCond = " AND acl.host_id = msr.host_id
                 AND acl.group_id IN (" . $acl->getAccessGroupsString() . ") ";
}

$statement = $pearDB->prepare(
    "SELECT DISTINCT msr.*
    FROM `meta_service_relation` msr $aclFrom
    WHERE msr.meta_id = :meta_id
    $aclCond
    ORDER BY host_id"
);
$statement->bindValue(':meta_id', $meta_id, PDO::PARAM_INT);
$statement->execute();

$ar_relations = array();

$form = new HTML_QuickFormCustom('Form', 'POST', "?p=" . $p);

/*
* Construct request
*/

$metrics = array();

while ($row = $statement->fetchRow()) {
    $ar_relations[$row['metric_id']][] = array("activate" => $row['activate'], "msr_id" => $row['msr_id']);
    $metrics[] = $row['metric_id'];
}
$in_statement = implode(",", $metrics);

if ($in_statement != "") {
    $query = "SELECT * FROM metrics m, index_data i " .
        "WHERE m.metric_id IN ($in_statement) " .
        "AND m.index_id=i.id ORDER BY i.host_name, i.service_description, m.metric_name";
    $DBRESULTO = $pearDBO->query($query);
    /*
     * Different style between each lines
     */
    $style = "one";
    /*
     * Fill a tab with a mutlidimensionnal Array we put in $tpl
     */
    $elemArr1 = array();
    $i = 0;
    while ($metric = $DBRESULTO->fetchRow()) {
        foreach ($ar_relations[$metric['metric_id']] as $relation) {
            $moptions = "";
            $selectedElements = $form->addElement('checkbox', "select[" . $relation['msr_id'] . "]");
            if ($relation["activate"]) {
                $moptions .= "<a href='main.php?p=" . $p . "&msr_id=" . $relation['msr_id'] .
                    "&o=us&meta_id=" . $meta_id . "&metric_id=" . $metric['metric_id'] .
                    "'><img src='img/icons/disabled.png' class='ico-14 margin_right' border='0' alt='" .
                    _("Disabled") . "'></a>&nbsp;&nbsp;";
            } else {
                $moptions .= "<a href='main.php?p=" . $p . "&msr_id=" . $relation['msr_id'] .
                    "&o=ss&meta_id=" . $meta_id . "&metric_id=" . $metric['metric_id'] .
                    "'><img src='img/icons/enabled.png' class='ico-14 margin_right' border='0' alt='" .
                    _("Enabled") . "'></a>&nbsp;&nbsp;";
            }
            $metric["service_description"] = str_replace("#S#", "/", $metric["service_description"]);
            $metric["service_description"] = str_replace("#BS#", "\\", $metric["service_description"]);
            $elemArr1[$i] = array(
                "MenuClass" => "list_" . $style,
                "RowMenu_select" => $selectedElements->toHtml(),
                "RowMenu_host" => htmlentities($metric["host_name"], ENT_QUOTES, "UTF-8"),
                "RowMenu_link" => "main.php?p=" . $p . "&o=cs&msr_id=" . $relation['msr_id'],
                "RowMenu_service" => htmlentities($metric["service_description"], ENT_QUOTES, "UTF-8"),
                "RowMenu_metric" =>
                    CentreonUtils::escapeSecure($metric["metric_name"] . " (" . $metric["unit_name"] . ")"),
                "RowMenu_status" => $relation["activate"] ? _("Enabled") : _("Disabled"),
                "RowMenu_badge" => $relation["activate"] ? "service_ok" : "service_critical",
                "RowMenu_options" => $moptions
            );
            $style != "two" ? $style = "two" : $style = "one";
            $i++;
        }
    }
}
if (isset($elemArr1)) {
    $tpl->assign("elemArr1", $elemArr1);
} else {
    $tpl->assign("elemArr1", array());
}

/*
 * Different messages we put in the template
 */
$tpl->assign('msg', array(
    "addL1" => "main.php?p=" . $p . "&o=as&meta_id=" . $meta_id,
    "addT" => _("Add"),
    "delConfirm" => _("Do you confirm the deletion ?")
));

/*
 * Element we need when we reload the page
 */
$form->addElement('hidden', 'p');
$form->addElement('hidden', 'meta_id');
$tab = array("p" => $p, "meta_id" => $meta_id);
$form->setDefaults($tab);

/*
 * Toolbar select
 */
?>
    <script type="text/javascript">
        function setO(_i) {
            document.forms['form'].elements['o'].value = _i;
        }
    </SCRIPT>
<?php
$attrs1 = array(
    'onchange' => "javascript: " .
        "if (this.form.elements['o1'].selectedIndex == 1 && confirm('" . _("Do you confirm the deletion ?") . "')) {" .
        " 	setO(this.form.elements['o1'].value); submit();} "
);
$form->addElement('select', 'o1', null, array(null => _("More actions..."), "ds" => _("Delete")), $attrs1);
$form->setDefaults(array('o1' => null));


$attrs2 = array(
    'onchange' => "javascript: " .
        "if (this.form.elements['o2'].selectedIndex == 1 && confirm('" . _("Do you confirm the deletion ?") . "')) {" .
        " 	setO(this.form.elements['o2'].value); submit();} "
);
$form->addElement('select', 'o2', null, array(null => _("More actions..."), "ds" => _("Delete")), $attrs2);
$form->setDefaults(array('o2' => null));

$o1 = $form->getElement('o1');
$o1->setValue(null);
$o1->setSelected(null);

$o2 = $form->getElement('o2');
$o2->setValue(null);
$o2->setSelected(null);

$tpl->assign('limit', $limit);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->display("listMetric.ihtml");
