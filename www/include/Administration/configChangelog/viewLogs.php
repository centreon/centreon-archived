<?php
/*
 * Copyright 2005-2011 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

	if (!isset ($oreon))
		exit ();

	function searchUserName($user_name) {
		global $pearDB;
		$str = "";

		$DBRES = $pearDB->query("SELECT contact_id FROM contact WHERE contact_name LIKE '%".$user_name."%' OR contact_alias LIKE '%".$user_name."%'");
		while ($row = $DBRES->fetchRow()) {
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
	foreach ($objects_type_tab as $key => $name) {
		$name = _("$name");
		$options .= "<option value='$key' ".(($otype == $key) ? 'selected' : "").">$name</option>";
	}

	$tpl->assign("obj_type", $options);
	?>

	<?php
	$tabz_obj_type = array();
	$tabz_obj_name = array();
	$tabz_obj_id = array();

	if ($searchO || $searchU || !is_null($otype)) {
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

		while ($res = $DBRESULT->fetchRow()) {
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
	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
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