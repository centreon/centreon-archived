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

	print "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $centreon->user->lang; ?>">
<head>
<title>Centreon - IT & Network Monitoring</title>
<link rel="shortcut icon" href="./img/favicon.ico"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="Generator" content="Centreon - Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved." />
<meta name="robots" content="index, nofollow" />
<link href="<?php echo $skin; ?>style.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $skin; ?><?php echo $colorfile; ?>" rel="stylesheet" type="text/css"/>
<link href="./include/common/javascript/modalbox.css" rel="stylesheet" type="text/css" media="screen"/>
<link href="<?php echo $skin; ?>Modalbox/<?php echo $colorfile; ?>" rel="stylesheet" type="text/css" media="screen"/>
<link href="./include/common/javascript/prototype-datepicker.css" rel="stylesheet" type="text/css" media="screen"/>
<link href="<?php echo $skin; ?>jquery-ui/jquery-ui.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $skin; ?>jquery-ui/jquery-ui-centreon.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="./include/common/javascript/scriptaculous/prototype.js"></script>
<?php if (!isset($_REQUEST['iframe']) || (isset($_REQUEST['iframe']) && $_REQUEST['iframe'] != 1)) { ?>
<script type="text/javascript" src="./include/common/javascript/scriptaculous/scriptaculous.js?load=effects,dragdrop"></script>
<script type="text/javascript" src="./include/common/javascript/modalbox.js"></script>
<script type="text/javascript" src="./include/common/javascript/prototype-datepicker.js"></script>
<script type="text/javascript" src="./include/common/javascript/jquery/jquery.js"></script>
<script type="text/javascript" src="./include/common/javascript/jquery/jquery-ui.js"></script>
<script type="text/javascript">jQuery.noConflict();</script>
<link href="./include/common/javascript/jquery/plugins/colorbox/colorbox.css" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="./include/common/javascript/jquery/plugins/colorbox/jquery.colorbox-min.js"></script>
<script type="text/javascript" src="./include/common/javascript/jquery/plugins/jeditable/jquery.jeditable-min.js"></script>

<?php } ?>
<script type="text/javascript" src="./class/centreonToolTip.js"></script>
<?php

	/*
	 * Add Javascript for NDO status Counter
	 */
	if ($centreon->user->access->admin == 0) {
		$tabActionACL = $centreon->user->access->getActions();
		if ($min != 1 && (isset($tabActionACL["top_counter"]) || isset($tabActionACL["poller_stats"]))) {
			print "<script type=\"text/javascript\" src=\"./include/common/javascript/topCounterStatus/ajaxStatusCounter.js.php\"></script>\n";
		}
		unset($tabActionACL);
	} else {
		if ($min != 1) {
			print "<script type=\"text/javascript\" src=\"./include/common/javascript/topCounterStatus/ajaxStatusCounter.js.php\"></script>\n";
		}
	}

	/*
	 * Add Template CSS for sysInfos Pages
	 */
	if (isset($p) && strstr($p, "505") && file_exists("./include/options/sysInfos/templates/classic/classic.css")) {
		echo "  <link rel=\"stylesheet\" type=\"text/css\" href=\"./include/options/sysInfos/templates/classic/classic.css\">\n";
	}

	global $search, $search_service;

	$searchStr = "";
	if (isset($_GET["search"]))
		$searchStr = "&search_host=".htmlentities($_GET["search"], ENT_QUOTES, "UTF-8");
	if (isset($centreon->historySearch[$url]) && !isset($_GET["search"]))
		$searchStr = "&search_host=".$centreon->historySearch[$url];

	$searchStrSVC = "";
	if (isset($_GET["search_service"])) {
		$searchStrSVC = "&search_service=".htmlentities($_GET["search_service"], ENT_QUOTES, "UTF-8");
		$search_service = htmlentities($_GET["search_service"], ENT_QUOTES, "UTF-8");
	} else if (isset($centreon->historySearchService[$url]) && !isset($_GET["search_service"])) {
		$search_service = $centreon->historySearchService[$url];
		$searchStr = "&search_service=".$centreon->historySearchService[$url];
	}

	print "<script type='text/javascript' src='./include/common/javascript/codebase/dhtmlxtree.php?sid=".session_id().$searchStr.$searchStrSVC."'></script>\n";

	/*
	 * include javascript
	 */

	$res = null;
	$DBRESULT = $pearDB->query("SELECT DISTINCT PathName_js, init FROM topology_JS WHERE id_page = '".$p."' AND (o = '" . $o . "' OR o IS NULL)");
	while ($topology_js = $DBRESULT->fetchRow()) {
		if ($topology_js['PathName_js'] != "./include/common/javascript/ajaxMonitoring.js" && $topology_js['PathName_js'] != "./include/common/javascript/codebase/dhtmlxtree.js") {
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
	$centreon->optGen["AjaxFirstTimeReloadStatistic"] == 0 ? $tFS = 10 : $tFS = $centreon->optGen["AjaxFirstTimeReloadStatistic"] * 1000;

	?>
<script type='text/javascript'>
	<?php
		require_once ("./include/common/javascript/autologout.php");
	?>
    jQuery(function () {
	<?php

	if ($centreon->user->access->admin == 0) {
		$tabActionACL = $centreon->user->access->getActions();
		if ($min != 1 && (isset($tabActionACL["top_counter"]) || isset($tabActionACL["poller_stats"]))) {
			print "setTimeout('reloadStatusCounter($tS, \"$sid\")', $tFS);\n";
		}
		unset($tabActionACL);
	} else {
		if ($min != 1) {
			print "setTimeout('reloadStatusCounter($tS, \"$sid\")', $tFS);\n";
		}
	}

	$res = null;
	$DBRESULT = $pearDB->query("SELECT DISTINCT PathName_js, init FROM topology_JS WHERE id_page = '".$p."' AND (o = '" . $o . "' OR o IS NULL)");
	while ($topology_js = $DBRESULT->fetchRow()){
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
		} else if ($topology_js['init']){
			echo "if (typeof ".$topology_js['init']." == 'function') {";
		    echo $topology_js['init'] ."();";
		    echo "}";
		}
	}
	print "check_session();";
	print "\n});\n";

?>
</script>
<script src="./include/common/javascript/xslt.js" type="text/javascript"></script>
</head>
<body>
<?php if (!isset($_REQUEST['iframe']) || (isset($_REQUEST['iframe']) && $_REQUEST['iframe'] != 1)) { ?>
<script type="text/javascript" src="./lib/wz_tooltip/wz_tooltip.js"></script>
<?php } ?>
