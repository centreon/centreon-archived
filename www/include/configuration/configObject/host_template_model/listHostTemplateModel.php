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
 * Init Host Method
 */
$host_method = new CentreonHost($pearDB);
$mediaObj = new CentreonMedia($pearDB);

/*
 * Get Extended informations
 */
$ehiCache = array();
$DBRESULT = $pearDB->query("SELECT ehi_icon_image, host_host_id FROM extended_host_information");
while ($ehi = $DBRESULT->fetchRow()) {
    $ehiCache[$ehi["host_host_id"]] = $ehi["ehi_icon_image"];
}
$DBRESULT->free();

$search = '';
if (isset($_POST['searchHT'])) {
    $search = $_POST['searchHT'];
    $_SESSION['searchHT'] = $_POST['searchHT'];
} else if (isset($_SESSION['searchHT']) && $_SESSION['searchHT'] != "") {
    $search = $_SESSION['searchHT'];
}

$query = "SELECT COUNT(*) "
    . "FROM host "
    . "WHERE host_register = '0' "
    . "AND (host_name LIKE '%".CentreonDB::escape($search)."%' OR host_alias LIKE '%".CentreonDB::escape($search)."%') ";
$DBRESULT = $pearDB->query($query);
$tmp = $DBRESULT->fetchRow();
$rows = $tmp["COUNT(*)"];

include("./include/common/checkPagination.php");

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
$tpl->assign("headerMenu_desc", _("Alias"));
$tpl->assign("headerMenu_svChilds", _("Linked Services Templates"));
$tpl->assign("headerMenu_parent", _("Templates"));
$tpl->assign("headerMenu_status", _("Status"));
$tpl->assign("headerMenu_options", _("Options"));

/*
 * Host Template list
 */
if ($search) {
    $rq = "SELECT host_id, host_name, host_alias, host_activate, host_template_model_htm_id FROM host WHERE (host_name LIKE '%".CentreonDB::escape($search)."%' OR host_alias LIKE '%".CentreonDB::escape($search)."%') AND host_register = '0' ORDER BY host_name LIMIT ".$num * $limit.", ".$limit;
} else {
    $rq = "SELECT host_id, host_name, host_alias, host_activate, host_template_model_htm_id FROM host WHERE host_register = '0' ORDER BY host_name LIMIT ".$num * $limit.", ".$limit;
}
$DBRESULT = $pearDB->query($rq);

$search = tidySearchKey($search, $advanced_search);

$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

/* Different style between each lines */
$style = "one";

