<?php
/*
 * Copyright 2005-2009 MERETHIS
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

	if (!isset($oreon))
		exit();
		
	print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $mlang; ?>">
<head>
<title>Centreon - IT & Network Monitoring</title>
<link rel="shortcut icon" href="./img/favicon.ico"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<meta name="Generator" content="Centreon - Copyright (C) 2005 - 2009 Open Source Matters. All rights reserved." />
<meta name="robots" content="index, nofollow" />
<link href="<?php echo $skin; ?>style.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $skin; ?><?php echo $colorfile; ?>" rel="stylesheet" type="text/css"/>
<script type="text/javascript" src="./include/common/javascript/scriptaculous/prototype.js"></script>
<script type="text/javascript" src="./include/common/javascript/scriptaculous/scriptaculous.js?load=effects"></script>
<?php

	/*
	 * Add Javascript for NDO status Counter
	 */		
	if ($min != 1) {
		print "<script type=\"text/javascript\" src=\"./include/common/javascript/topCounterStatus/ajaxStatusCounter.js\"></script>\n";
	}

	/*
	 * Add Template CSS for sysInfos Pages
	 */
	if (isset($p) && !strcmp($p, "505") && file_exists("./include/options/sysInfos/templates/classic/classic.css"))
		echo "  <link rel=\"stylesheet\" type=\"text/css\" href=\"./include/options/sysInfos/templates/classic/classic.css\">\n";


	global $search, $search_service;
	
	$searchStr = "";
	if (isset($_GET["search"]))
		$searchStr = "&search_host=".htmlentities($_GET["search"], ENT_QUOTES);
	if (isset($oreon->historySearch[$url]) && !isset($_GET["search"]))
		$searchStr = "&search_host=".$oreon->historySearch[$url];
	
	$searchStrSVC = "";
	if (isset($_GET["search_service"])) {
		$searchStrSVC = "&search_service=".htmlentities($_GET["search_service"], ENT_QUOTES);
		$search_service = htmlentities($_GET["search_service"], ENT_QUOTES);
	} else if (isset($oreon->historySearchService[$url]) && !isset($_GET["search_service"])) {
		$search_service = $oreon->historySearchService[$url];
		$searchStr = "&search_service=".$oreon->historySearchService[$url];
	}
		
	print "<script type='text/javascript' src='./include/common/javascript/codebase/dhtmlxtree.php?sid=".session_id().$searchStr.$searchStrSVC."'></script>\n";

	/*
	 * include javascript
	 */
	 
	$res = null;
	$DBRESULT =& $pearDB->query("SELECT DISTINCT PathName_js, init FROM topology_JS WHERE id_page = '".$p."' AND (o = '" . $o . "' OR o IS NULL)");
	while ($topology_js =& $DBRESULT->fetchRow()) {
		if ($topology_js['PathName_js'] != "./include/common/javascript/ajaxMonitoring.js" && $topology_js['PathName_js'] != "./include/common/javascript/codebase/dhtmlxtree.js")
			if ($topology_js['PathName_js'] != "")
				echo "<script type='text/javascript' src='".$topology_js['PathName_js']."'></script>\n";
	}
	
	/*
	 * init javascript
	 */
	
	$sid = session_id();

	$tS = $oreon->optGen["AjaxTimeReloadStatistic"] * 1000;
	$tM = $oreon->optGen["AjaxTimeReloadMonitoring"] * 1000;
	$oreon->optGen["AjaxFirstTimeReloadStatistic"] == 0 ? $tFS = 10 : $tFS = $oreon->optGen["AjaxFirstTimeReloadStatistic"] * 1000;	
	
	?>
	
<script type='text/javascript'>
	
	<?php
		require_once ("./include/common/javascript/autologout.php");
	?>    
    
    window.onload = function () {
	<?php
		
	if ($min != 1)
		print "setTimeout('reloadStatusCounter($tS, \"$sid\")', $tFS);\n";

	$res = null;
	$DBRESULT =& $pearDB->query("SELECT PathName_js, init FROM topology_JS WHERE id_page = '".$p."' AND (o = '" . $o . "' OR o IS NULL)");	
	while ($topology_js =& $DBRESULT->fetchRow()){
		if ($topology_js['init'] == "initM") {
			if ($o != "hd" && $o != "svcd") {
				print "\tsetTimeout('initM($tM, \"$sid\", \"$o\")', 0);";
			}
		} else if ($topology_js['init']){
			echo $topology_js['init'] ."();";
		}
	}
	print "check_session();";
	print "\n};\n</script>\n";
	
	?>
<script src="./include/common/javascript/xslt.js" type="text/javascript"></script>
</head>
<body>
