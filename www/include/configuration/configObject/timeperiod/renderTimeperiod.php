<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 */
if (!isset($centreon)) {
    exit();
}

isset($_GET["tp_id"]) ? $tpG = $_GET["tp_id"] : $tpG = null;
isset($_POST["tp_id"]) ? $tpP = $_POST["tp_id"] : $tpP = null;
$tpG ? $tp_id = $tpG : $tp_id = $tpP;
$path = "./include/configuration/configObject/timeperiod/";
require_once $path."DB-Func.php";
require_once "./include/common/common-Func.php";
require_once $centreon_path . "www/class/centreonTimeperiodRenderer.class.php";
$imgpath = "./include/common/javascript/scriptaculous/images/bramus/";
$imgs = scandir($imgpath);
$t = null;
if ($tp_id) {
    $t = new CentreonTimePeriodRenderer($pearDB,$tp_id,1);
    $t->timeBars();
}
$query = "SELECT tp_name, tp_id FROM timeperiod";
$DBRESULT = $pearDB->query($query);
$tplist[0] = _("Select Timeperiod...");
while ($row = $DBRESULT->fetchRow()) {
    $tplist[$row['tp_id']] = $row['tp_name'];
}
$form = new HTML_QuickForm('form', 'POST', "?p=".$p."&o=s");
$attrs1 = array('onchange'=>"javascript: setTP(this.form.elements['tp_id'].value); submit();");
$form->addElement('select', 'tp_id', null, $tplist, $attrs1);
$form->setDefaults(array('tp_id' => null));
$tpel = $form->getElement('tp_id');
if ($tp_id) {
    $tpel->setValue($tp_id);
    $tpel->setSelected($tp_id);
}

$attrsTextLong  = array("size"=>"55");
$form->addElement('header', 'title',_("Resulting Time Period with inclusions"));
$form->addElement('header', 'information',_("General Information"));
$form->addElement('header', 'notification',_("Time Range"));
$form->addElement('header', 'exception', _("Exception List"));
$form->addElement('text', 'tp_name', _("Timeperiod Name"), $attrsTextLong);
$form->addElement('text', 'tp_alias', _("Timeperiod Alias"), $attrsTextLong);
$form->addElement('text', 'tp_sunday', _("Sunday"), $attrsTextLong);
$form->addElement('text', 'tp_monday', _("Monday"), $attrsTextLong);
$form->addElement('text', 'tp_tuesday', _("Tuesday"), $attrsTextLong);
$form->addElement('text', 'tp_wednesday', _("Wednesday"), $attrsTextLong);
$form->addElement('text', 'tp_thursday', _("Thursday"), $attrsTextLong);
$form->addElement('text', 'tp_friday', _("Friday"), $attrsTextLong);
$form->addElement('text', 'tp_saturday', _("Saturday"), $attrsTextLong);
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$form->accept($renderer);
$labels = array(
					'unset_timerange'    => _('Unset Timerange'),
                    'included_timerange' => _('Included Timerange'),
                    'excluded_timerange' => _('Excluded Timerange'),
                    'timerange_overlaps' => _('Timerange Overlaps'),
                    'hover_for_info' 	 => _('Hover on timeline to see more information'),
                    'no_tp_selected'	 => _('No time period selected')
                );
$tpl->assign('labels', $labels);
$tpl->assign('form', $renderer->toArray());
$tpl->assign('tpId', $tp_id);
$tpl->assign('tp', $t);
$tpl->assign('path', $path);
$tpl->display("renderTimeperiod.ihtml");
?>
<script type="text/javascript">
var tipDiv;

jQuery(function() {
    genToolTip();
});
 
/**
 * Set Time period
 */
function setTP(_i) {
	document.forms['form'].elements['tp_id'].value = _i;
}

/**
 * The tool tip is created and referenced as a global object
 */
function genToolTip() {
	if (document.createElement) {
		tipDiv = document.createElement('div');
		document.body.appendChild(tipDiv);
		tipDiv.appendChild(document.createTextNode('initial text'));
		tipDiv.className = 'toolTip';
		tipDiv.style.display = 'none';
	}
}

/**
 * Show tooltip
 */
function showTip(e, txt) {
	if ( tipDiv ) {
		var e = e || window.event;
		var xy = cursorPos(e);
		tipDiv.firstChild.data = txt;
		tipDiv.style.left = (xy[0] + 5) + 'px';
		tipDiv.style.top = (xy[1] + 15) + 'px';
		tipDiv.style.display = '';
	}
}

/**
 * Hide tooltip
 */
function hideTip() {
	if ( tipDiv ) {
		tipDiv.style.display = 'none';
	}
}

/**
 * Based on quirskmode 'get cursor position' script
 */
function cursorPos(e){
	if (e.pageX || e.pageY) {
		return [ e.pageX, e.pageY ];
	} else if (e.clientX || e.clientY) {
		return [
				e.clientX + document.body.scrollLeft,
				e.clientY + document.body.scrollTop
               ];
	}
}
</script>
