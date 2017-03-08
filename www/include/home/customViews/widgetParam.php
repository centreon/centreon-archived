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

if (!isset($centreon)) {
    exit;
}

require_once _CENTREON_PATH_ . "www/class/centreonCustomView.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonWidget.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Boolean.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Hidden.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/List.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Password.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Range.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Text.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Compare.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Sort.class.php";
require_once _CENTREON_PATH_ . "www/class/centreonWidget/Params/Date.class.php";

$db = new CentreonDB();
$viewObj = new CentreonCustomView($centreon, $db);
$widgetObj = new CentreonWidget($centreon, $db);
$title = "";
$defaultTab = array();
if (isset($_REQUEST['view_id']) && $_REQUEST['view_id'] && isset($_REQUEST['widget_id']) && $_REQUEST['widget_id']) {
    $viewId = $_REQUEST['view_id'];
    $widgetId = $_REQUEST['widget_id'];
    $action = "setPreferences";

    $widgetTitle = $widgetObj->getWidgetTitle($widgetId);
    if ($widgetTitle != '') {
        $title = sprintf(_("Widget Preferences for %s"), $widgetTitle);
    } else {
        $title = _("Widget Preferences");
    }

    $info = $widgetObj->getWidgetDirectory($widgetObj->getWidgetType($widgetId));
    $title .= " [".$info."]";
    

    $defaultTab['custom_view_id'] = $viewId;
    $defaultTab['widget_id'] = $widgetId;
    $defaultTab['action'] = $action;
    $url = $widgetObj->getUrl($widgetId);
} else {
    exit;
}

/**
 * Smarty
 */
$path = "./include/home/customViews/";
$template = new Smarty();
$template = initSmartyTpl($path, $template, "./");

/**
 * Quickform
 */
require_once 'HTML/QuickForm.php';
require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

$form = new HTML_QuickForm('Form', 'post', "?p=103");
$form->addElement('header', 'title', $title);
$form->addElement('header', 'information', _("General Information"));

/* Prepare list of installed modules and have widget connectors */
$loadConnectorPaths = array();
/* Add core path */
$loadConnectorPaths[] = _CENTREON_PATH_ . "www/class/centreonWidget/Params/Connector";
$query = 'SELECT name FROM modules_informations ORDER BY name';
$res = $db->query($query);
while ($module = $res->fetchRow()) {
    $dirPath = _CENTREON_PATH_ . 'www/modules/' . $module['name'] . '/widgets/Params/Connector';
    if (is_dir($dirPath)) {
        $loadConnectorPaths[] = $dirPath;
    }
}

try {
    $permission = $viewObj->checkPermission($viewId);
    $params = $widgetObj->getParamsFromWidgetId($widgetId, $permission);
    foreach ($params as $paramId => $param) {
        if ($param['is_connector']) {
            $paramClassFound = false;
            foreach ($loadConnectorPaths as $path) {
                $filename = $path . '/' . ucfirst($param['ft_typename'].".class.php");
                if (is_file($filename)) {
                    require_once $filename;
                    $paramClassFound = true;
                    break;
                }
            }
            if (false === $paramClassFound) {
                throw new Exception('No connector found for ' . $param['ft_typename']);
            }
            $className = "CentreonWidgetParamsConnector".ucfirst($param['ft_typename']);
        } else {
            $className = "CentreonWidgetParams".ucfirst($param['ft_typename']);
        }
        if (class_exists($className)) {
            $currentParam = call_user_func(array($className, 'factory'), $db, $form, $className, $centreon->user->user_id);
            $param['custom_view_id'] = $viewId;
            $param['widget_id'] = $widgetId;
            $currentParam->init($param);
            $currentParam->setValue($param);
            $params[$paramId]['trigger'] = $currentParam->getTrigger();
            $element = $currentParam->getElement();
        } else {
            throw new Exception('No class name found');
        }
    }
} catch (Exception $e) {
    echo $e->getMessage() . "<br/>";
}

$template->assign('params', $params);

/**
 * Submit button
 */
$form->addElement('button', 'submit', _("Apply"), array("class" => "btc bt_success", "onClick" => "submitData();"));
$form->addElement('reset', 'reset', _("Reset"), array("class" => "btc bt_default"));
$form->addElement('hidden', 'custom_view_id');
$form->addElement('hidden', 'widget_id');
$form->addElement('hidden', 'action');
$form->setDefaults($defaultTab);

/**
 * Renderer
 */
$renderer = new HTML_QuickForm_Renderer_ArraySmarty($template, true);
$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
$form->accept($renderer);
$template->assign('form', $renderer->toArray());
$template->display("widgetParam.ihtml");
?>
<script type="text/javascript">
var viewId = <?php echo $viewId;?>;
var widgetId = <?php echo $widgetId;?>;
var widgetUrl = '<?php echo $url;?>';

jQuery(function()
{
    jQuery("input[type=text]").keypress(function(e) {
                                            var code = null;
                                            code =  (e.keyCode ? e.keyCode : e.which);
                                            return (code == 13) ? false : true;
                                        } );
    setDatePicker();
});

function submitData()
{
    jQuery.ajax({
            type    :   "POST",
            dataType:   "xml",
            url     :   "./include/home/customViews/action.php",
            data    :   jQuery("#Form").serialize(),
            success :   function(response) {
                            var view = response.getElementsByTagName('custom_view_id');
                            var error = response.getElementsByTagName('error');
                            if (typeof(view) != 'undefined') {
                                var viewId = view.item(0).firstChild.data;
                                parent.jQuery("[name=widget_" + viewId +  "_" + widgetId + "]").attr('src', widgetUrl + '?widgetId='+ widgetId);
                                parent.jQuery.colorbox.close();
                            } else if (typeof(error) != 'undefined') {
                                var errorMsg = error.item(0).firstChild.data;
                            }
                        }
    });
}

function setDatePicker()
{
    jQuery(".datepicker").datepicker({
                            defaultDate:    "+1w",
                            changeMonth:    true
                           });
}

/**
 * Load target select box with values that will be retrieved by trigger source
 *
 * @param string triggerSource
 * @param string targetId
 * @param string triggerValue
 * @return void
 */
function loadFromTrigger(triggerSource, targetId, triggerValue)
{
    jQuery.ajax({
        type    :   "POST",
        dataType:   "xml",
        url     :   triggerSource,
        data    :   { data: triggerValue } ,
        success :   function(response) {
                            jQuery("[name=param_"+targetId+"]").find('option').remove().end();
                            jQuery(response).find('option').each(function() {
                                jQuery("[name=param_"+targetId+"]").append(new Option(jQuery(this).find('label').text(),
                                                                                      triggerValue + '-' + jQuery(this).find('id').text(), true, true));
                            });
                    }
});
}
</script>
