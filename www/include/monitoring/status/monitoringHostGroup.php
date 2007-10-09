<?
/**
Oreon is developped with GPL Licence 2.0 :
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

	require_once './class/other.class.php';
	include_once("./include/monitoring/common-Func.php");			
	include_once("./include/monitoring/external_cmd/cmd.php");
	
	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	$DBRESULT =& $pearDB->query("SELECT ndo_base_prefix,ndo_activate FROM general_opt LIMIT 1");
	# Set base value
	$gopt = array_map("myDecode", $DBRESULT->fetchRow());
	
	$ndo = $gopt["ndo_activate"];
		
	
	if ($ndo)	
		$path = "./include/monitoring/status/status-ndo/";
	else{
		$path = "./include/monitoring/status/status-log/";	
	}

	$pathDetails = "./include/monitoring/objectDetails/";
	
	if ($ndo){
		switch ($o)	{
			case "hg" 	: require_once($path."hostGroup.php"); break;
			case "hgpb" : require_once($path."hostGroup.php"); break;
			case "hgd" 	: require_once($pathDetails."hostgroupDetails.php"); break; 
			default 	: require_once($path."hostGroup.php"); break;
		}
	}else{
		include("./include/monitoring/status/resume.php"); 	
		switch ($o)	{
			case "hg" 	: require_once($path."hostgroup.php"); break;
			case "hgpb" : require_once($path."hostgroup_problem.php"); break;
			case "hgd" 	: require_once($pathDetails."hostgroupDetails.php"); break; 
			default 	: require_once($path."hostgroup.php"); break;		
		}
	}	
?>