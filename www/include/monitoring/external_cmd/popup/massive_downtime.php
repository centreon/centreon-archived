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

$select = array();
if (isset($_GET['select'])) {
    foreach ($_GET['select'] as $key => $value) {
        if ($cmd == '75') {
            $tmp = preg_split("/\;/", $key);
            $select[] = $tmp[0];
        } else {
            $select[] = $key;
        }
    }
}

$path = _CENTREON_PATH_."/www/include/monitoring/external_cmd/popup/";

/*
 * Init GMT
 */
$centreonGMT = new CentreonGMT($pearDB);
$centreonGMT->getMyGMTFromSession(session_id(), $pearDB);

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTplForPopup($path, $tpl, './templates/', _CENTREON_PATH_);

/*
 * Pear library
 */
require_once "HTML/QuickForm.php";
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

$form = new HTML_QuickForm('select_form', 'GET', 'main.php');

$form->addElement('header', 'title', _("Set downtimes"));

$tpl->assign('authorlabel', _("Alias"));
$tpl->assign('authoralias', $centreon->user->get_alias());

$form->addElement('textarea', 'comment', _("Comment"), array("rows"=>"5", "cols"=>"70", "id"=>"popupComment"));
$form->setDefaults(array("comment" => sprintf(_("Downtime set by %s"), $centreon->user->alias)));

$form->addElement('text', 'start', _('Start Time'), array('id'=>'start', 'size'=>10, 'class'=>'datepicker'));
$form->addElement('text', 'end', _('End Time'), array('id'=>'end', 'size'=>10, 'class'=>'datepicker'));
    
$form->addElement('text', 'start_time', '', array('id'=>'start_time', 'size' => 5, 'class' => 'timepicker'));
$form->addElement('text', 'end_time', '', array('id'=>'end_time', 'size' => 5, 'class' => 'timepicker'));

$form->addElement('text','timezone_warning', _("*The timezone used is configured on your user settings"));
    
$form->setDefaults(
    array(
        "start" => $centreonGMT->getDate("Y/m/d", time()),
        "end" => $centreonGMT->getDate("Y/m/d", time() + 7200),
        "start_time" => $centreonGMT->getDate("G:i", time()),
        "end_time" => $centreonGMT->getDate("G:i", time() + 7200)
    )
);
/*
$host_or_centreon_time[] = HTML_QuickForm::createElement('radio', 'host_or_centreon_time', null, _("Centreon Time"), '0');
$host_or_centreon_time[] = HTML_QuickForm::createElement('radio', 'host_or_centreon_time', null, _("Host Time"), '1');
$form->addGroup($host_or_centreon_time, 'host_or_centreon_time', _("Select Host or Centreon Time"), '&nbsp;');        
$form->setDefaults(array('host_or_centreon_time' => '0'));   
*/
$form->addElement('text', 'duration', _('Duration'), array('id'=>'duration', 'width'=>'30', 'disabled'=>'true'));
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
$form->addElement('select', 'duration_scale', _("Scale of time"), $scaleChoices, array('id'=>'duration_scale'));
$defaultScale = 's';
if (isset($centreon->optGen['monitoring_dwt_duration_scale']) && $centreon->optGen['monitoring_dwt_duration_scale']) {
    $defaultScale = $centreon->optGen['monitoring_dwt_duration_scale'];
}
$form->setDefaults(array('duration_scale' => $defaultScale));

$chckbox[] = $form->addElement('checkbox', 'fixed', _("Fixed"), "", array("id"=>"fixed"));
$chckbox[0]->setChecked(true);

$chckbox2[] = $form->addElement('checkbox', 'downtimehostservice', _("Set downtimes on services attached to hosts"), "", array("id"=>"downtimehostservice"));
$chckbox2[0]->setChecked(true);

$form->addElement('hidden', 'author', $centreon->user->get_alias(), array("id"=>"author"));

$form->addRule('comment', _("Comment is required"), 'required', '', 'client');
$form->setJsWarnings(_("Invalid information entered"), _("Please correct these fields"));

$form->addElement('button', 'submit', _("Set downtime"), array("onClick" => "send_the_command();", "class" => "btc bt_info"));
$form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));

$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');

$form->accept($renderer);
$tpl->assign('form', $renderer->toArray());

$defaultFixed = "";
if (isset($centreon->optGen['monitoring_dwt_fixed']) && $centreon->optGen['monitoring_dwt_fixed']) {
    $defaultFixed = "checked";
}
$tpl->assign('defaultFixed', $defaultFixed);

$defaultSetDwtOnSvc = "";
if (isset($centreon->optGen['monitoring_dwt_svc']) && $centreon->optGen['monitoring_dwt_svc']) {
    $defaultSetDwtOnSvc = "checked";
}
$tpl->assign('defaultSetDwtOnSvc', $defaultSetDwtOnSvc);

$tpl->assign('o', $o);
$tpl->assign('p', $p);
$tpl->assign('cmd', $cmd);
$tpl->assign('select', $select);
$tpl->display("massive_downtime.ihtml");
