<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!isset($oreon))
		exit();
	print "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>\n";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $mlang; ?>" lang="<?php echo $mlang; ?>">
<head>
<title>Supervision Tool - Powered By Centreon</title>
<HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
<link rel="shortcut icon" href="./img/iconOreon.ico"/>
<link rel="stylesheet" type="text/css" href="./include/common/javascript/autocompletion.css" />
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
<link href="<?php echo $skin; ?>style.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $skin; ?>menu.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $skin; ?>configuration_form.css" rel="stylesheet" type="text/css"/>
<link href="<?php echo $skin; ?><?php echo $colorfile; ?>" rel="stylesheet" type="text/css"/>
<?php
	if($min != 1){

	$DBRESULT =& $pearDB->query("SELECT ndo_activate FROM general_opt LIMIT 1");
	# Set base value
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());

	$ndo = $gopt["ndo_activate"];

	if (isset($ndo) && !$ndo)
		print '<script language="javascript"> var _adrrsearchC = "./include/monitoring/engine/MakeXML4statusCounter.php"; </script>';
	else
		print '<script language="javascript"> var _adrrsearchC = "./include/monitoring/engine/MakeXML_Ndo_StatusCounter.php"; </script>';

		print "<script language='javascript' src='./include/common/javascript/ajaxStatusCounter.js'></script>";

	}

	# Add Template CSS for sysInfos Pages
	if (isset($p) && !strcmp($p, "505") && file_exists("./include/options/sysInfos/templates/classic/classic.css"))
		echo "  <link rel=\"stylesheet\" type=\"text/css\" href=\"./include/options/sysInfos/templates/classic/classic.css\">\n";

	if (isset($p) && $p == 310)
		print "<SCRIPT language='javascript' src='./include/common/javascript/datepicker.js'></SCRIPT>";

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
		print $DBRESULT->getDebugInfo()."<br>";
	while ($DBRESULT->fetchInto($topology_js)){
		if(!$ndo || ($ndo && $topology_js['PathName_js'] != "./include/common/javascript/ajaxMonitoring.js"))
		echo "<script language='javascript' src='".$topology_js['PathName_js']."'></script> ";
	}
	/*
	 * init javascript
	 */

	$tS = $oreon->optGen["AjaxTimeReloadStatistic"] * 1000;
	$tM = $oreon->optGen["AjaxTimeReloadMonitoring"] * 1000;
	$oreon->optGen["AjaxFirstTimeReloadStatistic"] == 0 ? $tFS = 10 : $tFS = $oreon->optGen["AjaxFirstTimeReloadStatistic"] * 1000;
//	$oreon->optGen["AjaxFirstTimeReloadMonitoring"] == 0 ? $tFM = 10 : $tFM = $oreon->optGen["AjaxFirstTimeReloadMonitoring"] * 1000;
	$sid = session_id();

	?>
	<script type='text/javascript'>
	    window.onload = function () {
	<?php
	if($min != 1)
		print "setTimeout('reloadStatusCounter($tS, \"$sid\")', $tFS);\n";

	$res = null;
	$DBRESULT =& $pearDB->query("SELECT PathName_js, init FROM topology_JS WHERE id_page = '".$p."' AND (o = '" . $o . "' OR o IS NULL)");
	if (PEAR::isError($DBRESULT)) print $DBRESULT->getDebugInfo()."<br>";
	while ($DBRESULT->fetchInto($topology_js)){
		if($topology_js['init'] == "initM")	{
			?>setTimeout('initM(<?php echo $tM; ?>,"<?php echo $sid; ?>","<?php echo $o;?>")', 0);<?php
		} else if ($topology_js['init'])
			echo $topology_js['init'] ."();";
	}
	?>
    	};
    </script>
<?php
if($ndo)
    print '<script src="./include/common/javascript/xslt.js" type="text/javascript"></script>';
?>
</head>
<body>