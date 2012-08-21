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
var $set_displayPOPUP = function() {
        jQuery('.link_popup_volante').mouseenter(func_displayPOPUP);
        jQuery('.link_popup_volante').mouseleave(func_hidePOPUP);
};

var refreshInterval = <?php echo $refreshInterval; ?>;
var _sid = '<?php echo session_id();?>';
var broker = '<?php  echo $oreon->broker->getBroker();?>';
var ajax = new CentreonAjax('./include/home/tacticalOverview/xml/' + broker +'/tacticalOverviewXml.php', './include/home/tacticalOverview/xsl/tacticalOverview.xsl', 'ajaxDiv');
ajax.setCallback($set_displayPOPUP);
ajax.setTime(refreshInterval);
document.onLoad = ajax.start();

// Poppin Function
var func_popupXsltCallback = function() {
        jQuery('.popup_volante .container-load').empty();
        <?php   if ($centreon->user->get_js_effects() > 0) { ?>
        jQuery('.popup_volante').animate({width: jQuery('#popup-container-display').width(), height: jQuery('#popup-container-display').height(),
                             top: (jQuery(window).height() / 2) - (jQuery('#popup-container-display').height() / 2)}, "slow");
        jQuery('#popup-container-display').fadeIn(1000);
        <?php } else { ?>
        jQuery('.popup_volante').css('left', jQuery('.popup_volante').attr('left'));
        jQuery('.popup_volante').css('top', (jQuery(window).height() / 2) - (jQuery('#popup-container-display').height() / 2));
        jQuery('#popup-container-display').show();
        <?php } ?>
};

var func_displayPOPUP = function(event) {
        var position = jQuery('#' + $(this).id).offset();

        jQuery('.popup_volante .container-load').html('<img src="img/misc/ajax-loader.gif" />');
        jQuery('.popup_volante').css('left', position.left + jQuery('#' + $(this).id).width() + 10);
        jQuery('.popup_volante').css('top', (jQuery(window).height() / 2) - (jQuery('.img_volante').height() / 2));
        jQuery('.popup_volante').show();

        var elements = $(this).id.split('-');
        var proc_popup = new Transformation();
        proc_popup.setCallback(func_popupXsltCallback);
        if (elements[0] == "host") {
                proc_popup.setXml("./include/monitoring/status/Services/xml/" + broker + "/makeXMLForOneHost.php?"+'&sid='+_sid+'&host_id='+elements[1]);
                proc_popup.setXslt("./include/monitoring/status/Services/xsl/popupForHost.xsl");
        } else {
                proc_popup.setXml("./include/monitoring/status/Services/xml/" + broker + "/makeXMLForOneService.php?"+'&sid='+_sid+'&svc_id='+ elements[1]);
                proc_popup.setXslt("./include/monitoring/status/Services/xsl/popupForService.xsl");
        }
        jQuery('#popup-container-display').hide();
        proc_popup.transform('popup-container-display');
};

var func_hidePOPUP = function(event) {
        jQuery('.popup_volante .container-load').empty();
        jQuery('#popup-container-display').hide();
        jQuery('.popup_volante').hide();
        jQuery('.popup_volante').css('width', 'auto');
        jQuery('.popup_volante').css('height', 'auto');
};

</script>