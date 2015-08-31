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
var popup_counter = {};

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

jQuery(function() {
    ajax.start();
});
 
// Poppin Function
var func_popupXsltCallback = function(trans_obj) {
        var target_element = trans_obj.getTargetElement();
        if (popup_counter[target_element] == 0) {
                return ;
        }

        jQuery('.popup_volante .container-load').empty();
        <?php   if ($centreon->user->get_js_effects() > 0) { ?>
        jQuery('.popup_volante').stop(true, true).animate({width: jQuery('#' + target_element).width(), height: jQuery('#' + target_element).height(),
                             top: (jQuery(window).height() / 2) - (jQuery('#' + target_element).height() / 2)}, 25);
        jQuery('#' + target_element).stop(true, true).fadeIn(1000);
        <?php } else { ?>
        jQuery('.popup_volante').css('left', jQuery('.popup_volante').attr('left'));
        jQuery('.popup_volante').css('top', (jQuery(window).height() / 2) - (jQuery('#' + target_element).height() / 2));
        jQuery('#' + target_element).show();
        <?php } ?>
};

var func_displayPOPUP = function(event) {
        var position = jQuery('#' + $(this).id).offset();
        if (jQuery('#popup-container-display-' + $(this).id).length == 0) {
            popup_counter['popup-container-display-' + $(this).id] = 1;
            jQuery('.popup_volante').append('<div id="popup-container-display-' + $(this).id + '" style="display: none"></div>');
        } else {
            popup_counter['popup-container-display-' + $(this).id] += 1;
        }
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
        proc_popup.transform('popup-container-display-' + $(this).id);
};

var func_hidePOPUP = function(event) {
        popup_counter['popup-container-display-' + $(this).id] -= 1;
        jQuery('.popup_volante .container-load').empty();
        jQuery('#popup-container-display-' + $(this).id).hide();
        jQuery('.popup_volante').hide();
        jQuery('.popup_volante').css('width', 'auto');
        jQuery('.popup_volante').css('height', 'auto');
};

</script>