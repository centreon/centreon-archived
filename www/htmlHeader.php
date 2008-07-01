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
 * SVN: $URL$
 * SVN: $Id$
 */

	if (!isset($oreon))
		exit();
		
	print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $mlang; ?>">
<head>
<title> Centreon </title>
<link rel="shortcut icon" href="./img/iconOreon.ico"/>
<link rel="stylesheet" type="text/css" href="./include/common/javascript/autocompletion.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
<meta name="Generator" content="Centreon - Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved." />
<meta name="robots" content="index, follow" />
<link href="<?php echo $skin; ?>style.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $skin; ?>menu.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $skin; ?>configuration_form.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $skin; ?><?php echo $colorfile; ?>" rel="stylesheet" type="text/css"/>
<script src="./include/common/javascript/scriptaculous/prototype.js" type="text/javascript"></script>
<script src="./include/common/javascript/scriptaculous/scriptaculous.js?load=effects" type="text/javascript"></script>
<?php
	if ($min != 1){

		$DBRESULT =& $pearDB->query("SELECT ndo_activate FROM general_opt LIMIT 1");
		# Set base value
		$gopt = array_map("myDecode", $DBRESULT->fetchRow());
	
		$ndo = $gopt["ndo_activate"];
	
		if (isset($ndo) && !$ndo)
			print "<script type=\"text/javascript\"> var _adrrsearchC = \"./include/monitoring/engine/MakeXML4statusCounter.php\";</script>\n";
		else
			print "<script type=\"text/javascript\"> var _adrrsearchC = \"./include/monitoring/engine/MakeXML_Ndo_StatusCounter.php\";</script>\n";
		
		print "<script type=\"text/javascript\" src=\"./include/common/javascript/topCounterStatus/ajaxStatusCounter.js\"></script>\n";
	}

	# Add Template CSS for sysInfos Pages
	if (isset($p) && !strcmp($p, "505") && file_exists("./include/options/sysInfos/templates/classic/classic.css"))
		echo "  <link rel=\"stylesheet\" type=\"text/css\" href=\"./include/options/sysInfos/templates/classic/classic.css\">\n";

	print "<SCRIPT type='text/javascript' src='./include/common/javascript/codebase/dhtmlxtree.php?sid=".session_id()."'></SCRIPT>\n";
	
	if (isset($p) && $p == 310)
		print "<SCRIPT type='text/javascript' src='./include/common/javascript/datepicker.js'></SCRIPT>\n";

	/*
	 * include javascript
	 */

	$DBRESULT =& $pearDB->query("SELECT ndo_activate FROM general_opt LIMIT 1");
	# Set base value
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());
	$ndo = $gopt["ndo_activate"];

	$res = null;
	$DBRESULT =& $pearDB->query("SELECT DISTINCT PathName_js, init FROM topology_JS WHERE id_page = '".$p."' AND (o = '" . $o . "' OR o IS NULL)");
	if (PEAR::isError($DBRESULT))
		print $DBRESULT->getDebugInfo()."<br />";
	while ($topology_js =& $DBRESULT->fetchRow()){
		if (!$ndo || ($ndo && $topology_js['PathName_js'] != "./include/common/javascript/ajaxMonitoring.js" && $topology_js['PathName_js'] != "./include/common/javascript/codebase/dhtmlxtree.js"))
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
	    window.onload = function () {
	
	<?php
	
	if ($min != 1)
		print "setTimeout('reloadStatusCounter($tS, \"$sid\")', $tFS);\n";

	$res = null;
	$DBRESULT =& $pearDB->query("SELECT PathName_js, init FROM topology_JS WHERE id_page = '".$p."' AND (o = '" . $o . "' OR o IS NULL)");
	if (PEAR::isError($DBRESULT)) print $DBRESULT->getDebugInfo()."<br />";
	while ($topology_js =& $DBRESULT->fetchRow()){
		if ($topology_js['init'] == "initM")	{
			?>setTimeout('initM(<?php echo $tM; ?>,"<?php echo $sid; ?>","<?php echo $o;?>")', 0);<?php
		} else if ($topology_js['init']){
			echo $topology_js['init'] ."();";
		}
	}
	?>
    	};
    </script>
<?php
	if ($ndo)
		print '<script src="./include/common/javascript/xslt.js" type="text/javascript"></script>';?>
		
</head>
<body>