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
 * SVN : $URL$
 * SVN : $Id$
 *
 */

	if (!isset($centreon)) {
		exit();
	}

	$path = "./include/home/tacticalOverview/";

	$refreshInterval = 10;
    if (isset($centreon->optGen['tactical_refresh_interval'])) {
        $refreshInterval = $centreon->optGen['tactical_refresh_interval'];
    }

	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	/*
	 * Display tactical
	 */
	$tpl->display("tacticalOverview.ihtml");

?>
<script type='text/javascript' src='./class/centreonAjax.js'></script>
<script type='text/javascript'>
var refreshInterval = <?php echo $refreshInterval; ?>;
var _sid = '<?php echo session_id();?>';
var broker = '<?php  echo $oreon->broker->getBroker();?>';
var ajax = new CentreonAjax('./include/home/tacticalOverview/xml/' + broker +'/tacticalOverviewXml.php', './include/home/tacticalOverview/xsl/tacticalOverview.xsl', 'ajaxDiv');
var ajaxOverlay = new CentreonAjaxOverlay();
ajax.setTime(refreshInterval);
setTimeout('ajax.start()', 200);

function showHostOverlay(id, domId) {
	var span = document.getElementById('span_' + domId);
	var xmlPage = "./include/monitoring/status/Services/xml/" + broker + "/makeXMLForOneHost.php?"+'&sid='+_sid+'&host_id='+id;
	var xslPage = "./include/monitoring/status/Services/xsl/popupForHost.xsl";
	ajaxOverlay.show(xmlPage, xslPage, 'span_' + domId);
}

function showServiceOverlay(id) {
	var span = document.getElementById('span_'+id);
	var xmlPage = "./include/monitoring/status/Services/xml/" + broker + "/makeXMLForOneService.php?"+'&sid='+_sid+'&svc_id='+id;
	var xslPage = "./include/monitoring/status/Services/xsl/popupForService.xsl";
	ajaxOverlay.show(xmlPage, xslPage, 'span_'+id);
}

function hideOverlay(id) {
   	ajaxOverlay.hide('span_'+id);
}

</script>