/* Fill a tab with a mutlidimensionnal Array we put in $tpl */
$elemArr = array();
for ($i = 0; $host = $DBRESULT->fetchRow(); $i++) {
    $moptions = "";
    $selectedElements = $form->addElement('checkbox', "select[".$host['host_id']."]");
    if (isset($lockedElements[$host['host_id']])) {
        $selectedElements->setAttribute('disabled', 'disabled');
    } else {
        if ($host["host_activate"]) {
            $moptions .= "<a href='main.php?p=".$p."&host_id=".$host['host_id']."&o=u&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icons/disabled.png' class='ico-14 margin_right' border='0' alt='"._("Disabled")."'></a>&nbsp;&nbsp;";
        } else {
            $moptions .= "<a href='main.php?p=".$p."&host_id=".$host['host_id']."&o=s&limit=".$limit."&num=".$num."&search=".$search."'><img src='img/icons/enabled.png' class='ico-14 margin_right' border='0' alt='"._("Enabled")."'></a>&nbsp;&nbsp;";
        }
        $moptions .= "&nbsp;";
        $moptions .= "<input onKeypress=\"if(event.keyCode > 31 && (event.keyCode < 45 || event.keyCode > 57)) event.returnValue = false; if(event.which > 31 && (event.which < 45 || event.which > 57)) return false;\" maxlength=\"3\" size=\"3\" value='1' style=\"margin-bottom:0px;\" name='dupNbr[".$host['host_id']."]'></input>";
    }
    # If the name of our Host Model is in the Template definition, we have to catch it, whatever the level of it :-)
    if (!$host["host_name"]) {
        $host["host_name"] = getMyHostName($host["host_template_model_htm_id"]);
    }

    /* TPL List */
    $tplArr = array();
    $tplStr = null;

    $tplArr = getMyHostMultipleTemplateModels($host['host_id']);
    if (count($tplArr)) {
        $firstTpl = 1;
        foreach ($tplArr as $key => $value) {
            if ($firstTpl) {
                $tplStr .= "<a href='main.php?p=60103&o=c&host_id=".$key."'>".$value."</a>";
                $firstTpl = 0;
            } else {
                $tplStr .= "&nbsp;|&nbsp;<a href='main.php?p=60103&o=c&host_id=".$key."'>".$value."</a>";
            }
        }
    }

    /*
	 * Check icon
	 */
    if ((isset($ehiCache[$host["host_id"]]) && $ehiCache[$host["host_id"]])) {
        $host_icone = "./img/media/" . $mediaObj->getFilename($ehiCache[$host["host_id"]]);
    } elseif ($icone = $host_method->replaceMacroInString($host["host_id"], getMyHostExtendedInfoImage($host["host_id"], "ehi_icon_image", 1))) {
        $host_icone = "./img/media/" . $icone;
    } else {
        $host_icone = "./img/icons/host.png";
    }

    /*
	 * Service List
	 */
    $svArr = array();
    $svStr = null;
    $svArr = getMyHostServices($host['host_id']);
    $elemArr[$i] = array("MenuClass" => "list_".$style,
                    "RowMenu_select" => $selectedElements->toHtml(),
                    "RowMenu_name" => CentreonUtils::escapeSecure($host["host_name"]),
                    "RowMenu_link" => "?p=".$p."&o=c&host_id=".$host['host_id'],
                    "RowMenu_desc" => CentreonUtils::escapeSecure($host["host_alias"]),
                    "RowMenu_icone" => $host_icone,
                    "RowMenu_svChilds" => count($svArr),
                    "RowMenu_parent" => CentreonUtils::escapeSecure($tplStr),
                    "RowMenu_status" => $host["host_activate"] ? _("Enabled") : _("Disabled"),
                    "RowMenu_badge" => $host["host_activate"] ? "service_ok" : "service_critical",
                    "RowMenu_options" => $moptions);
    $style != "two" ? $style = "two" : $style = "one";
}
$tpl->assign("elemArr", $elemArr);

/* Different messages we put in the template */
$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>_("Add"), "delConfirm"=>_("Do you confirm the deletion ?")));

#
## Toolbar select
#
?>
<script type="text/javascript">
function setO(_i) {
    document.forms['form'].elements['o'].value = _i;
}
</SCRIPT>
<?php
foreach (array('o1', 'o2') as $option) {
    $attrs1 = array(
        'onchange' => "javascript: " .
            "var bChecked = isChecked();".
            "if (this.form.elements['$option'].selectedIndex != 0 && !bChecked) {".
            "   alert('"._("Please select one or more items")."'); return false;} " .
            "if (this.form.elements['$option'].selectedIndex == 1 && confirm('"._("Do you confirm the duplication ?")."')) {" .
            "   setO(this.form.elements['$option'].value); submit();} " .
            "else if (this.form.elements['$option'].selectedIndex == 2 && confirm('"._("Do you confirm the deletion ?")."')) {" .
            "   setO(this.form.elements['$option'].value); submit();} " .
            "else if (this.form.elements['$option'].selectedIndex == 3 || this.form.elements['$option'].selectedIndex == 4 || this.form.elements['$option'].selectedIndex == 5){" .
            "   setO(this.form.elements['$option'].value); submit();} " .
            "this.form.elements['o1'].selectedIndex = 0");
    $form->addElement(
        'select',
        $option,
        null,
        array(  null => _("More actions..."),
                    "m" => _("Duplicate"),
                    "d" => _("Delete"),
                    "mc" => _("Massive Change"),
                    "ms" => _("Enable"),
                    "mu" => _("Disable")),
        $attrs1
    );
    $form->setDefaults(array($option => null));
    $o1 = $form->getElement($option);
    $o1->setValue(null);
    $o1->setSelected(null);
}

$tpl->assign('limit', $limit);

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('searchHT', $search);
$tpl->display("listHostTemplateModel.ihtml");
