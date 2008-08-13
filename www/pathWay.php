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
 * SVN: $URL: http://svn.centreon.com/trunk/centreon/www/pathWay.php $
 * SVN: $Id$
 */ 
	if (!isset($oreon))
		exit();

	function getTopologyParent($p)	{
		global $pearDB;
		$rqPath = "SELECT topology_url, topology_url_opt, topology_parent, topology_name, topology_page FROM topology WHERE topology_page = '".$p."' ORDER BY topology_page";
		$DBRESULT =& $pearDB->query($rqPath);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$redirectPath =& $DBRESULT->fetchRow();
		return $redirectPath;
	}
	
	function getTopologyDataPage($p)	{
		global $pearDB;
		$rqPath = "SELECT topology_url, topology_url_opt, topology_parent, topology_name, topology_page FROM topology WHERE topology_page = '".$p."' ORDER BY topology_page";
		$DBRESULT =& $pearDB->query($rqPath);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$redirectPath =& $DBRESULT->fetchRow();
		return $redirectPath;
	}
	
	function getTopologyParentPage($p)	{
		global $pearDB;
		$rqPath = "SELECT topology_parent FROM topology WHERE topology_page = '".$p."'";
		$DBRESULT =& $pearDB->query($rqPath);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
		$redirectPath =& $DBRESULT->fetchRow();
		return $redirectPath["topology_parent"];
	}
	
	
	$tab = getTopologyParent($p);
	$tabPath = array();

	$NameTopology = _($tab["topology_name"]);

	$tabPath[$tab["topology_page"]] = array();
	$tabPath[$tab["topology_page"]]["name"] = $NameTopology;
	$tabPath[$tab["topology_page"]]["opt"] = $tab["topology_url_opt"];
	$tabPath[$tab["topology_page"]]["page"] = $tab["topology_page"];
	$tabPath[$tab["topology_page"]]["url"] = $tab["topology_url"];

	while($tab["topology_parent"]){
		$tab = getTopologyParent($tab["topology_parent"]);
		$tabPath[$tab["topology_page"]] = array();
		$tabPath[$tab["topology_page"]]["name"] = _($tab["topology_name"]);
		$tabPath[$tab["topology_page"]]["opt"] = $tab["topology_url_opt"];
		$tabPath[$tab["topology_page"]]["page"] = $tab["topology_page"];
		$tabPath[$tab["topology_page"]]["url"] = $tab["topology_url"];
	}
	ksort($tabPath);

	$DBRESULT =& $pearDB->query("SELECT * FROM topology WHERE topology_page = '".$p."'");
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$current =& $DBRESULT->fetchRow();
	$DBRESULT->free();
	
	$page = $p;
	if (isset($tabPath[$p]) && !$tabPath[$p]["url"])
		while (1){
			$req = "SELECT * FROM topology WHERE topology_page LIKE '".$page."%' AND topology_parent = '$page' ORDER BY topology_order, topology_page ASC";
			$DBRESULT =& $pearDB->query($req);
			if (!$DBRESULT->numRows())
				break;
			$new_url =& $DBRESULT->fetchRow();
			if (PEAR::isError($DBRESULT))
				print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
			$tabPath[$new_url["topology_page"]] = array();
			$tabPath[$new_url["topology_page"]]["name"] = _($tab["topology_name"]);
			$tabPath[$new_url["topology_page"]]["opt"] = $new_url["topology_url_opt"];
			$tabPath[$new_url["topology_page"]]["page"] = $new_url["topology_page"];
			$page = $new_url["topology_page"];
			if (isset($new_url["topology_url"]) && $new_url["topology_url"])
				break;
		}
		
	$tmp = array();
	foreach ($tabPath as $k => $v){
		$ok = 0;
		foreach ($tmp as $key => $value)
			if ($value["name"] == $v["name"])
				$ok = 1;
		if ($ok == 0)
			$tmp[$k] = $v;
	}
	$tabPath = $tmp;

	if (isset($oreon->user->lcaTopo[$p])){	
		$flag = '<img src="./img/icones/8x14/pathWayBlueStart.gif" alt="" class="imgPathWay">&nbsp;';
		foreach ($tabPath as $cle => $valeur){
			echo $flag;
			?><a href="main.php?p=<?php echo $cle.$valeur["opt"]; ?>" class="pathWay" ><?php print $valeur["name"]; ?></a><?php
			$flag = '&nbsp;<img src="./img/icones/8x14/pathWayBlue.gif" alt="" class="imgPathWay">&nbsp;';
		}
	
		if(isset($_GET["host_id"]))	{
			echo '&nbsp;<img src="./img/icones/8x14/pathWayBlue.gif" alt="" class="imgPathWay">&nbsp;';
			echo getMyHostName($_GET["host_id"]);
		}
	}
?>
<hr>