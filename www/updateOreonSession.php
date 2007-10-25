<?



	require_once ("class/Session.class.php");
	require_once ("class/Oreon.class.php");

		$sid = $_GET["sid"];


			session_id($sid);
			session_start();
			
			//$oreon = $_SESSION["oreon"];
			
			$oreon = new oreon();
			
			$oreon =& $_SESSION["oreon"];
			if (!is_object($oreon))
			exit();			
			
			
			$oreon->historySearch["./include/monitoring/status/monitoringService.php"] = "pouet";

?>