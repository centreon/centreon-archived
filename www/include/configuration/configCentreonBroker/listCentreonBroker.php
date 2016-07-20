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

include_once("./class/centreonUtils.class.php");

include("./include/common/autoNumLimit.php");

/*
 * nagios servers comes from DB
 */
$nagios_servers = array();
$DBRESULT = $pearDB->query("SELECT * FROM nagios_server ORDER BY name");
while ($nagios_server = $DBRESULT->fetchRow()) {
    $nagios_servers[$nagios_server["id"]] = $nagios_server["name"];
}
$DBRESULT->free();

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

/* Access level */
($centreon->user->access->page($p) == 1) ? $lvl_access = 'w' : $lvl_access = 'r';
$tpl->assign('mode_access', $lvl_access);

/*
 * start header menu
 */
$tpl->assign("headerMenu_name", _("Name"));
$tpl->assign("headerMenu_desc", _("Requester"));
$tpl->assign("headerMenu_outputs", _("Outputs"));
$tpl->assign("headerMenu_inputs", _("Inputs"));
$tpl->assign("headerMenu_loggers", _("Loggers"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));

/*
 * Centreon Brober config list
 */
$aclCond = "";
$search = '';
if (isset($_POST['searchCB']) && $_POST['searchCB']) {
    $search = $_POST['searchCB'];
}
if (!$centreon->user->admin && count($allowedBrokerConf)) {
    if ($search) {
        $aclCond = " AND ";
    } else {
        $aclCond = " WHERE ";
    }
    $aclCond .= "config_id IN (".implode(',', array_keys($allowedBrokerConf)).") ";
}
if ($search) {
    $rq = "SELECT SQL_CALC_FOUND_ROWS config_id, config_name, ns_nagios_server, config_activate
               FROM cfg_centreonbroker
               WHERE config_name LIKE '%".htmlentities($search, ENT_QUOTES, "UTF-8")."%'
               $aclCond
               ORDER BY config_name
               LIMIT ".$num * $limit.", ".$limit;
} else {
    $rq = "SELECT SQL_CALC_FOUND_ROWS config_id, config_name, ns_nagios_server, config_activate
               FROM cfg_centreonbroker
               $aclCond
               ORDER BY config_name
               LIMIT ".$num * $limit.", ".$limit;
}
$DBRESULT = $pearDB->query($rq);

/*
 * Get results numbers
 */
$rows = $pearDB->numberRows();

include("./include/common/checkPagination.php");

$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

/*
 * Different style between each lines
 */
$style = "one";

/*
 * Fill a tab with a mutlidimensionnal Array we put in $tpl
 */
