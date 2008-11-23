<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

/*
 * SVN: $URL: http://svn.centreon.com/trunk/centreon/www/htmlHeader.php $
 * SVN: $Id$
 */

	if (!isset($oreon))
		exit();
		
	print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";
	
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $mlang; ?>">
<head>
<title>Centreon, Revisited Experience Of Nagios</title>
<link rel="shortcut icon" href="./img/favicon.ico"/>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
<meta name="Generator" content="Centreon - Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved." />
<meta name="robots" content="index, nofollow" />
<link href="<?php echo $skin; ?>style.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $skin; ?><?php echo $colorfile; ?>" rel="stylesheet" type="text/css"/>
<script src="./include/common/javascript/scriptaculous/prototype.js" type="text/javascript"></script>
<script src="./include/common/javascript/scriptaculous/scriptaculous.js?load=effects" type="text/javascript"></script>
<?php

	if ($min != 1) {
		/*
		 * Add Javascript for NDO status Counter
		 */		
		print "<script type=\"text/javascript\" src=\"./include/common/javascript/topCounterStatus/ajaxStatusCounter.js\"></script>\n";
	}

	/*
	 * Add Template CSS for sysInfos Pages
	 */
	if (isset($p) && !strcmp($p, "505") && file_exists("./include/options/sysInfos/templates/classic/classic.css"))
		echo "  <link rel=\"stylesheet\" type=\"text/css\" href=\"./include/options/sysInfos/templates/classic/classic.css\">\n";

	print "<SCRIPT type='text/javascript' src='./include/common/javascript/codebase/dhtmlxtree.php?sid=".session_id()."'></SCRIPT>\n";
	
	if (isset($p) && $p == 310)
		print "<SCRIPT type='text/javascript' src='./include/common/javascript/datepicker.js'></SCRIPT>\n";

	/*
	 * include javascript
	 */
	 
	$res = null;
	$DBRESULT =& $pearDB->query("SELECT DISTINCT PathName_js, init FROM topology_JS WHERE id_page = '".$p."' AND (o = '" . $o . "' OR o IS NULL)");
	if (PEAR::isError($DBRESULT))
		print $DBRESULT->getDebugInfo()."<br />";
	while ($topology_js =& $DBRESULT->fetchRow()){
		if ($topology_js['PathName_js'] != "./include/common/javascript/ajaxMonitoring.js" && $topology_js['PathName_js'] != "./include/common/javascript/codebase/dhtmlxtree.js")
			echo "<script type='text/javascript' src='".$topology_js['PathName_js']."'></script>\n";
	}
	
	/*
	 * init javascript
	 */
	
	$sid = session_id();

	$tS = $oreon->optGen["AjaxTimeReloadStatistic"] * 1000;
	$tM = $oreon->optGen["AjaxTimeReloadMonitoring"] * 1000;
	
	?>
	<script type='text/javascript'>
	    window.onload = function () {
	<?php
	if ($min != 1)
		print "setTimeout('reloadStatusCounter($tS, \"$sid\")', 0);\n";

	$res = null;
	$DBRESULT =& $pearDB->query("SELECT `PathName_js`, `init` FROM `topology_JS` WHERE `id_page` = '".$p."' AND (`o` = '" . $o . "' OR `o` IS NULL)");
	if (PEAR::isError($DBRESULT)) 
		print $DBRESULT->getDebugInfo()."<br />";
	while ($topology_js =& $DBRESULT->fetchRow()){
		if ($topology_js['init'] == "initM")	{
			?>setTimeout('initM(<?php echo $tM; ?>,"<?php echo $sid ; ?>", "<?php echo $o;?>")', 0);<?php
		} else if ($topology_js['init']){
			echo $topology_js['init'] ."();";
		}
	}
	?>
    	};
    </script>
	<script src="./include/common/javascript/xslt.js" type="text/javascript"></script>
</head>
<body>