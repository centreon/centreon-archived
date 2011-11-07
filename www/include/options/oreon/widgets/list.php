<?php
/**
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