$elemArr = array();
for ($i = 0; $config = $DBRESULT->fetchRow(); $i++) {
    $moptions = "";
    $selectedElements = $form->addElement('checkbox', "select[".$config['config_id']."]");

    if ($config["config_activate"]) {
        $moptions .= "<a href='main.php?p=".$p."&id=".$config['config_id']."&o=u&limit=".$limit."&num="
            .$num."&search=".$search."'><img src='img/icons/disabled.png' class='ico-14' border='0' alt='"
            ._("Disabled")."'></a>&nbsp;&nbsp;";
    } else {
        $moptions .= "<a href='main.php?p=".$p."&id=".$config['config_id']."&o=s&limit=".$limit."&num=".$num."&search="
            .$search."'><img src='img/icons/enabled.png' class='ico-14' border='0' alt='"
            ._("Enabled")."'></a>&nbsp;&nbsp;";
    }
    $moptions .= "&nbsp;<input onKeypress=\"if(event.keyCode > 31 "
        . "&& (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; "
        . "if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\""
        . "  maxlength=\"3\" size=\"3\" value='1' "
        . "style=\"margin-bottom:0px;\" name='dupNbr[".$config['config_id']."]'></input>";

    /*
     * Number of output
     */
    $res = $pearDB->query("SELECT COUNT(DISTINCT(config_group_id)) as num 
                            FROM cfg_centreonbroker_info 
                            WHERE config_group = 'output' 
                            AND config_id = " . $config['config_id']);
    $row = $res->fetchRow();
    $outputNumber = $row["num"];

    /*
     * Number of input
     */
    $res = $pearDB->query("SELECT COUNT(DISTINCT(config_group_id)) as num 
                            FROM cfg_centreonbroker_info 
                            WHERE config_group = 'input' 
                            AND config_id = " .$config['config_id']);
    $row = $res->fetchRow();
    $inputNumber = $row["num"];

    /*
     * Number of logger
     */
    $res = $pearDB->query("SELECT COUNT(DISTINCT(config_group_id)) as num 
                            FROM cfg_centreonbroker_info 
                            WHERE config_group = 'logger' 
                            AND config_id = " . $config['config_id']);
    $row = $res->fetchRow();
    $loggerNumber = $row["num"];

    $elemArr[$i] = array(
                         "MenuClass" => "list_".$style,
                         "RowMenu_select" => $selectedElements->toHtml(),
                         "RowMenu_name" => CentreonUtils::escapeSecure($config["config_name"]),
                         "RowMenu_link" => "?p=".$p."&o=c&id=".$config['config_id'],
                         "RowMenu_desc" => CentreonUtils::escapeSecure(
                             substr(
                                 $nagios_servers[$config["ns_nagios_server"]],
                                 0,
                                 40
                             )
                         ),
                         "RowMenu_inputs" => $inputNumber,
                         "RowMenu_outputs" => $outputNumber,
                         "RowMenu_loggers" => $loggerNumber,
                         "RowMenu_status" => $config["config_activate"] ? _("Enabled") : _("Disabled"),
                         "RowMenu_badge" => $config["config_activate"] ? "service_ok" : "service_critical",
                         "RowMenu_options" => $moptions);
    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("elemArr", $elemArr);

/*
 * Different messages we put in the template
 */
$tpl->assign('msg', array(
    "addL"=>"?p=".$p."&o=a",
    "addT"=>_("Add"),
    "addWizard" => _('Add with wizard'),
    "delConfirm"=>_("Do you confirm the deletion ?")
));
?>
<script type="text/javascript">
function setO(_i) {
    document.forms['form'].elements['o'].value = _i;
}
</SCRIPT>
<?php
$attrs = array(
               'onchange'=>"javascript: " .
               " var bChecked = isChecked(); ".
               " if (this.form.elements['o1'].selectedIndex != 0 && !bChecked) {".
               " alert('"._("Please select one or more items")."'); return false;} " .
               "if (this.form.elements['o1'].selectedIndex == 1 && confirm('"
                   ._("Do you confirm the duplication ?")."')) {" .
               " 	setO(this.form.elements['o1'].value); submit();} " .
               "else if (this.form.elements['o1'].selectedIndex == 2 && confirm('"
                   ._("Do you confirm the deletion ?")."')) {" .
               " 	setO(this.form.elements['o1'].value); submit();} " .
               "else if (this.form.elements['o1'].selectedIndex == 3) {" .
               " 	setO(this.form.elements['o1'].value); submit();} " .
               "");
$form->addElement(
    'select',
    'o1',
    null,
    array(null=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")),
    $attrs
);
$form->setDefaults(array('o1' => null));
$o1 = $form->getElement('o1');
$o1->setValue(null);

$attrs = array(
               'onchange'=>"javascript: " .
               " var bChecked = isChecked(); ".
               " if (this.form.elements['o2'].selectedIndex != 0 && !bChecked) {".
               " alert('"._("Please select one or more items")."'); return false;} " .
               "if (this.form.elements['o2'].selectedIndex == 1 && confirm('"
                   ._("Do you confirm the duplication ?")."')) {" .
               " 	setO(this.form.elements['o2'].value); submit();} " .
               "else if (this.form.elements['o2'].selectedIndex == 2 && confirm('"
                   ._("Do you confirm the deletion ?")."')) {" .
               " 	setO(this.form.elements['o2'].value); submit();} " .
               "else if (this.form.elements['o2'].selectedIndex == 3) {" .
               " 	setO(this.form.elements['o2'].value); submit();} " .
               "");
$form->addElement(
    'select',
    'o2',
    null,
    array(null=>_("More actions..."), "m"=>_("Duplicate"), "d"=>_("Delete")),
    $attrs
);
$form->setDefaults(array('o2' => null));
$o2 = $form->getElement('o2');
$o2->setValue(null);

$tpl->assign('limit', $limit);
$tpl->assign('searchCB', $search);

/*
 * Apply a template definition
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());

$tpl->display("listCentreonBroker.ihtml");

?>
