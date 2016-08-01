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

include_once _CENTREON_PATH_."www/class/centreonGMT.class.php";
include_once _CENTREON_PATH_."www/class/centreonDB.class.php";

/*
 * Init GMT class
 */
$centreonGMT = new CentreonGMT($pearDB);
$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);

$hostStr = $centreon->user->access->getHostsString("ID", $pearDBO);

if ($centreon->user->access->checkAction("service_schedule_downtime")) {
    isset($_GET["host_id"]) ? $cG = $_GET["host_id"] : $cG = null;
    isset($_POST["host_id"]) ? $cP = $_POST["host_id"] : $cP = null;
    $cG ? $host_id = $cG : $host_id = $cP;

    $svc_description = null;

    if (isset($_GET["host_name"]) && isset($_GET["service_description"])) {
        $host_id = getMyHostID($_GET["host_name"]);
        $service_id = getMyServiceID($_GET["service_description"], $host_id);
        $host_name = $_GET["host_name"];
        $svc_description = $_GET["service_description"];
    } else {
        $host_name = null;
    }
    $data = array();
    $data = array(  "start" => $centreonGMT->getDate("Y/m/d", time() + 120),
                    "end" => $centreonGMT->getDate("Y/m/d", time() + 7320),
                    "start_time" => $centreonGMT->getDate("G:i", time() + 120),
                    "end_time" => $centreonGMT->getDate("G:i", time() + 7320)
                );
    if (isset($host_id)) {
        $data["host_id"] = $host_id;
    }
    if (isset($service_id)) {
        $data["service_id"] = $service_id;
    }

    /*
	 * Database retrieve information for differents elements list we need on the page
	 */
    $hosts = array(null => null);
    $query = "SELECT host_id, host_name " .
            "FROM `host` " .
            "WHERE host_register = '1' " .
            $centreon->user->access->queryBuilder("AND", "host_id", $hostStr) .
            "ORDER BY host_name";
    $DBRESULT = $pearDB->query($query);
    while ($host = $DBRESULT->fetchRow()) {
        $hosts[$host["host_id"]]= $host["host_name"];
    }
    $DBRESULT->free();

    $services = array(null => null);
    if (isset($host_id)) {
        $services = $centreon->user->access->getHostServices($pearDBO, $host_id);
    }

    $debug = 0;
    $attrsTextI         = array("size"=>"3");
    $attrsText      = array("size"=>"30");
    $attrsTextarea  = array("rows"=>"7", "cols"=>"100");

    /*
	 * Form begin
	 */
    $form = new HTML_QuickForm('Form', 'POST', "?p=".$p);
    $form->addElement('header', 'title', _("Add a Service downtime"));

    /*
	 * Indicator basic information
	 */
    $redirect = $form->addElement('hidden', 'o');
    $redirect->setValue($o);

    $form->addElement('select', 'host_id', _("Host Name"), $hosts, array("onChange" =>"this.form.submit();"));
    $form->addElement('select', 'service_id', _("Service"), $services);
    $chbx = $form->addElement('checkbox', 'persistant', _("Fixed"), null, array('id' => 'fixed', 'onClick' => 'javascript:setDurationField()'));
    if (isset($centreon->optGen['monitoring_dwt_fixed']) && $centreon->optGen['monitoring_dwt_fixed']) {
        $chbx->setChecked(true);
    }
    $form->addElement('textarea', 'comment', _("Comments"), $attrsTextarea);
    $form->addElement('text', 'start', _("Start Time"), array('size' => 10, 'class' => 'datepicker'));
    $form->addElement('text', 'end', _("End Time"), array('size' => 10, 'class' => 'datepicker'));
    $form->addElement('text', 'start_time', '', array('size' => 5, 'class' => 'timepicker'));
    $form->addElement('text', 'end_time', '', array('size' => 5, 'class' => 'timepicker'));
    $form->addElement('text', 'duration', _("Duration"), array('size' => '15', 'id' => 'duration'));

    // uncomment this section : the user can choose to set a downtime based on the host time or the centreon user time.
    /*
    $host_or_centreon_time[] = HTML_QuickForm::createElement('radio', 'host_or_centreon_time', null, _("Centreon Time"), '0');
    $host_or_centreon_time[] = HTML_QuickForm::createElement('radio', 'host_or_centreon_time', null, _("Host Time"), '1');
    $form->addGroup($host_or_centreon_time, 'host_or_centreon_time', _("Select Host or Centreon Time"), '&nbsp;');        
    $form->setDefaults(array('host_or_centreon_time' => '0'));   
    */
    
    $defaultDuration = 3600;
    if (isset($centreon->optGen['monitoring_dwt_duration']) && $centreon->optGen['monitoring_dwt_duration']) {
        $defaultDuration = $centreon->optGen['monitoring_dwt_duration'];
    }
    $form->setDefaults(array('duration' => $defaultDuration));
    
    $scaleChoices = array("s" => _("Seconds"),
                          "m" => _("Minutes"),
                          "h" => _("Hours"),
                          "d" => _("Days")
                );
    $form->addElement('select', 'duration_scale', _("Scale of time"), $scaleChoices);
    $defaultScale = 's';
    if (isset($centreon->optGen['monitoring_dwt_duration_scale']) && $centreon->optGen['monitoring_dwt_duration_scale']) {
        $defaultScale = $centreon->optGen['monitoring_dwt_duration_scale'];
    }
    $form->setDefaults(array('duration_scale' => $defaultScale));
    
    $form->addElement('textarea', 'comment', _("Comments"), $attrsTextarea);

    $form->addRule('host_id', _("Required Field"), 'required');
    $form->addRule('service_id', _("Required Field"), 'required');
    $form->addRule('end', _("Required Field"), 'required');
    $form->addRule('start', _("Required Field"), 'required');
    $form->addRule('end_time', _("Required Field"), 'required');
    $form->addRule('start_time', _("Required Field"), 'required');
    $form->addRule('comment', _("Required Field"), 'required');

    $form->setDefaults($data);

    $subA = $form->addElement('submit', 'submitA', _("Save"));
    $res = $form->addElement('reset', 'reset', _("Reset"));

    if ((isset($_POST["submitA"]) && $_POST["submitA"]) && $form->validate()) {
        if (!isset($_POST["persistant"]) || !in_array($_POST["persistant"], array('0', '1'))) {
            $_POST["persistant"] = '0';
        }
        isset($_POST['host_or_centreon_time']['host_or_centreon_time']) && $_POST['host_or_centreon_time']['host_or_centreon_time'] ? $host_or_centreon_time = $_POST['host_or_centreon_time']['host_or_centreon_time'] : $host_or_centreon_time = "0";
            
        if (!isset($_POST["comment"])) {
            $_POST["comment"] = 0;
        }
            $_POST["comment"] = str_replace("'", " ", $_POST['comment']);
            $duration = null;
        if (isset($_POST['duration'])) {
            if (isset($_POST['duration_scale'])) {
                $duration_scale = $_POST['duration_scale'];
            } else {
                $duration_scale = 's';
            }
                
            switch ($duration_scale) {
                default:
                case 's':
                    $duration = $_POST['duration'];
                    break;
                    
                case 'm':
                    $duration = $_POST['duration'] * 60;
                    break;
                    
                case 'h':
                    $duration = $_POST['duration'] * 60 * 60;
                    break;
                    
                case 'd':
                    $duration = $_POST['duration'] * 60 * 60 * 24;
                    break;
            }
        }
            $ecObj->addSvcDowntime(
                $_POST["host_id"],
                $_POST["service_id"],
                $_POST["comment"],
                $_POST["start"] . ' ' . $_POST['start_time'],
                $_POST["end"] . ' ' . $_POST['end_time'],
                $_POST["persistant"],
                $duration,
                $host_or_centreon_time
            );
            require_once("listDowntime.php");
    } else {
        /*
		 * Smarty template Init
		 */
        $tpl = new Smarty();
        $tpl = initSmartyTpl($path, $tpl, "template/");

        /*
		 * Apply a template definition
		 */
        $renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
        $renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
        $renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
        $form->accept($renderer);
        $tpl->assign('form', $renderer->toArray());
        $tpl->assign('seconds', _("seconds"));
        $tpl->assign('o', $o);
        $tpl->display("AddSvcDowntime.ihtml");
    }
} else {
    require_once("../errors/alt_error.php");
}
?>
<script type='text/javascript'>
jQuery(function() {
    setDurationField();
});

function setDurationField()
{
    var durationField = document.getElementById('duration');
    var fixedCb = document.getElementById('fixed');

    if (fixedCb.checked == true) {
        durationField.disabled = true;
    } else {
        durationField.disabled = false;
    }
}
</script>
