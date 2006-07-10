<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

Adapted to Pear library by Merethis company, under direction of Cedrick Facon, Romain Le Merlus, Julien Mathis

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
	if (!isset ($oreon))
		exit ();

	isset($_GET["service_id"]) ? $cG = $_GET["service_id"] : $cG = NULL;
	isset($_POST["service_id"]) ? $cP = $_POST["service_id"] : $cP = NULL;
	$cG ? $service_id = $cG : $service_id = $cP;

	isset($_GET["service_description"]) ? $cG = $_GET["service_description"] : $cG = NULL;
	isset($_POST["service_description"]) ? $cP = $_POST["service_description"] : $cP = NULL;
	$cG ? $service_description = $cG : $service_description = $cP;

	isset($_GET["host_name"]) ? $cG = $_GET["host_name"] : $cG = NULL;
	isset($_POST["host_name"]) ? $cP = $_POST["host_name"] : $cP = NULL;
	$cG ? $host_name = $cG : $host_name = $cP;

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	#Path to the configuration dir
	$path = "./include/views/graphs/graphPlugins/";

	#PHP functions
	require_once "./include/common/common-Func.php";

	#
	## Database retrieve information for differents elements list we need on the page
	#

	$graphTs = array(NULL=>NULL);
	$res =& $pearDB->query("SELECT graph_id, name FROM giv_graphs_template ORDER BY name");
	while($res->fetchInto($graphT))
		$graphTs[$graphT["graph_id"]] = $graphT["name"];
	$res->free();

	$tableFile1 = array();
	$tableFile2 = array();
	if ($handle  = @opendir($oreon->optGen["oreon_rrdbase_path"]))	{
		while ($file = @readdir($handle))
			if (is_file($oreon->optGen["oreon_rrdbase_path"]."/$file"))	{
				preg_match("([0-9\_]+)", $file, $matches);
				$split = preg_split("/\_/", $matches[0]);
				if (count($split) == 2 && $split[0] && $split[1]){
					$host_name = getMyHostName($split[0]);
					$service_description = getMyServiceName($split[1]);
					if (array_search($host_name, $oreon->user->lcaHost) && getMyServiceID($service_description, $split[0]))	{
						$tableFile1[$host_name] =  $host_name;
						$tableFile2[$host_name][$file] = $service_description;
					}
				}
			}
		@closedir($handle);
	}

	asort($tableFile1);
	asort($tableFile2);

	$debug = 0;
	$attrsTextI		= array("size"=>"3");
	$attrsText 		= array("size"=>"30");
	$attrsTextarea 	= array("rows"=>"5", "cols"=>"40");
 
 	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./");

	#
	## Form begin
	#

	$form = new HTML_QuickForm('Form', 'get', "?p=".$p);
	$form->addElement('header', 'title', $lang["giv_sr_infos"]);
	#
	## Indicator basic information
	#

	$redirect =& $form->addElement('hidden', 'o');
	$redirect->setValue($o);
	$page =& $form->addElement('hidden', 'p');
	$page->setValue($p);
	$minF =& $form->addElement('hidden', 'min');
	$minF->setValue($min);

	$sel =& $form->addElement('hierselect', 'database', "Host / Service");
	$sel->setOptions(array($tableFile1, $tableFile2));
	$form->addElement('select', 'grapht_graph_id', $lang["giv_gg_tpl"], $graphTs);

	$periods = array(	""=>"",
						"10800"=>$lang["giv_sr_p3h"],
						"21600"=>$lang["giv_sr_p6h"],
						"43200"=>$lang["giv_sr_p12h"],
						"86400"=>$lang["giv_sr_p24h"],
						"172800"=>$lang["giv_sr_p2d"],
						"302400"=>$lang["giv_sr_p4d"],
						"604800"=>$lang["giv_sr_p7d"],
						"1209600"=>$lang["giv_sr_p14d"],
						"2419200"=>$lang["giv_sr_p28d"],
						"2592000"=>$lang["giv_sr_p30d"],
						"2678400"=>$lang["giv_sr_p31d"],
						"5184000"=>$lang["giv_sr_p2m"],
						"10368000"=>$lang["giv_sr_p4m"],
						"15552000"=>$lang["giv_sr_p6m"],
						"31104000"=>$lang["giv_sr_p1y"]);
						
	$sel =& $form->addElement('select', 'period', $lang["giv_sr_period"], $periods);
	//$form->setDefaults(array('period' =>'10800'));

	$form->addElement('text', 'start', $lang['giv_gt_start']);
	$form->addElement('button', "startD", $lang['modify'], array("onclick"=>"displayDatePicker('start')"));
	$form->addElement('text', 'end', $lang['giv_gt_end']);
	$form->addElement('button', "endD", $lang['modify'], array("onclick"=>"displayDatePicker('end')"));


	$subC =& $form->addElement('submit', 'submitC', $lang["giv_sr_button"]);
	$res =& $form->addElement('reset', 'reset', $lang["reset"]);
  	$res =& $form->addElement('reset', 'advanced', $lang["advanced"], array("onclick"=>"DivStatus( 'div', '1' )"));

	if (((isset($_GET["submitC"]) && $_GET["submitC"]) || $min == 1))
		if ($form->validate())	{
		$ret = $form->getsubmitValues();
		$case = NULL;
		$rrDB = array(0=>NULL, 1=>NULL);
		//$_GET["database"] = array("0" => getHostID($_GET["host_name"]) . "_" . getServiceID($_GET["host_name"], $_GET["service_description"]) . ".rrd", "1" => $_GET["host_name"]);

		$rrdDB = $_GET["database"];
		preg_match("([0-9\_]+)", $rrdDB[1], $matches);
		$split = preg_split("/\_/", $matches[0]);
		if (count($split) == 2 && $split[0] && $split[1]){
			//if ()
			$host_name = getMyHostName($split[0]);
			$service_description = getMyServiceName($split[1]);
			$case = $host_name . " / " . $service_description;
		}
		if (array_search($host_name, $oreon->user->lcaHost) && $case)	{

			# 1 for +1 and -1 for -1 and 0 for GMT
			$GMT = "0";

			if (((isset($_GET["submitC"]) && $_GET["submitC"]) || $min == 1))
				$form->setDefaults(array('database' => $split[0]."_".$split[1]));

			# Init variable in the page
			$tpl->assign("title2", $lang["giv_sr_rendTitle"]);
			$tpl->assign("res", $case);
			$tpl->assign("period", $periods[$ret["period"]]);

			# Grab default Graph Template Model and default Data Source Template Model
			$tpl->assign("lgGraph", $lang['giv_gt_name']);
			$tpl->assign("lgMetric", $lang['giv_ct_metric']);
			$tpl->assign("lgCompoTmp", $lang['giv_ct_name']);

			# Init

			if (isset($_GET["start"]) && $_GET["start"] && isset($_GET["end"]) && $_GET["end"]){
				preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $_GET["start"], $matches);
				$start = mktime("0", "0", "0", $matches[1], $matches[2], $matches[3], 1) ;
				preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $_GET["end"], $matches);
				$end = mktime("23", "59", "59", $matches[1], $matches[2], $matches[3], 1)  + 10;
			} else if (isset($_GET["period"]) && $_GET["period"]){
				$start = time() - ($_GET["period"] + 120);
				$end = time() + 120;
			} 

			if ($case)
				$tpl->assign('database_name', $case);
			$tpl->assign('databaseSH', $rrdDB[1]);
			if (isset($end) && isset($start)){
				$tpl->assign("end", $end);
				$tpl->assign("start", $start);
			}
			$tpl->assign('graph_graph_id', $_GET["grapht_graph_id"]);
		}
    }

	#Apply a template definition
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$renderer->setRequiredTemplate('{$label}&nbsp;<font color="red" size="1">*</font>');
	$renderer->setErrorTemplate('<font color="red">{$error}</font><br />{$html}');
	$form->accept($renderer);

	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('o', $o);
	$tpl->assign('lang', $lang);
	$tpl->assign('session_id', session_id());
	$tpl->display("listGraphPlugins.ihtml");
?>
