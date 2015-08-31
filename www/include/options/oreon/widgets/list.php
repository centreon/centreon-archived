<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

if (!isset($centreon)) {
    exit;
}

require_once $centreon_path . "www/class/centreonWidget.class.php";
require_once $centreon_path . "www/class/centreonUtils.class.php";

$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$widgetObj = new CentreonWidget($centreon, $pearDB);
$labels = array();
$labels['title'] = _("Title");
$labels['description'] = _("Description");
$labels['version'] = _("Version");
$labels['author'] = _("Author");
$labels['actions'] = _("Actions");

$handle = opendir($centreon_path . 'www/widgets/');
$widgets = array();
while (($currentDir = readdir($handle)) != false)	{
    if ($currentDir != "." && $currentDir != ".." && $currentDir != ".SVN" && $currentDir != ".svn" && $currentDir != ".CSV") {
        $configFile = $centreon_path . 'www/widgets/' . $currentDir . '/configs.xml';
        if (is_file($configFile)) {
            $tab = $widgetObj->readConfigFile($configFile);
            $dbTab = $widgetObj->getWidgetInfoByDirectory($currentDir);
            if (isset($dbTab)) {
                $dbTab['is_installed'] = 1;
                if ($dbTab['version'] != $tab['version']) {
                    $dbTab['upgrade'] = 1;
                }
                $widgets[] = $dbTab;
            } else {
                $tab['is_installed'] = 0;
                $tab['install'] = 1;
                $tab['directory'] = $currentDir;
                $widgets[] = $tab;
            }
        }
    }
}

$tpl->assign('widgets', $widgets);
$tpl->assign('labels', $labels);
$tpl->display("list.ihtml");
?>
<script type='text/javascript'>
var installConfirmMsg = '<?php echo _('Would you like to install this widget?');?>';
var uninstallConfirmMsg = '<?php echo _('Are you sure you want to uninstall this widget?');?>';
var upgradeConfirmMsg = '<?php echo _('Would you like to upgrade this widget?');?>';
var p = '<?php echo $p;?>';

jQuery(function() {
	jQuery('.installBtn').click(function() {
		forwardAction(installConfirmMsg, 'install', jQuery(this).parent('td').attr('id'));
	});

	jQuery('.upgradeBtn').click(function() {
		forwardAction(upgradeConfirmMsg, 'upgrade', jQuery(this).parent('td').attr('id'));
	});

	jQuery('.uninstallBtn').click(function() {
		forwardAction(uninstallConfirmMsg, 'uninstall', jQuery(this).parent('td').attr('id'));
	});
});

function forwardAction(confirmMsg, action, data)
{
	var tab = data.split('widget_');
	if (typeof(tab[1]) != 'undefined') {
		var directory = tab[1];
    	if (confirm(confirmMsg)) {
    		jQuery.ajax({
    			type	:	"POST",
    			dataType:	"xml",
    			url 	:	"./include/options/oreon/widgets/action.php",
    			data	:   {
    							action  	:	action,
    							directory	:	directory
    						},
    			success :	function(response) {
    							var result = response.getElementsByTagName('result');
    							if (typeof(result) != 'undefined') {
    								window.location = './main.php?p='+p;
    							}
    						}
    		});
    	}
	}
}
</script>