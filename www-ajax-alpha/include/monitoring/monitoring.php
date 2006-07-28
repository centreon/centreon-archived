<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon.org
*/

	$str = "";

	
	// init value for each included file
	$i = 0;
	$old = "";
	

	if ((isset($_GET["o"]) && !strcmp($_GET['o'], "h")))
		include("./include/monitoring/status/host.php");
	else if ((isset($_GET["o"]) && !strcmp($_GET['o'], "hp")))
		include("./include/monitoring/status/host_probleme.php");
	else if (!isset($_GET["o"]) || (isset($_GET["o"]) && !strcmp($_GET['o'], "s") || !strcmp($_GET['o'], "")))
		include("./include/monitoring/status/service.php");
	else if (!strcmp($_GET['o'], "sp"))
		include("./include/monitoring/status/service_probleme.php");
	else if (isset($_GET["o"]) && !strcmp($_GET['o'], "sc"))
		include("./include/monitoring/status/service_sc.php");
	else if (isset($_GET["o"]) && !strcmp($_GET['o'], "hg"))
		include("./include/monitoring/status/hostgroup.php");
	else if (isset($_GET["o"]) && !strcmp($_GET['o'], "proc"))
		include("./include/monitoring/status/proc_info.php");
	else if (isset($_GET["o"]) && !strcmp($_GET['o'], "sg"))
		include("./include/monitoring/status/status_gird.php");
	else if (isset($_GET["o"]) && !strcmp($_GET['o'], "sm"))
		include("./include/monitoring/status/status_summary.php");
	else if (isset($_GET["o"]) && !strcmp($_GET['o'], "sgr"))
		include("./include/monitoring/status/status_servicegroup.php");
?>