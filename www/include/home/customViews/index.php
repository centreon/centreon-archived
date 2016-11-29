<?php
/**
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

require_once _CENTREON_PATH_ . 'www/class/centreonCustomView.class.php';
require_once _CENTREON_PATH_ . "www/class/centreonWidget.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonContactgroup.class.php";

/**
 * Quickform
 */
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/select2.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

try {
    $db = new CentreonDB();
    $viewObj = new CentreonCustomView($centreon, $db);
    
    /*
	 * Smarty
	 */
    $path = "./include/home/customViews/";
    
    /*
     * Smarty INIT
     */
    $template = new Smarty();
    $template = initSmartyTpl($path, $template, "./");
    
    $aclEdit = $centreon->user->access->page('10301', true);
    $template->assign('aclEdit', $aclEdit);
    
    $aclShare = $centreon->user->access->page('10302', true);
    $template->assign('aclShare', $aclShare);
    
    $aclParameters = $centreon->user->access->page('10303', true);
    $template->assign('aclParameters', $aclParameters);
    
    $aclAddWidget = $centreon->user->access->page('10304', true);
    $template->assign('aclAddWidget', $aclAddWidget);
    
    $aclRotation = $centreon->user->access->page('10305', true);
    $template->assign('aclRotation', $aclRotation);
    
    $aclDeleteView = $centreon->user->access->page('10306', true);
    $template->assign('aclDeleteView', $aclDeleteView);
    
    $aclAddView = $centreon->user->access->page('10307', true);
    $template->assign('aclAddView', $aclAddView);
    
    $aclSetDefault = $centreon->user->access->page('10308', true);
    $template->assign('aclSetDefault', $aclSetDefault);
   
    $template->assign('editMode', _("Show/Hide edit mode"));

    $viewId = $viewObj->getCurrentView();
    $views = $viewObj->getCustomViews();

    $rotationTimer = 0;
    $contactParameters = $centreon->user->getContactParameters($db, array('widget_view_rotation'));

    if (isset($contactParameters['widget_view_rotation'])) {
        $rotationTimer = $contactParameters['widget_view_rotation'];
    }

    $i = 1;
    $indexTab = array(0 => -1);

    foreach ($views as $key => $val) {
        $indexTab[$key] = $i;
        $i++;
        if (!$viewObj->checkPermission($key)) {
            $views[$key]['icon'] = "locked";
        } else {
            $views[$key]['icon'] = "unlocked";
        }
        $views[$key]['default'] = "";
        if ($viewObj->getDefaultViewId() == $key) {
            $views[$key]['default'] = sprintf(" (%s)", _('default'));
            $views[$key]['default'] = '<span class="ui-icon ui-icon-star" style="float:left;"></span>';
        }
    }
    $template->assign('views', $views);
    $template->assign('empty', $i);
    $template->assign('msg', _("No view available. To create a new view, please click \"Add view\" button."));

    $formAddView = new HTML_QuickForm('formAddView', 'post', "?p=103", '_selft', array('onSubmit' => 'submitAddView(); return false;'));
    $formAddView->addElement('header', 'title', _("Create a view"));
    $formAddView->addElement('header', 'information', _("General Information"));


    $query =  "SELECT cv.*, '1' as from_public FROM custom_views cv where public = 1 "
            . " UNION "
            . " SELECT cv.*, '0' as from_public FROM custom_views cv "
            . " INNER JOIN custom_view_user_relation cvur on cv.custom_view_id = cvur.custom_view_id "
            . " WHERE (cvur.user_id = " . $db->escape($centreon->user->user_id)
            . "        OR cvur.usergroup_id IN ( "
            . "           SELECT contactgroup_cg_id "
            . "           FROM contactgroup_contact_relation "
            . "           WHERE contact_contact_id = " . $db->escape($centreon->user->user_id)
            . "           ) "
            . " ) AND cvur.is_consumed = 0 ";
    

    $DBRES = $db->query($query);
    $arrayView = array();
    $arrayView[-1] = "";
    $arrayViewShared = array();
    $arrayViewShared[-1] = "";
    
    while ($row = $DBRES->fetchRow()) {
        if ($row['from_public'] == '1') {
            $arrayView[$row['custom_view_id']] = $row['name'];
        } else {
            $arrayViewShared[$row['custom_view_id']] = $row['name'];
        }
    }

    asort($arrayView);
    asort($arrayViewShared);

    $attrsText      = array("size"=>"30");
    $formAddView->addElement('select', 'viewLoad', _("Public views list"), $arrayView);
    $formAddView->addElement('select', 'viewLoadShare', _("Shared views list"), $arrayViewShared);
    /**
     * Name
     */
    $formAddView->addElement('text', 'name', _("Name"), $attrsText);

    $createLoad = array();
    $createLoad[] = HTML_QuickForm::createElement('radio', 'create_load', null, _("Create new view "), 'create');
    $createLoad[] = HTML_QuickForm::createElement('radio', 'create_load', null, _("Load from existing view"), 'load');
    $formAddView->addGroup($createLoad, 'create_load', _("create or load"), '&nbsp;');
    $formAddView->setDefaults(array('create_load[create_load]' => 'create'));

    /**
     * Layout
     */
    $layouts[] = HTML_QuickForm::createElement('radio', 'layout', null, _("1 Column"), 'column_1');
    $layouts[] = HTML_QuickForm::createElement('radio', 'layout', null, _("2 Columns"), 'column_2');
    $layouts[] = HTML_QuickForm::createElement('radio', 'layout', null, _("3 Columns"), 'column_3');
    $formAddView->addGroup($layouts, 'layout', _("Layout"), '&nbsp;');
    $formAddView->setDefaults(array('layout[layout]' => 'column_1'));

    $formAddView->addElement('checkbox', 'public', '', _("Public"));

    /**
     * Submit button
     */
    $formAddView->addElement('submit', 'submit', _("Submit"), array("class" => "btc bt_success"));
    $formAddView->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $formAddView->addElement('hidden', 'action');
    $formAddView->setDefaults(array('action' => 'add'));

    /**
     * Renderer
     */
    $rendererAddView = new HTML_QuickForm_Renderer_ArraySmarty($template, true);
    $rendererAddView->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $rendererAddView->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $formAddView->accept($rendererAddView);
    $template->assign('formAddView', $rendererAddView->toArray());
    
    /**
     * Form for edit view
     */
    $formEditView = new HTML_QuickForm('formEditView', 'post', "?p=103", '', array('onSubmit' => 'submitEditView(); return false;'));
    $formEditView->addElement('header', 'title', _('Edit a view'));
    $formEditView->addElement('header', 'information', _("General Information"));
    
    /**
     * Name
     */
    $formEditView->addElement('text', 'name', _("Name"), $attrsText);
    
    /**
     * Layout
     */
    $layouts = array();
    $layouts[] = HTML_QuickForm::createElement('radio', 'layout', null, _("1 Column"), 'column_1');
    $layouts[] = HTML_QuickForm::createElement('radio', 'layout', null, _("2 Columns"), 'column_2');
    $layouts[] = HTML_QuickForm::createElement('radio', 'layout', null, _("3 Columns"), 'column_3');
    $formEditView->addGroup($layouts, 'layout', _("Layout"), '&nbsp;');
    $formEditView->setDefaults(array('layout[layout]' => 'column_1'));

    $formEditView->addElement('checkbox', 'public', '', _("Public"));
    /**
     * Submit button
     */
    $formEditView->addElement('submit', 'submit', _("Submit"), array("class" => "btc bt_success"));
    $formEditView->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $formEditView->addElement('hidden', 'action');
    $formEditView->addElement('hidden', 'custom_view_id');
    $formEditView->setDefaults(array('action' => 'edit'));

    /**
     * Renderer
     */
    $rendererEditView = new HTML_QuickForm_Renderer_ArraySmarty($template, true);
    $rendererEditView->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $rendererEditView->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $formEditView->accept($rendererEditView);
    $template->assign('formEditView', $rendererEditView->toArray());

    /**
     * Form share view
     */
    $cgObj = new CentreonContactgroup($db);
    $formShareView = new HTML_QuickForm('formShareView', 'post', "?p=103", '', array('onSubmit' => 'submitShareView(); return false;'));
    $formShareView->addElement('header', 'title', _("Share view"));

    /**
     * Locked
     */
    $locked[] = HTML_QuickForm::createElement('radio', 'locked', null, _("Yes"), '1');
    $locked[] = HTML_QuickForm::createElement('radio', 'locked', null, _("No"), '0');
    $formShareView->addGroup($locked, 'locked', _("Locked?"), '&nbsp;');
    $formShareView->setDefaults(array('locked' => '1'));

    /**
     * Users
     */
    $attrContacts = array(
        'datasourceOrigin' => 'ajax',
        'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_contact&action=list',
        'multiple' => true
    );
    $formShareView->addElement('select2', 'user_id', _("User List"), array(), $attrContacts);

    /*
     * Widgets
     */
    $attrWidgets = array(
        'datasourceOrigin' => 'ajax',
        'multiple' => false,
        'availableDatasetRoute' => './api/internal.php?object=centreon_administration_widget&action=list',
        'allowClear' => false
    );

    /**
     * User groups
     */
    $attrContactgroups = array(
        'datasourceOrigin' => 'ajax',
        'availableDatasetRoute' => './include/common/webServices/rest/internal.php?object=centreon_configuration_contactgroup&action=list',
        'multiple' => true
    );
    $formShareView->addElement('select2', 'usergroup_id', _("User Group List"), array(), $attrContactgroups);
    
    /**
     * Submit button
     */
    $formShareView->addElement('submit', 'submit', _("Share"), array("class" => "btc bt_info"));
    $formShareView->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $formShareView->addElement('hidden', 'action');
    $formShareView->setDefaults(array('action' => 'share'));
    $formShareView->addElement('hidden', 'custom_view_id');
    $rendererShareView = new HTML_QuickForm_Renderer_ArraySmarty($template, true);
    $rendererShareView->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $rendererShareView->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $formShareView->accept($rendererShareView);
    $template->assign('formShareView', $rendererShareView->toArray());

    /**
     * Form add widget
     */
    $widgetObj = new CentreonWidget($centreon, $db);
    $formAddWidget = new HTML_QuickForm('formAddWidget', 'post', "?p=103", '', array('onSubmit' => 'submitAddWidget(); return false;'));
    $formAddWidget->addElement('header', 'w_title', _('Add a widget'));
    $formAddWidget->addElement('header', 'title', _('Add a widget'));
    $formAddWidget->addElement('header', 'information', _("Widget Information"));

    /**
     * Name
     */
    $formAddWidget->addElement('text', 'widget_title', _("Title"), $attrsText);
    $formAddWidget->addElement('select2', 'widget_model_id', _("Widget"), array(), $attrWidgets);

    /**
     * Widgets
     */
    $widgetList = $widgetObj->getWidgetModels();
    $widgetModels = array();
    foreach ($widgetList as $widgetModelId => $widgetModelName) {
        $widgetModels[$widgetModelId] = $widgetObj->getWidgetInfoById($widgetModelId);
    }

    /**
     * Submit button
     */
    $formAddWidget->addElement('submit', 'submit', _("Submit"), array("class" => "btc bt_success"));
    $formAddWidget->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
    $formAddWidget->addElement('hidden', 'action');
    $formAddWidget->addElement('hidden', 'custom_view_id');
    $formAddWidget->setDefaults(array('action' => 'addWidget'));

    /**
     * Renderer
     */
    $rendererAddWidget = new HTML_QuickForm_Renderer_ArraySmarty($template, true);
    $rendererAddWidget->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
    $rendererAddWidget->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
    $formAddWidget->accept($rendererAddWidget);
    $template->assign('widgetModels', $widgetModels);
    $template->assign('formAddWidget', $rendererAddWidget->toArray());
    $template->assign('rotationTimer', $rotationTimer);
    
    $template->display("index.ihtml");
} catch (CentreonCustomViewException $e) {
    echo $e->getMessage() . "<br/>";
}
$modeEdit = 'undefined';
if (isset($_SESSION['customview_edit_mode'])) {
    $modeEdit = $_SESSION['customview_edit_mode'] == "true" ? 'true' : 'false';
}
?>
<script type="text/javascript">
var modeEdit = <?php echo $modeEdit; ?>;
/**
 * Resize widget iframe
 */
function iResize(ifrm, height)
{
    if (height < 150) {
        height = 150;
    }
    jQuery("[name="+ifrm+"]").height(height);
}
</script>
