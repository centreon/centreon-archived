<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon

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


header("Content-Type: application/csv-tab-delimited-table");
header("Content-disposition: filename=table.csv");


	$oreonPath = '/srv/oreon/';

	function check_injection(){
		if ( eregi("(<|>|;|UNION|ALL|OR|AND|ORDER|SELECT|WHERE)", $_GET["sid"])) {
			get_error('sql injection detected');
			return 1;
		}
		return 0;
	}

	function get_error($str){
		echo $str."<br>";
		exit(0);
	}

	include_once($oreonPath . "www/oreon.conf.php");
	include_once($oreonPath . "www/DBconnect.php");
	include_once($oreonPath . "www/DBOdsConnect.php");

	if(isset($_GET["sid"]) && !check_injection($_GET["sid"])){

		$sid = $_GET["sid"];
		$sid = htmlentities($sid);
		$res =& $pearDB->query("SELECT * FROM session WHERE session_id = '".$sid."'");
		if($res->fetchInto($session)){
			$_POST["sid"] = $sid;
		}else
			get_error('bad session id');
	}
	else
		get_error('need session identifiant !');
	/* security end 2/2 */

	isset ($_GET["host"]) ? $mhost = $_GET["host"] : $mhost = NULL;
	isset ($_POST["host"]) ? $mhost = $_POST["host"] : $mhost = $mhost;

	$path = "./include/reporting/dashboard";
	require_once '../../../class/other.class.php';
	require_once '../../common/common-Func.php';
	require_once '../../common/common-Func-ACL.php';

	$period = (isset($_POST["period"])) ? $_POST["period"] : "today"; 
	$period = (isset($_GET["period"])) ? $_GET["period"] : $period;

	$user_lang = "fr";

	# Load traduction in the selected language.
	is_file ("../../../lang/".$user_lang.".php") ? include_once ("../../../lang/".$user_lang.".php") : include_once ("./lang/en.php");
	is_file ("../../reporting/lang/".$user_lang.".php") ? include_once ("../../reporting/lang/".$user_lang.".php") : include_once ("./include/reporting/lang/en.php");


	require_once 'HostLog.php';


	echo "Rapport de suppervision \n";
	echo "\n";
	echo "Host; debut de la periode; fin de la periode; durée\n";
	echo $mhost."; ".$start_date_select."; ".$end_date_select."; ". Duration::toString($ed - $sd) ."\n";
	echo "\n";


	echo "Etats;Temps;Temps total;Temps connu; Alert\n";
	foreach ($tab_resume as $tab) {
		echo $tab["state"]. ";".$tab["time"]. ";".$tab["pourcentTime"]. ";".$tab["pourcentkTime"]. ";".$tab["nbAlert"]. ";\n";
	}
	echo "\n";
	
	echo "Service;".$lang["m_OKTitle"]."%; ".$lang["m_OKTitle"]." Alert;"
				   .$lang["m_WarningTitle"]."%; ".$lang["m_WarningTitle"]." Alert;"
				   .$lang["m_UnknownTitle"]."%; ".$lang["m_UnknownTitle"]." Alert;"
				   .$lang["m_CriticalTitle"]."%; ".$lang["m_CriticalTitle"]." Alert;"
				   .$lang["m_PendingTitle"]."%;\n";
					
	foreach ($tab_svc as $tab) {
		echo $tab["svcName"]. ";".$tab["PtimeOK"]. ";".$tab["OKnbEvent"]. 
							  ";".$tab["PtimeWARNING"]. ";".$tab["WARNINGnbEvent"].
							  ";".$tab["PtimeCRITICAL"]. ";".$tab["CRITICALnbEvent"]. 
							  ";".$tab["PtimeUNKNOWN"]. ";".$tab["UNKNOWNnbEvent"].
							  ";".$tab["PtimeNONE"]. ";;\n";
	}

/*
	echo "<pre>";
	print_r($tab_svc);
	echo "</pre>";
*/	
?>