<?php

/*
 * Copyright 2005-2021 Centreon
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
    exit();
}

// generate version URI parameter to clean css cache at each new version
$versionParam = isset($centreon->informations) && isset($centreon->informations['version'])
    ? '?version=' . $centreon->informations['version']
    : '';

print "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";

$variablesThemeCSS  = null;
$userId = (int) $centreon->user->user_id;
$statement = $pearDB->prepare('SELECT contact_theme FROM contact WHERE contact_id = :contactId');
$statement->bindValue(':contactId', $userId, \PDO::PARAM_INT);
$statement->execute();
if ($result = $statement->fetch(\PDO::FETCH_ASSOC)) {
    switch ($result['contact_theme']) {
        case 'light':
            $variablesThemeCSS = null;
            break;
        case 'dark':
            $variablesThemeCSS = "Centreon-Dark";
            break;
        default:
            throw new \Exception('Unknown contact theme : ' . $result['contact_theme']);
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $centreon->user->lang; ?>">
    <title>Centreon - IT & Network Monitoring</title>
    <link rel="shortcut icon" href="./img/favicon.ico"/>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="Generator" content="Centreon - Copyright (C) 2005 - 2021 Open Source Matters. All rights reserved."/>
    <meta name="robots" content="index, nofollow"/>

    <?php if (isset($isMobile) && $isMobile) : ?>
    <link href="./Themes/Generic-theme/MobileMenu/css/material_icons.css" rel="stylesheet" type="text/css"/>
    <link href="./Themes/Generic-theme/MobileMenu/css/menu.css" rel="stylesheet" type="text/css"/>
    <?php endif; ?>

    <link href="./include/common/javascript/jquery/plugins/jpaginator/jPaginator.css" rel="stylesheet" type="text/css"/>

    <!-- Theme selection -->
    <link
        href="./Themes/Generic-theme/style.css<?php echo $versionParam; ?>"
        rel="stylesheet"
        type="text/css"
    />
    <link
        href="./Themes/Generic-theme/centreon-loading.css<?php echo $versionParam; ?>"
        rel="stylesheet"
        type="text/css"
    />
    <link
        href="./Themes/Generic-theme/responsive-style.css<?php echo $versionParam; ?>"
        rel="stylesheet"
        type="text/css"
    />
    <link
        href="./Themes/Generic-theme/color.css<?php echo $versionParam; ?>"
        rel="stylesheet"
        type="text/css"
    />
    <link
        href="./Themes/Generic-theme/jquery-ui/jquery-ui.css<?php echo $versionParam; ?>"
        rel="stylesheet"
        type="text/css"
    />
    <link
        href="./Themes/Generic-theme/jquery-ui/jquery-ui-centreon.css<?php echo $versionParam; ?>"
        rel="stylesheet"
        type="text/css"
    />

    <link
        href="./include/common/javascript/jquery/plugins/timepicker/jquery.ui.timepicker.css"
        rel="stylesheet"
        type="text/css"
        media="screen"
    />
    <link
        href="./include/common/javascript/jquery/plugins/select2/css/select2.css"
        rel="stylesheet"
        type="text/css"
        media="screen"
    />
    <link href="./include/common/javascript/jquery/plugins/colorbox/colorbox.css" rel="stylesheet" type="text/css"/>
    <link href="./include/common/javascript/jquery/plugins/qtip/jquery-qtip.css" rel="stylesheet" type="text/css"/>

    <!-- graph css -->
    <link href="./include/common/javascript/charts/c3.min.css" type="text/css" rel="stylesheet" />
    <link href="./include/views/graphs/javascript/centreon-status-chart.css" type="text/css" rel="stylesheet" />
    <link
            href="./Themes/Generic-theme/variables.css"
            rel="stylesheet"
            type="text/css"
    />
        <?php
    // Override variables CSS
        if($variablesThemeCSS !==null)
        {
            print "<link "
                . "href='./Themes/" . $variablesThemeCSS . "/variables.css' "
                . "rel='stylesheet' type='text/css' "
                . "/>\n";
        }
        // == Declare CSS for modules
        foreach ($centreon->modules as $moduleName => $infos) {
            if (file_exists(__DIR__ . "/../../../www/modules/" . $moduleName . "/static/css/styles.css")) {
                print "<link "
                    . "href='./modules/" . $moduleName . "/static/css/styles.css' "
                    . "rel='stylesheet' type='text/css' "
                    . "/>\n";
            }
        }

        if (!isset($_REQUEST['iframe']) || (isset($_REQUEST['iframe']) && $_REQUEST['iframe'] != 1)) {
            ?>
    <script type="text/javascript" src="./include/common/javascript/jquery/jquery.min.js"></script>
    <script type="text/javascript" src="./include/common/javascript/jquery/plugins/toggleClick/jquery.toggleClick.js">
    </script>
    <script type="text/javascript" src="./include/common/javascript/jquery/plugins/select2/js/select2.full.min.js">
    </script>
    <script type="text/javascript" src="./include/common/javascript/centreon/centreon-select2.js"></script>
    <script type="text/javascript" src="./include/common/javascript/jquery/jquery-ui.js"></script>
    <script type="text/javascript" src="./include/common/javascript/jquery/jquery-ui-tabs-rotate.js"></script>
    <script type="text/javascript" src="./include/common/javascript/jquery/plugins/colorbox/jquery.colorbox-min.js">
    </script>
    <script type="text/javascript" src="./include/common/javascript/jquery/plugins/jeditable/jquery.jeditable-min.js">
    </script>
    <script type="text/javascript" src="./include/common/javascript/jquery/plugins/timepicker/jquery.ui.timepicker.js">
    </script>
    <script type="text/javascript" src="./include/common/javascript/jquery/plugins/noty/jquery.noty.js"></script>
    <script type="text/javascript" src="./include/common/javascript/jquery/plugins/noty/themes/default.js"></script>
    <script type="text/javascript" src="./include/common/javascript/jquery/plugins/noty/layouts/bottomRight.js">
    </script>
    <script type="text/javascript" src="./include/common/javascript/jquery/plugins/buzz/buzz.min.js"></script>
    <script type='text/javascript' src='./include/common/javascript/visibility.min.js'></script>
    <script type="text/javascript" src="./include/common/javascript/centreon/notifier.js"></script>
    <script type="text/javascript" src="./include/common/javascript/centreon/multiselectResizer.js"></script>
    <script type="text/javascript" src="./include/common/javascript/centreon/popin.js"></script>
    <script type="text/javascript" src="./include/common/javascript/jquery/plugins/jquery.nicescroll.min.js"></script>
    <script type="text/javascript" src="./include/common/javascript/jquery/plugins/jpaginator/jPaginator.js"></script>
    <script type="text/javascript" src="./include/common/javascript/clipboard.min.js"></script>
    <script type='text/javascript' src='./include/common/javascript/changetab.js'></script>
    <script type='text/javascript' src='./include/common/javascript/linkify/linkify.min.js'></script>
    <script type='text/javascript' src='./include/common/javascript/linkify/linkify-jquery.min.js'></script>

            <?php
        }
        ?>

    <script type="text/javascript" src="./class/centreonToolTip.js"></script>

    <!-- graph js -->
    <script src="./include/common/javascript/charts/d3.min.js"></script>
    <script src="./include/common/javascript/charts/c3.min.js"></script>
    <script src="./include/common/javascript/charts/d3-timeline.js"></script>
    <script src="./include/views/graphs/javascript/centreon-graph.js"></script>
    <script src="./include/views/graphs/javascript/centreon-c3.js"></script>
    <script src="./include/common/javascript/numeral.min.js"></script>
    <script src="./include/views/graphs/javascript/centreon-status-chart.js"></script>
    <script src="./include/common/javascript/moment-with-locales.min.2.21.js"></script>
    <script src="./include/common/javascript/moment-timezone-with-data.min.js"></script>

    <?php if (isset($isMobile) && $isMobile) : ?>
    <script type="text/javascript">
      var text_back = '<?= gettext('Back') ?>'
    </script>
    <script src="./Themes/Generic-theme/MobileMenu/js/menu.js"></script>
    <?php endif; ?>
    <?php

    global $search, $search_service;

    $searchStr = "";
    if (isset($_GET["search"])) {
        $searchStr .= "search_host=" . htmlentities($_GET["search"], ENT_QUOTES, "UTF-8");
    }
    if (isset($centreon->historySearch[$url]) && !isset($_GET["search"])) {
        if (!is_array($centreon->historySearch[$url])) {
            $searchStr .= "search_host=" . $centreon->historySearch[$url];
        } elseif (isset($centreon->historySearch[$url]['search'])) {
            $searchStr .= "search_host=" . $centreon->historySearch[$url]['search'];
        }
    }

    $searchStrSVC = "";
    if (isset($_GET["search_service"])) {
        $searchStrSVC = "search_service=" . htmlentities($_GET["search_service"], ENT_QUOTES, "UTF-8");
        if ($searchStr == "") {
            $searchStrSVC = "&" . $searchStrSVC;
        }
        $search_service = htmlentities($_GET["search_service"], ENT_QUOTES, "UTF-8");
    } elseif (isset($centreon->historySearchService[$url]) && !isset($_GET["search_service"])) {
        $search_service = $centreon->historySearchService[$url];
        $searchStr .= "search_service=" . $centreon->historySearchService[$url];
    }

    /*
     * include javascript
     */
    $res = null;
    $query = "SELECT DISTINCT PathName_js, init FROM topology_JS WHERE id_page = ? AND (o = ? OR o IS NULL)";
    $sth = $pearDB->prepare($query);
    $sth->execute(array($p, $o));
    while ($topology_js = $sth->fetch()) {
        if ($topology_js['PathName_js'] != "./include/common/javascript/ajaxMonitoring.js") {
            if ($topology_js['PathName_js'] != "") {
                echo "<script type='text/javascript' src='" . $topology_js['PathName_js'] . "'></script>\n";
            }
        }
    }
    $DBRESULT = null;

    /*
     * init javascript
     */

    $sid = session_id();

    $tS = $centreon->optGen["AjaxTimeReloadStatistic"] * 1000;
    $tM = $centreon->optGen["AjaxTimeReloadMonitoring"] * 1000;

    ?>
    <script src="./include/common/javascript/centreon/dateMoment.js" type="text/javascript"></script>
    <script src="./include/common/javascript/centreon/centreon-localStorage.js" type="text/javascript"></script>
    <script src="./include/common/javascript/datepicker/localizedDatepicker.js"></script>
    <script type='text/javascript'>
        jQuery(function () {
            <?php
            $res = null;
            $query = "SELECT DISTINCT PathName_js, init FROM topology_JS WHERE id_page = '" .
                $p . "' AND (o = '" . $o . "' OR o IS NULL)";
            $DBRESULT = $pearDB->query($query);
            while ($topology_js = $DBRESULT->fetch()) {
                if ($topology_js['init'] == "initM") {
                    if ($o != "hd" && $o != "svcd") {
                        $obis = $o;
                        if (isset($_GET["problem"])) {
                            $obis .= '_pb';
                        }
                        if (isset($_GET["acknowledge"])) {
                            $obis .= '_ack_' . $_GET["acknowledge"];
                        }
                        print "\tsetTimeout('initM($tM, \"$obis\")', 0);";
                    }
                } elseif ($topology_js['init']) {
                    echo "if (typeof " . $topology_js['init'] . " == 'function') {";
                    echo $topology_js['init'] . "();";
                    echo "}";
                }
            }
            ?>
            check_session(<?php $tM ?>);
        });
    </script>
    <script src="./include/common/javascript/xslt.js" type="text/javascript"></script>
</head>
<body>

<?php if (!isset($_REQUEST['iframe']) || (isset($_REQUEST['iframe']) && $_REQUEST['iframe'] != 1)) { ?>
    <script type="text/javascript" src="./lib/wz_tooltip/wz_tooltip.js"></script>
<?php } ?>
<div style="display:none" id="header"></div>

<?php
// Showing the mobile menu if it's a mobile browser
if (isset($isMobile) && $isMobile) {
    require(_CENTREON_PATH_ . 'www/include/common/mobile_menu.php');
}