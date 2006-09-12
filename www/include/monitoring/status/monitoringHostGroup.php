<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

Adapted to Pear library Quickform & Template_PHPLIB by Merethis company, under direction of Cedrick Facon

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
	
	$path = "./include/monitoring/status/";
	$pathDetails = "./include/monitoring/objectDetails/";
	
	switch ($o)	{
		case "hg" 	: require_once($path."hostgroup.php"); break; 
		case "hgpb" 	: require_once($path."hostgroup_problem.php"); break;
		case "hgd" 	: require_once($pathDetails."hostgroupDetails.php"); break; 
		default 	: require_once($path."hostgroup.php"); break;
	}
?>