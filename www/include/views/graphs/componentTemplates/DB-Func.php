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
 	if (!isset($oreon))
		exit;

	function NameHsrTestExistence ($name = NULL)    {
		global $pearDB, $pearDBO, $form;
		$gsvs = NULL;
		if (isset($form))
			$gsvs = $form->getSubmitValues();
		$sql = "SELECT compo_id FROM giv_components_template WHERE ";
		$sql .= "name = '".$gsvs["name"]."' ";
		if ( $gsvs["index_id"] != NULL ) {
			$sql_qy = $pearDBO->query("SELECT host_id, service_id  FROM index_data WHERE id='".$gsvs["index_id"]."' LIMIT 1;");
			$hs_id = $sql_qy->fetchRow();
			$sql_qy->free();
			$sql .= "AND host_id = '".$hs_id["host_id"]."' AND service_id = '".$hs_id["service_id"]."'";
		} else {
			$sql .= "AND host_id iS NULL  AND service_id IS NULL";
		}
		$DBRESULT = $pearDB->query($sql);
		$compo = $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $compo["compo_id"] == $gsvs["compo_id"]) {
			return true;
		#Duplicate entry
		} else if ($DBRESULT->numRows() >= 1 && $compo["compo_id"] != $gsvs["compo_id"]) {
			return false;
		} else {
			return true;
		}
	}

	function DsHsrTestExistence ($name = NULL)      {
		global $pearDB, $pearDBO, $form;
		$gsvs = NULL;
		if (isset($form))
			$gsvs = $form->getSubmitValues();
		$sql = "SELECT compo_id FROM giv_components_template WHERE ";
		$sql .= "ds_name = '".$gsvs["ds_name"]."' ";
		if ( $gsvs["index_id"] != NULL ) {
			$sql_qy = $pearDBO->query("SELECT host_id, service_id  FROM index_data WHERE id='".$gsvs["index_id"]."' LIMIT 1;");
			$hs_id = $sql_qy->fetchRow();
			$sql_qy->free();
			$sql .= "AND host_id = '".$hs_id["host_id"]."' AND service_id = '".$hs_id["service_id"]."'";
		} else {
			$sql .= "AND host_id iS NULL  AND service_id IS NULL";
		}
		$DBRESULT = $pearDB->query($sql);
		$compo = $DBRESULT->fetchRow();
		#Modif case
		if ($DBRESULT->numRows() >= 1 && $compo["compo_id"] == $gsvs["compo_id"])
			return true;
		#Duplicate entry
		else if ($DBRESULT->numRows() >= 1 && $compo["compo_id"] != $gsvs["compo_id"])
			return false;
		else
			return true;
	}

	function deleteComponentTemplateInDB ($compos = array())	{
		global $pearDB;
		foreach($compos as $key => $value){
			$DBRESULT = $pearDB->query("DELETE FROM giv_components_template WHERE compo_id = '".$key."'");
		}
		defaultOreonGraph();
	}

	function defaultOreonGraph ()	{
		global $pearDB;
		$DBRESULT = $pearDB->query("SELECT DISTINCT compo_id FROM giv_components_template WHERE default_tpl1 = '1'");
		if (!$DBRESULT->numRows())	{
			$DBRESULT2 = $pearDB->query("UPDATE giv_components_template SET default_tpl1 = '1' LIMIT 1");
		}
	}

	function noDefaultOreonGraph ()	{
		global $pearDB;
		$rq = "UPDATE giv_components_template SET default_tpl1 = '0'";
		$DBRESULT = $pearDB->query($rq);
	}

	function multipleComponentTemplateInDB ($compos = array(), $nbrDup = array())	{
		global $pearDB;
		foreach($compos as $key=>$value) {
			$DBRESULT = $pearDB->query("SELECT * FROM giv_components_template WHERE compo_id = '".$key."' LIMIT 1");
			$row = $DBRESULT->fetchRow();
			$row["compo_id"] = '';
			$row["default_tpl1"] = '0';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "name" ? ($name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (NameHsrTestExistence($name))	{
					$val ? $rq = "INSERT INTO giv_components_template VALUES (".$val.")" : $rq = null;
					$DBRESULT2 = $pearDB->query($rq);
				}
			}
		}
	}

	function updateComponentTemplateInDB ($compo_id = NULL)	{
		if (!$compo_id) return;
		updateComponentTemplate($compo_id);
	}

	function insertComponentTemplateInDB ()	{
		$compo_id = insertComponentTemplate();
		return ($compo_id);
	}

	function insertComponentTemplate()	{
		global $form, $pearDB, $pearDBO;
		$ret = array();
		$ret = $form->getSubmitValues();
		if (isset($ret["default_tpl1"]) && $ret["default_tpl1"])
			noDefaultOreonGraph();

		if ($ret["ds_filled"] == 1 && ($ret["ds_color_area"] == "" || !isset($ret["ds_color_area"])))
			$ret["ds_color_area"] = $ret["ds_color_line"];

		$rq = "INSERT INTO `giv_components_template` ( `compo_id` , `host_id`, `service_id`, `name` , `ds_order` , `ds_hidecurve` , `ds_name` , " .
				" `ds_color_line` , `ds_color_area` , `ds_color_area_warn` , `ds_color_area_crit` , `ds_filled` , `ds_max` , `ds_min` , `ds_minmax_int`, ds_average` , `ds_last` , `ds_total`, `ds_tickness` , `ds_transparency`, `ds_invert`," .
				" `ds_legend` , `ds_jumpline` , `ds_stack`, `default_tpl1`, `comment` ) ";
		$rq .= "VALUES ( NULL, ";
		if ( $ret["index_id"] != NULL ) {
			$sql_qy = $pearDBO->query("SELECT host_id, service_id  FROM index_data WHERE id='".$ret["index_id"]."' LIMIT 1;");
			$hs_id = $sql_qy->fetchRow();
			$sql_qy->free();
			$rq .= "'".$hs_id["host_id"]."', ";
			$rq .= "'".$hs_id["service_id"]."', ";
		} else {
			$rq .= "NULL, NULL, ";
		}
		isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "'".htmlentities($ret["name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_order"]) && $ret["ds_order"] != NULL ? $rq .= "'".htmlentities($ret["ds_order"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_hidecurve"]) && $ret["ds_hidecurve"] != NULL ? $rq .= "'".htmlentities($ret["ds_hidecurve"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_name"]) && $ret["ds_name"] != NULL ? $rq .= "'".$ret["ds_name"]."', ": $rq .= "NULL, ";
		isset($ret["ds_color_line"]) && $ret["ds_color_line"] != NULL ? $rq .= "'".htmlentities($ret["ds_color_line"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_color_area"]) && $ret["ds_color_area"] != NULL ? $rq .= "'".htmlentities($ret["ds_color_area"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_color_area_warn"]) && $ret["ds_color_area_warn"] != NULL ? $rq .= "'".htmlentities($ret["ds_color_area_wrn"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_color_area_crit"]) && $ret["ds_color_area_crit"] != NULL ? $rq .= "'".htmlentities($ret["ds_color_area_crit"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_filled"]) && $ret["ds_filled"] != NULL ? $rq .= "'".htmlentities($ret["ds_filled"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_max"]) && $ret["ds_max"] != NULL ? $rq .= "'".htmlentities($ret["ds_max"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_min"]) && $ret["ds_min"] != NULL ? $rq .= "'".htmlentities($ret["ds_min"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_min"]) && $ret["ds_minmax_int"] != NULL ? $rq .= "'".htmlentities($ret["ds_minmax_int"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_average"]) && $ret["ds_average"] != NULL ? $rq .= "'".htmlentities($ret["ds_average"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_last"]) && $ret["ds_last"] != NULL ? $rq .= "'".htmlentities($ret["ds_last"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_total"]) && $ret["ds_total"] != NULL ? $rq .= "'".htmlentities($ret["ds_total"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_tickness"]) && $ret["ds_tickness"] != NULL ? $rq .= "'".htmlentities($ret["ds_tickness"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_transparency"]) && $ret["ds_transparency"] != NULL ? $rq .= "'".htmlentities($ret["ds_transparency"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_invert"]) && $ret["ds_invert"] != NULL ? $rq .= "'".$ret["ds_invert"]."', ": $rq .= "NULL, ";
		isset($ret["ds_legend"]) && $ret["ds_legend"] != NULL ? $rq .= "'".htmlentities($ret["ds_legend"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_jumpline"]) && $ret["ds_jumpline"] != NULL ? $rq .= "'".htmlentities($ret["ds_jumpline"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["ds_stack"]) && $ret["ds_stack"] != NULL ? $rq .= "'".htmlentities($ret["ds_stack"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		isset($ret["default_tpl1"]) && $ret["default_tpl1"] != NULL ? $rq .= "'".$ret["default_tpl1"]."', ": $rq .= "NULL, ";
		isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES, "UTF-8")."'": $rq .= "NULL";
		$rq .= ")";
fwrite($fh, $rq);
		$DBRESULT = $pearDB->query($rq);
		defaultOreonGraph();
		$DBRESULT = $pearDB->query("SELECT MAX(compo_id) FROM giv_components_template");
		$compo_id = $DBRESULT->fetchRow();
		return ($compo_id["MAX(compo_id)"]);
	}

	function updateComponentTemplate($compo_id = null)	{
		if (!$compo_id) return;
		global $form, $pearDB, $pearDBO;
		$ret = array();
		$ret = $form->getSubmitValues();
		$hs_id = array();
		if ( $ret["index_id"] != NULL ) {
			$sql_qy = $pearDBO->query("SELECT host_id, service_id  FROM index_data WHERE id='".$ret["index_id"]."' LIMIT 1;");
			$hs_id = $sql_qy->fetchRow();
			$sql_qy->free();
		}
		if (isset($ret["default_tpl1"]) && $ret["default_tpl1"])
			noDefaultOreonGraph();

		if ($ret["ds_filled"] == 1 && ($ret["ds_color_area"] == "" || !isset($ret["ds_color_area"])))
			$ret["ds_color_area"] = $ret["ds_color_line"];

		$rq = "UPDATE giv_components_template ";
		$rq .= "SET `host_id` = ";
		isset($hs_id["host_id"]) && $hs_id["host_id"] != NULL ? $rq .= "'".htmlentities($hs_id["host_id"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "`service_id` = ";
		isset($hs_id["service_id"]) && $hs_id["service_id"] != NULL ? $rq .= "'".htmlentities($hs_id["service_id"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "`name` = ";
		isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "'".htmlentities($ret["name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "`ds_order` = ";
		isset($ret["ds_order"]) && $ret["ds_order"] != NULL ? $rq .= "'".htmlentities($ret["ds_order"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "`ds_hidecurve` = ";
		isset($ret["ds_hidecurve"]) && $ret["ds_hidecurve"] != NULL ? $rq .= "'".htmlentities($ret["ds_hidecurve"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .=	"ds_name = ";
		isset($ret["ds_name"]) && $ret["ds_name"] != NULL ? $rq .= "'".htmlentities($ret["ds_name"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "ds_color_line = ";
		isset($ret["ds_color_line"]) && $ret["ds_color_line"] != NULL ? $rq .= "'".$ret["ds_color_line"]."', ": $rq .= "NULL, ";
		$rq .= "ds_color_area = ";
		isset($ret["ds_color_area"]) && $ret["ds_color_area"] != NULL ? $rq .= "'".$ret["ds_color_area"]."', ": $rq .= "NULL, ";
		$rq .= "ds_color_area_warn = ";
		isset($ret["ds_color_area_warn"]) && $ret["ds_color_area_warn"] != NULL ? $rq .= "'".$ret["ds_color_area_warn"]."', ": $rq .= "NULL, ";
		$rq .= "ds_color_area_crit = ";
		isset($ret["ds_color_area_crit"]) && $ret["ds_color_area_crit"] != NULL ? $rq .= "'".$ret["ds_color_area_crit"]."', ": $rq .= "NULL, ";
		$rq .= "ds_filled = ";
		isset($ret["ds_filled"]) && $ret["ds_filled"] != NULL ? $rq .= "'".$ret["ds_filled"]."', ": $rq .= "NULL, ";
		$rq .= "ds_max = ";
		isset($ret["ds_max"]) && $ret["ds_max"] != NULL ? $rq .= "'".$ret["ds_max"]."', ": $rq .= "NULL, ";
		$rq .= "ds_min = ";
		isset($ret["ds_min"]) && $ret["ds_min"] != NULL ? $rq .= "'".$ret["ds_min"]."', ": $rq .= "NULL, ";
		$rq .= "ds_minmax_int = ";
		isset($ret["ds_min"]) && $ret["ds_minmax_int"] != NULL ? $rq .= "'".$ret["ds_minmax_int"]."', ": $rq .= "NULL, ";
		$rq .= "ds_average = ";
		isset($ret["ds_average"]) && $ret["ds_average"] != NULL ? $rq .= "'".$ret["ds_average"]."', ": $rq .= "NULL, ";
		$rq .= "ds_last = ";
		isset($ret["ds_last"]["ds_last"]) && $ret["ds_last"] != NULL ? $rq .= "'".$ret["ds_last"]."', ": $rq .= "NULL, ";
                $rq .= "ds_total = ";
		isset($ret["ds_total"]["ds_total"]) && $ret["ds_total"] != NULL ? $rq .= "'".$ret["ds_total"]."', ": $rq .= "NULL, ";
		$rq .= 	"ds_tickness = ";
		isset($ret["ds_tickness"]) && $ret["ds_tickness"] != NULL ? $rq .= "'".$ret["ds_tickness"]."', ": $rq .= "NULL, ";
		$rq .= 	"ds_transparency = ";
		isset($ret["ds_transparency"]) && $ret["ds_transparency"] != NULL ? $rq .= "'".$ret["ds_transparency"]."', ": $rq .= "NULL, ";
		$rq .= 	"ds_invert = ";
		isset($ret["ds_invert"]) && $ret["ds_invert"] != NULL ? $rq .= "'".$ret["ds_invert"]."', ": $rq .= "NULL, ";
		$rq .=  "ds_legend = ";
		isset($ret["ds_legend"]) && $ret["ds_legend"] != NULL ? $rq .= "'".htmlentities($ret["ds_legend"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .=  "ds_jumpline = ";
		isset($ret["ds_jumpline"]) && $ret["ds_jumpline"] != NULL ? $rq .= "'".htmlentities($ret["ds_jumpline"], ENT_QUOTES, "UTF-8")."', ": $rq .= "NULL, ";
		$rq .= "`ds_stack` = ";
		isset($ret["ds_stack"]) && $ret["ds_stack"] != NULL ? $rq .= "'".$ret["ds_stack"]."', ": $rq .= "NULL, ";
		$rq .= "default_tpl1 = ";
		isset($ret["default_tpl1"]) && $ret["default_tpl1"] != NULL ? $rq .= "'".$ret["default_tpl1"]."', ": $rq .= "NULL, ";
		$rq .= "comment = ";
		isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES, "UTF-8")."' ": $rq .= "NULL ";
		$rq .= "WHERE compo_id = '".$compo_id."'";
		$DBRESULT = $pearDB->query($rq);
		defaultOreonGraph();
	}

?>
