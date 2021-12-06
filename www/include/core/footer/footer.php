<?php
/*
 * Copyright 2005-2018 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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

require_once("./class/centreonData.class.php");
include_once("./include/core/menu/templates/webpack.tpl");

if (!$min) {
    ?>
    <!-- Footer -->
    <div class="fullscreen-icon" title="Fullscreen" onclick="myToggleAll(0,true);">
        <img class="ico-16" id="fullscreenIcon" alt="FullScreen" src="./img/icons/fullscreen.png">
    </div>
    <div id="clearfooter" style="height: 1px;"></div>
    <div id="footer">
        <table cellpadding='0' cellspacing='0' width='100%' border='0' id="tfooter">
            <tr>
                <td>
                    <?php print _("Generated in ");
                    $time_end = microtime_float();
                    $now = $time_end - $time_start;
                    print round($now, 3) . " " . _("seconds"); ?>
                </td>
                <td align='center' class='copyRight'>
                    <a href='https://documentation.centreon.com' title='{$Documentation}' target='_blank'><?php echo _("Documentation"); ?></a> |
                    <a href="https://support.centreon.com" title="Centreon Support Access" target='_blank'>Centreon Support</a> |
                    <a href="https://www.centreon.com" title='Centreon Services Overview' target='_blank'>Centreon</a> |
                    <a href="https://github.com/centreon/centreon.git" title='Follow and Fork us on Github' target='_blank'>Github Project</a> |
                    <a href="https://centreon.github.io" title='Give us your feedback' target='_blank'>Slack</a>
                    <?php if (!empty($centreon->optGen["centreon_support_email"])) { ?> |
                        <a href='mailto:<?php print $oreon->optGen["centreon_support_email"]; ?>'><?php print _("Help Desk"); ?></a>
                    <?php } ?>
                </td>
                <td>Copyright &copy; 2005 - <?php echo date("Y"); ?></td>
            </tr>
        </table>
    </div>
    <?php
}
?>

<script type="text/javascript">
    // Centreon ToolTips
    var centreonTooltip = new CentreonToolTip();
    centreonTooltip.setTitle('<?php echo _("Help"); ?>');
    centreonTooltip.render();

    function myToggleAll(duration, toggle) {
        if (toggle) {
            //var i = document.getElementsByTagName("html")[0];
            var i = document.documentElement;
            if (
                document.fullscreenElement ||
                document.webkitFullscreenElement ||
                document.mozFullScreenElement ||
                document.msFullscreenElement
            ) {
                jQuery(
                  "#actionBar, .pathWayBracket, .imgPathWay, .pathWay, hr, #QuickSearch, #menu1_bgcolor, " +
                  "#footer, #menu_1, #Tmenu , #menu_2, #menu_3, #header, .toHideInFullscreen"
                ).removeClass('tohide');
                jQuery("#fullscreenIcon").attr("src", "./img/icons/fullscreen.png");
                jQuery('#contener').css({
                    'height': 'calc(100% - 170px)'
                });
                jQuery('#Tcontener').css({
                    'margin-bottom': '0px'
                });
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                } else if (document.msExitFullscreen) {
                    document.msExitFullscreen();
                } else if (document.mozCancelFullScreen) {
                    document.mozCancelFullScreen();
                } else if (document.webkitExitFullscreen) {
                    document.webkitExitFullscreen();
                }
            } else {
                jQuery(
                  "#actionBar, .pathWayBracket, .imgPathWay, .pathWay, hr, #QuickSearch, #menu1_bgcolor," +
                  " #footer, #menu_1, #Tmenu , #menu_2, #menu_3, #header, .toHideInFullscreen"
                ).addClass('tohide');
                jQuery("#fullscreenIcon").attr("src", "./img/icons/fullscreen_off.png");
                jQuery('#contener').css({
                    'height': '100%'
                });
                jQuery('#Tcontener').css({
                    'margin-bottom': '0px'
                });
                // go full-screen
                if (i.requestFullscreen) {
                    i.requestFullscreen();
                } else if (i.webkitRequestFullscreen) {
                    i.webkitRequestFullscreen();
                } else if (i.mozRequestFullScreen) {
                    i.mozRequestFullScreen();
                } else if (i.msRequestFullscreen) {
                    i.msRequestFullscreen();
                }
            }
        }
    }
    document.addEventListener('webkitfullscreenchange', exitHandler, false);
    document.addEventListener('mozfullscreenchange', exitHandler, false);
    document.addEventListener('fullscreenchange', exitHandler, false);
    document.addEventListener('MSFullscreenChange', exitHandler, false);

    function exitHandler() {
        var state = document.fullScreen ||
            document.mozFullScreen ||
            document.webkitIsFullScreen ||
            document.msFullscreenElement;
        var event = state ? 'FullscreenOn' : 'FullscreenOff';
        if (event === 'FullscreenOff') {
            jQuery("#fullscreenIcon").attr("src", "./img/icons/fullscreen.png");
            jQuery(
              "#actionBar, .pathWayBracket, .imgPathWay, .pathWay, hr, #QuickSearch, #menu1_bgcolor, " +
              "#footer, #menu_1, #Tmenu , #menu_2, #menu_3, #header, .toHideInFullscreen"
            ).removeClass('tohide');
        }
    }

</script>
<?php

if ((isset($_GET["mini"]) && $_GET["mini"] == 1) ||
    (isset($_SESSION['fullScreen']) &&
        isset($_SESSION['fullScreen']['value']) &&
        $_SESSION['fullScreen']['value']
    )
) {
    ?>
    <script type="text/javascript">
        myToggleAll(0, false);
    </script>
<?php } else {
    if (!$centreon->user->showDiv("footer")) {
        ?>
        <script type="text/javascript">new Effect.toggle('footer', 'blind', {duration: 0});</script> <?php
    }
}

/*
 * Create Data Flow
 */
