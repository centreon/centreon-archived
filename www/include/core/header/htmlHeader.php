<?php
/*
 * Copyright 2005-2016 Centreon
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

print "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";

?>

<!DOCTYPE html>
<html lang="<?php echo $centreon->user->lang; ?>">
<head>
    <title>Centreon - IT & Network Monitoring</title>
    <link rel="shortcut icon" href="./img/favicon.ico"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="Generator" content="Centreon - Copyright (C) 2005 - 2017 Open Source Matters. All rights reserved."/>
    <meta name="robots" content="index, nofollow"/>

    <link href="./include/common/javascript/jquery/plugins/jpaginator/jPaginator.css" rel="stylesheet" type="text/css"/>
    <link href="./Themes/Centreon-2/style.css" rel="stylesheet" type="text/css"/>
    <link href="./Themes/Centreon-2/responsive-style.css" rel="stylesheet" type="text/css"/>
    <link href="./Themes/Centreon-2/<?php echo $colorfile; ?>" rel="stylesheet" type="text/css" />
    <link href="./include/common/javascript/modalbox.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="./Themes/Centreon-2/Modalbox/<?php echo $colorfile; ?>" rel="stylesheet" type="text/css" media="screen"/>
    <link href="./include/common/javascript/jquery/plugins/timepicker/jquery.ui.timepicker.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="./include/common/javascript/jquery/plugins/select2/css/select2.css" rel="stylesheet" type="text/css" media="screen"/>
    <link href="./Themes/Centreon-2/jquery-ui/jquery-ui.css" rel="stylesheet" type="text/css"/>
    <link href="./Themes/Centreon-2/jquery-ui/jquery-ui-centreon.css" rel="stylesheet" type="text/css"/>
    <link href="./include/common/javascript/jquery/plugins/colorbox/colorbox.css" rel="stylesheet" type="text/css"/>
    <link rel="stylesheet" type="text/css" href="./include/configuration/configCentreonBroker/wizard/css/style.css" />
    <link rel="stylesheet" type="text/css" href="./include/common/javascript/jquery/plugins/qtip/jquery-qtip.css" />
    <?php

    // == Declare CSS for modules
    foreach ($centreon->modules as $module_name => $infos) {
        if (file_exists(_CENTREON_PATH_."www/modules/".$module_name."/static/css/styles.css")) {
            print "<link href='./modules/".$module_name."/static/css/styles.css' rel='stylesheet' type='text/css' />\n";
        }
    }

    ?>
    <script type="text/javascript" src="./include/common/javascript/scriptaculous/prototype.js"></script>
    <?php if (!isset($_REQUEST['iframe']) || (isset($_REQUEST['iframe']) && $_REQUEST['iframe'] != 1)) { ?>
        <script type="text/javascript" src="./include/common/javascript/scriptaculous/scriptaculous.js?load=effects,dragdrop"></script>
        <script type="text/javascript" src="./include/common/javascript/modalbox.js"></script>
        <script type="text/javascript" src="./include/common/javascript/jquery/jquery.min.js"></script>
        <script type="text/javascript" src="./include/common/javascript/jquery/plugins/select2/js/select2.full.min.js"></script>
        <script type="text/javascript" src="./include/common/javascript/centreon/centreon-select2.js"></script>
        <script type="text/javascript" src="./include/common/javascript/jquery/jquery-ui.js"></script>
        <script type="text/javascript">jQuery.noConflict();</script>
        <script type="text/javascript" src="./include/common/javascript/jquery/plugins/colorbox/jquery.colorbox-min.js"></script>
        <script type="text/javascript" src="./include/common/javascript/jquery/plugins/jeditable/jquery.jeditable-min.js"></script>
        <script type="text/javascript" src="./include/common/javascript/jquery/plugins/timepicker/jquery.ui.timepicker.js"></script>
        <script type="text/javascript" src="./include/common/javascript/jquery/plugins/noty/jquery.noty.js"></script>
        <script type="text/javascript" src="./include/common/javascript/jquery/plugins/noty/themes/default.js"></script>
        <script type="text/javascript" src="./include/common/javascript/jquery/plugins/noty/layouts/bottomRight.js"></script>
        <script type="text/javascript" src="./include/common/javascript/jquery/plugins/buzz/buzz.min.js"></script>
        <script type="text/javascript" src="./include/common/javascript/centreon/notifier.js"></script>
        <script type="text/javascript" src="./include/common/javascript/centreon/multiselectResizer.js"></script>
        <script type="text/javascript" src="./include/common/javascript/centreon/popin.js"></script>
        <script type="text/javascript" src="./include/common/javascript/jquery/plugins/jquery.nicescroll.min.js"></script>
        <script type="text/javascript" src="./include/common/javascript/jquery/plugins/jpaginator/jPaginator.js"></script>
        <script type='text/javascript' src='./include/common/javascript/changetab.js'></script>
    <?php } ?>
    <script type="text/javascript" src="./class/centreonToolTip.js"></script>
    <script type="text/javascript" src="./include/common/javascript/keepAlive.js"></script>
    <?php

    /*
     * Add Javascript for NDO status Counter
     */
    if ($centreon->user->access->admin == 0) {
        $tabActionACL = $centreon->user->access->getActions();
        if ($min != 1 && (isset($tabActionACL["top_counter"]) || isset($tabActionACL["poller_stats"]))) {
            print "<script type=\"text/javascript\" src=\"./include/common/javascript/topCounterStatus/ajaxStatusCounter.js\"></script>\n";
        }
        unset($tabActionACL);
    } else {
        if ($min != 1) {
            print "<script type=\"text/javascript\" src=\"./include/common/javascript/topCounterStatus/ajaxStatusCounter.js\"></script>\n";
        }
    }

    global $search, $search_service;

    $searchStr = "";
    if (isset($_GET["search"])) {
        $searchStr .= "search_host=".htmlentities($_GET["search"], ENT_QUOTES, "UTF-8");
    }
    if (isset($centreon->historySearch[$url]) && !isset($_GET["search"])) {
        $searchStr .= "search_host=".$centreon->historySearch[$url];
    }

    $searchStrSVC = "";
    if (isset($_GET["search_service"])) {
        $searchStrSVC = "search_service=".htmlentities($_GET["search_service"], ENT_QUOTES, "UTF-8");
        if ($searchStr == "") {
            $searchStrSVC = "&".$searchStrSVC;
        }
        $search_service = htmlentities($_GET["search_service"], ENT_QUOTES, "UTF-8");
    } elseif (isset($centreon->historySearchService[$url]) && !isset($_GET["search_service"])) {
        $search_service = $centreon->historySearchService[$url];
        $searchStr .= "search_service=".$centreon->historySearchService[$url];
    }

    /*
     * include javascript
     */
    $res = null;
    $DBRESULT = $pearDB->prepare("SELECT DISTINCT PathName_js, init FROM topology_JS WHERE id_page = ? AND (o = ? OR o IS NULL)");
    $DBRESULT = $pearDB->execute($DBRESULT, array($p, $o));
    while ($topology_js = $DBRESULT->fetchRow()) {
        if ($topology_js['PathName_js'] != "./include/common/javascript/ajaxMonitoring.js") {
            if ($topology_js['PathName_js'] != "") {
                echo "<script type='text/javascript' src='".$topology_js['PathName_js']."'></script>\n";
            }
        }
    }
    $DBRESULT->free();

    /*
     * init javascript
     */

    $sid = session_id();

    $tS = $centreon->optGen["AjaxTimeReloadStatistic"] * 1000;
    $tM = $centreon->optGen["AjaxTimeReloadMonitoring"] * 1000;

    ?>
    <script type='text/javascript'>
        <?php
        require_once("./include/core/autologout/autologout.php");
        ?>
        jQuery(function () {
            <?php

            if ($centreon->user->access->admin == 0) {
                $tabActionACL = $centreon->user->access->getActions();
                if ($min != 1 && (isset($tabActionACL["top_counter"]) || isset($tabActionACL["poller_stats"]))) {
                    print "setTimeout('reloadStatusCounter($tS)', 0);\n";
                }
                unset($tabActionACL);
            } else {
                if ($min != 1) {
                    print "setTimeout('reloadStatusCounter($tS)', 0);\n";
                }
            }

            $res = null;
            $DBRESULT = $pearDB->query("SELECT DISTINCT PathName_js, init FROM topology_JS WHERE id_page = '".$p."' AND (o = '" . $o . "' OR o IS NULL)");
            while ($topology_js = $DBRESULT->fetchRow()) {
                if ($topology_js['init'] == "initM") {
                    if ($o != "hd" && $o != "svcd") {
                        $obis = $o;
                        if (isset($_GET["problem"])) {
                            $obis .= '_pb';
                        }
                        if (isset($_GET["acknowledge"])) {
                            $obis .= '_ack_' . $_GET["acknowledge"];
                        }
                        print "\tsetTimeout('initM($tM, \"$sid\", \"$obis\")', 0);";
                    }
                } elseif ($topology_js['init']) {
                    echo "if (typeof ".$topology_js['init']." == 'function') {";
                    echo $topology_js['init'] ."();";
                    echo "}";
                }
            }
            ?>
            check_session();
        });
    </script>
    <script src="./include/common/javascript/xslt.js" type="text/javascript"></script>
</head>
<body>
<?php if (!isset($_REQUEST['iframe']) || (isset($_REQUEST['iframe']) && $_REQUEST['iframe'] != 1)) { ?>
    <script type="text/javascript" src="./lib/wz_tooltip/wz_tooltip.js"></script>
<?php } ?>
