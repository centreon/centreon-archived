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

	if (!isset ($oreon))
		exit ();
	
	function searchUserName($user_name) {
		global $pearDB;
		$str = "";
		
		$DBRES =& $pearDB->query("SELECT contact_id FROM contact WHERE contact_name LIKE '%".$user_name."%' OR contact_alias LIKE '%".$user_name."%'");
		while ($row =& $DBRES->fetchRow()) {
			if ($str != "")
				$str .= ", ";
			$str .= "'" . $row['contact_id'] . "'";
		}
		if ($str == "")
			$str = "''";
		return $str;
	}
			
	/*
	 * Pear library
	 */
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';
	
	/*
	 * Path to the configuration dir
	 */
	$path = "./include/Administration/configChangelog/";
	
	/*
	 * PHP functions
	 */ 
	require_once "./include/common/common-Func.php";
	require_once("./class/centreonDB.class.php");
	
	$pearDBO = new CentreonDB("centstorage");
	
	
	if (isset($_POST["searchO"]))
		$searchO = $_POST["searchO"];
	elseif (isset($_GET["searchO"]))
		$searchO = $_GET["searchO"];
	else
		$searchO = NULL;
		
	if (isset($_POST["searchU"])) 
		$searchU = $_POST["searchU"];
	elseif (isset($_GET["searchU"]))
		$searchU = $_GET["searchU"];			
	else
		$searchU = NULL;
	
	if (isset($_POST["otype"])) 
		$otype = $_POST["otype"];
	elseif (isset($_GET["otype"]))
		$otype = $_GET["otype"];		
	else
		$otype = NULL;
 			
		
	$form = new HTML_QuickForm('select_form', 'POST', "?p=".$p);
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);
	
	$tpl->assign("date", _("Date"));
	$tpl->assign("type", _("Type"));
	$tpl->assign("object_id", _("Object ID"));
	$tpl->assign("object_name", _("Object"));
	$tpl->assign("action", _("Action"));
	$tpl->assign("contact_name", _("Contact Name"));
	$tpl->assign("field_name", _("Field Name"));
	$tpl->assign("field_value", _("Field Value"));
	$tpl->assign("before", _("Before"));
	$tpl->assign("after", _("After"));
	$tpl->assign("logs", _("Logs for "));
	$tpl->assign("objTypeLabel", _("Object type : "));
	$tpl->assign("objNameLabel", _("Object name : "));
	$tpl->assign("noModifLabel", _("No modification was made."));
	
	if (isset($listAction))
		$tpl->assign("action", $listAction);
	if (isset($listModification))
		$tpl->assign("modification", $listModification);
	
		
	$objects_type_tab = array();
	$objects_type_tab = $oreon->CentreonLogAction->listObjecttype();
	$options = "";	
	foreach ($objects_type_tab as $key => $name)
		$options .= "<option value='$key' ".(($otype == $key) ? 'selected' : "").">$name</option>"; 
	 
	$tpl->assign("obj_type", $options);
	?>
	
	<?php
	$tabz_obj_type = array();
	$tabz_obj_name = array();
	$tabz_obj_id = array();
		
	if ($searchO || $searchU || $otype) {
		$query = "SELECT DISTINCT object_name, object_id, object_type FROM log_action ";
		$where_flag = 1;
		if ($searchO) {			
			if ($where_flag)  {
				$query .= " WHERE ";
				$where_flag = 0;
			}
			else
				$query .= " AND ";
			$query .= " object_name LIKE '%".$searchO."%' ";
		}
		if ($searchU) {
			if ($where_flag)  {
				$query .= " WHERE ";
				$where_flag = 0;
			}
			else
				$query .= " AND ";
			$query .= " log_contact_id IN (".searchUserName($searchU).") ";			
		}
		if ($otype) {
			if ($where_flag)  {
				$query .= " WHERE ";
				$where_flag = 0;
			}
			else
				$query .= " AND ";
			$query .= " object_type = '".$objects_type_tab[$otype]."' ";			
		}
		$query .= " ORDER BY object_type, object_name";
		$DBRESULT = $pearDBO->query($query);
		
		while ($res =& $DBRESULT->fetchRow()) {
			if ($res['object_id']) {
				$res['object_name'] = str_replace('#S#', "/", $res["object_name"]);
				$res['object_name'] = str_replace('#BS#', "\\", $res["object_name"]);
				$tabz_obj_id[] = $res['object_id'];
				$tabz_obj_type[] = $res['object_type'];
				$tabz_obj_name[] = $res['object_name'];			
			}
		}
	}	
	
		
	/*
	 * Apply a template definition
	 */	 
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);		
	$tpl->assign('form', $renderer->toArray());
	$tpl->assign('search_object_str', _("Object"));
	$tpl->assign('search_user_str', _("User"));
	$tpl->assign('searchO', $searchO);
	$tpl->assign('searchU', $searchU);
	$tpl->assign('obj_str', _("Object Type"));
	$tpl->assign('tabz_obj_type', $tabz_obj_type);
	$tpl->assign('tabz_obj_name', $tabz_obj_name);
	$tpl->assign('tabz_obj_id', $tabz_obj_id);
	$tpl->assign('type_id', $otype);
	$tpl->assign('p', $p);
	if (isset($_POST['searchO']) || isset($_POST['searchU']) || isset($_POST['otype']) || !isset($_GET['object_id']))
		$tpl->display("viewLogs.ihtml");
	else {
		$listAction = array();
        $listAction = $oreon->CentreonLogAction->listAction($_GET['object_id'], $_GET['object_type']);
        $listModification = array();
        $listModification = $oreon->CentreonLogAction->listModification($_GET['object_id'], $_GET['object_type']);
		
		if (isset($listAction))
                $tpl->assign("action", $listAction);
        if (isset($listModification))
                $tpl->assign("modification", $listModification);
		
		$tpl->display("viewLogsDetails.ihtml");
	}
?>