$cdata = CentreonData::getInstance();
$jsdata = $cdata->getJsData();
foreach ($jsdata as $k => $val) {
    echo "<span class=\"data hide\" id=\"" . $k . "\" data-" . $k . "=\"" . $val . "\"></span>";
}

?>

<script type='text/javascript'>
    jQuery(function() {
        initWholePage();

        // convert URIs to links
        jQuery(".containsURI").each(function() {
            jQuery(this).linkify();
        });
    });

    /*
     * Init whole page
     */
    function initWholePage() {
        setQuickSearchPosition();
        jQuery().centreon_notify({
            refresh_rate: <?php echo($centreon->optGen['AjaxTimeReloadMonitoring'] * 1000);?>
        });
    }

    /*
     * set quick search position
     */
    function setQuickSearchPosition() {
        if (jQuery('QuickSearch')) {
            if (jQuery('header').is(':visible')) {
                jQuery('QuickSearch').css({ top: '86px' });
            } else {
                jQuery('QuickSearch').css({ top: '3px' });
            }
        }
        jQuery(".timepicker").timepicker();
        jQuery(".datepicker").datepicker();
    }

    <?php
    $featureToAsk = $centreonFeature->toAsk($centreon->user->get_id());
    if (count($featureToAsk) === 1) {
        ?>
        var testingFeature = jQuery('<div/>')
            .html(
                '<h3>Feature testing</h3>' +
                '<div style="margin: 2px;">Would you like to activate the feature flipping: <?php echo $featureToAsk[0]['name']; ?>  ?</div>' +
                '<div style="margin: 2px; font-weight: bold;">Description: </div>' +
                '<div style="margin: 2px;"> <?php echo $featureToAsk[0]['description']; ?>.</div>' +
                '<div style="margin: 2px;">Please, give us your feedback on <a href="https://centreon.github.io">Slack</a> ' +
                  'or <a href="https://github.com/centreon/centreon/issues">Github</a>.</div>' +
                '<div style="margin: 2px; font-weight: bold;">Legacy version: </div>' +
                '<div style="margin: 2px;">You can switch back to the legacy version in my account page. ' +
                '<div style="margin-top: 8px; text-align: center;">' +
                    '<button class="btc bt_success" onclick="featureEnable()" id="btcActivateFf" >Activate</button>' +
                    '&nbsp;<button class="btc bt_default" onclick="featureDisable()" id="btcDisableFf">No</button>' +
                '</div>'
            )
            .css('position', 'relative');

        function validateFeature(name, version, enabled) {
            jQuery.ajax({
                url: './api/internal.php?object=centreon_featuretesting&action=enabled',
                type: 'POST',
                data: JSON.stringify({
                    name: name,
                    version: version,
                    enabled: enabled
                }),
                dataType: 'json',
                success: function () {
                    location.reload()
                }
            })
        }

        function featureEnable() {
            validateFeature(
            "<?php echo $featureToAsk[0]['name']; ?>",
            "<?php echo $featureToAsk[0]['version']; ?>",
            true
            );
            testingFeature.centreonPopin("close");
        }

        function featureDisable() {
            validateFeature(
            "<?php echo $featureToAsk[0]['name']; ?>",
            "<?php echo $featureToAsk[0]['version']; ?>",
            true
            );
            testingFeature.centreonPopin("close");
        }

        testingFeature.centreonPopin({
            isModal: true,
            open: true
        });
        <?php
    }
?>
</script>
</body>
</html>
<?php

/*
 * Close all DB handler
 */
if (isset($pearDB) && is_object($pearDB)) {
    $pearDB = null;
}
if (isset($pearDBO) && is_object($pearDBO)) {
    $pearDBO = null;
}
