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

include("./include/common/autoNumLimit.php");

!isset($_GET["sort_types"]) ? $sort_types = 0 : $sort_types = $_GET["sort_types"];
!isset($_GET["order"]) ? $order = 'ASC' : $order = $_GET["order"];
!isset($_GET["num"]) ? $num = 0 : $num = $_GET["num"];
!isset($_GET["host_search"]) ? $host_search = 0 : $host_search = $_GET["host_search"];
!isset($_GET["search_type_host"]) ? $search_type_host = 1 : $search_type_host = $_GET["search_type_host"];
!isset($_GET["search_type_service"]) ? $search_type_service = 1 : $search_type_service = $_GET["search_type_service"];
!isset($_GET["sort_type"]) ? $sort_type = "host_name" : $sort_type = $_GET["sort_type"];

/*
 * Check search value in Host search field
 */
if (isset($_GET["host_search"])) {
    $centreon->historySearch[$url] = $_GET["host_search"];
}

$tab_class = array(
    "0" => "list_one",
    "1" => "list_two"
    );
$rows = 10;

include_once("./include/monitoring/status/Common/default_poller.php");
include_once("./include/monitoring/status/Common/default_hostgroups.php");

include_once($svc_path . "/serviceGridJS.php");
$aTypeAffichageLevel1 = array(
    "svcOV" => _("Details"),
    "svcSum" => _("Summary")
);

$aTypeAffichageLevel2 = array(
    "" => _("All"),
    "pb" => _("Problems"),
    "ack_1" => _("Acknowledge"),
    "ack_0" => _("Not Acknowledged"),
);

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($svc_path, $tpl, "/templates/");

$tpl->assign("p", $p);
$tpl->assign('o', $o);
$tpl->assign("sort_types", $sort_types);
$tpl->assign("num", $num);
$tpl->assign("limit", $limit);
$tpl->assign("mon_host", _("Hosts"));
$tpl->assign("mon_status", _("Status"));
$tpl->assign("typeDisplay", _("Display"));
$tpl->assign("typeDisplay2", _("Display details"));
$tpl->assign("mon_ip", _("IP"));
$tpl->assign("mon_last_check", _("Last Check"));
$tpl->assign("mon_duration", _("Duration"));
$tpl->assign("mon_status_information", _("Status information"));

$form = new HTML_QuickForm('select_form', 'GET', "?p=" . $p);

$tpl->assign("order", strtolower($order));
$tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc");
$tpl->assign("tab_order", $tab_order);
?>
<script type="text/javascript">
    _sid = '<?php echo $sid ?>';
    _tm = <?php echo $tM ?>;
    p = <?php echo $p ?>;
    
    function setO(_i) {
        document.forms['form'].elements['cmd'].value = _i;
        document.forms['form'].elements['o1'].selectedIndex = 0;
        document.forms['form'].elements['o2'].selectedIndex = 0;
    }
    function displayingLevel1(val)
    {
       _o = val;
       if (_o == 'svcOV') { 
            _addrXML = "./include/monitoring/status/Services/xml/serviceGridXML.php";
            _addrXSL = "./include/monitoring/status/Services/xsl/serviceGrid.xsl";
        } else {
            _addrXML = "./include/monitoring/status/Services/xml/serviceSummaryXML.php";
            _addrXSL = "./include/monitoring/status/Services/xsl/serviceSummary.xsl";
        }
        monitoring_refresh();
    }
    function displayingLevel2(val)
    {
        var sel1 = document.getElementById("typeDisplay").value;
        _o = sel1;
        if (val != '') {
            _o = _o + "_" + val;
        }

        monitoring_refresh();
    }
</script>
<?php
$form->addElement('select', 'typeDisplay', _('Display'), $aTypeAffichageLevel1, array('id' => 'typeDisplay', 'onChange' => "displayingLevel1(this.value);"));
$form->addElement('select', 'typeDisplay2', _('Display '), $aTypeAffichageLevel2, array('id' => 'typeDisplay2', 'onChange' => "displayingLevel2(this.value);"));

foreach (array('o1', 'o2') as $option) {
    $attrs = array('onchange' => "javascript: setO(this.form.elements['$option'].value); submit();");
    $form->addElement('select', $option, null, array(null => _("More actions..."),
        "3" => _("Verification Check"),
        "4" => _("Verification Check (Forced)"),
        "70" => _("Services : Acknowledge"),
        "71" => _("Services : Disacknowledge"),
        "80" => _("Services : Enable Notification"),
        "81" => _("Services : Disable Notification"),
        "90" => _("Services : Enable Check"),
        "91" => _("Services : Disable Check"),
        "72" => _("Hosts : Acknowledge"),
        "73" => _("Hosts : Disacknowledge"),
        "82" => _("Hosts : Enable Notification"),
        "83" => _("Hosts : Disable Notification"),
        "92" => _("Hosts : Enable Check"),
        "93" => _("Hosts : Disable Check")), $attrs);

    $form->setDefaults(array($option => null));
    $o1 = $form->getElement($option);
    $o1->setValue(null);
    $o1->setSelected(null);
}

$tpl->assign('limit', $limit);

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('search', _('Search'));
$tpl->assign('pollerStr', _('Poller'));
$tpl->assign('poller_listing', $centreon->user->access->checkAction('poller_listing'));
$tpl->assign('hgStr', _('Hostgroup'));
$tpl->assign('form', $renderer->toArray());
$tpl->display("serviceGrid.ihtml");
