<?php
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

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

	function testExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('graph_id');
		$res =& $pearDB->query("SELECT graph_id, name FROM giv_graphs_template WHERE name = '".htmlentities($name, ENT_QUOTES)."'");
		$graph =& $res->fetchRow();
		#Modif case
		if ($res->numRows() >= 1 && $graph["graph_id"] == $id)	
			return true;
		#Duplicate entry
		else if ($res->numRows() >= 1 && $graph["graph_id"] != $id)
			return false;
		else
			return true;
	}
	
	function deleteGraphTemplateInDB ($graphs = array())	{
		global $pearDB;
		foreach($graphs as $key=>$value)
			$pearDB->query("DELETE FROM giv_graphs_template WHERE graph_id = '".$key."'");
		defaultOreonGraph();
	}
	
	function multipleGraphTemplateInDB ($graphs = array(), $nbrDup = array())	{
		foreach($graphs as $key=>$value)	{
			global $pearDB;
			$res =& $pearDB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$key."' LIMIT 1");
			$row = $res->fetchRow();
			$row["graph_id"] = '';
			$row["default_tpl1"] = '0';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "name" ? ($name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testExistence($name))	{
					$val ? $rq = "INSERT INTO giv_graphs_template VALUES (".$val.")" : $rq = null;
					$pearDB->query($rq);
					$res =& $pearDB->query("SELECT MAX(graph_id) FROM giv_graphs_template");
					$maxId =& $res->fetchRow();
					if (isset($maxId["MAX(graph_id)"]))	{
						$res =& $pearDB->query("SELECT DISTINCT gc_compo_id FROM giv_graphT_componentT_relation WHERE gg_graph_id = '".$key."'");
						while($res->fetchInto($graph))
							$pearDB->query("INSERT INTO giv_graphT_componentT_relation VALUES ('', '".$maxId["MAX(graph_id)"]."', '".$graph["gc_compo_id"]."')");
					}
				}
			}
		}
	}
	
	function defaultOreonGraph ()	{
		global $pearDB;
		$rq = "SELECT DISTINCT graph_id FROM giv_graphs_template WHERE default_tpl1 = '1'";
		$res =& $pearDB->query($rq);
		if (!$res->numRows())	{
			$rq = "UPDATE giv_graphs_template SET default_tpl1 = '1' LIMIT 1";
			$pearDB->query($rq);
		}
	}
	
	function noDefaultOreonGraph ()	{
		global $pearDB;
		$rq = "UPDATE giv_graphs_template SET default_tpl1 = '0'";
		$pearDB->query($rq);
	}
	
	
	function updateGraphTemplateInDB ($graph_id = NULL)	{
		if (!$graph_id) return;
		updateGraphTemplate($graph_id);
		updateComponentChilds($graph_id);
	}	
	
	function insertGraphTemplateInDB ()	{
		$graph_id = insertGraphTemplate();
		updateComponentChilds($graph_id);
		return ($graph_id);
	}
	
	function insertGraphTemplate()	{
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		if ($ret["default_tpl1"]["default_tpl1"])
			noDefaultOreonGraph();
		$rq = "INSERT INTO `giv_graphs_template` ( `graph_id` , `name` , " .
				"`vertical_label` , `width` , `height` , `base` , `lower_limit`, `upper_limit` , `bg_grid_color` , `bg_color` , `police_color` , `grid_main_color` , " .
				"`grid_sec_color` , `contour_cub_color` , `col_arrow` , `col_top` , `col_bot` , `default_tpl1` , `split_component` , " .
				"`stacked` , `comment` ) ";
		$rq .= "VALUES (";
		$rq .= "NULL, ";
		isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "'".htmlentities($ret["name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["vertical_label"]) && $ret["vertical_label"] != NULL ? $rq .= "'".htmlentities($ret["vertical_label"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["width"]) && $ret["width"] != NULL ? $rq .= "'".htmlentities($ret["width"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["height"]) && $ret["height"] != NULL ? $rq .= "'".htmlentities($ret["height"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["base"]) && $ret["base"] != NULL ? $rq .= "'".htmlentities($ret["base"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["lower_limit"]) && $ret["lower_limit"] != NULL ? $rq .= "'".$ret["lower_limit"]."', ": $rq .= "NULL, ";
		isset($ret["upper_limit"]) && $ret["upper_limit"] != NULL ? $rq .= "'".$ret["upper_limit"]."', ": $rq .= "NULL, ";
		isset($ret["bg_grid_color"]) && $ret["bg_grid_color"] != NULL ? $rq .= "'".htmlentities($ret["bg_grid_color"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["bg_color"]) && $ret["bg_color"] != NULL ? $rq .= "'".htmlentities($ret["bg_color"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["police_color"]) && $ret["police_color"] != NULL ? $rq .= "'".htmlentities($ret["police_color"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["grid_main_color"]) && $ret["grid_main_color"] != NULL ? $rq .= "'".htmlentities($ret["grid_main_color"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["grid_sec_color"]) && $ret["grid_sec_color"] != NULL ? $rq .= "'".htmlentities($ret["grid_sec_color"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["contour_cub_color"]) && $ret["contour_cub_color"] != NULL ? $rq .= "'".htmlentities($ret["contour_cub_color"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["col_arrow"]) && $ret["col_arrow"] != NULL ? $rq .= "'".htmlentities($ret["col_arrow"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["col_top"]) && $ret["col_top"] != NULL ? $rq .= "'".htmlentities($ret["col_top"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["col_bot"]) && $ret["col_bot"] != NULL ? $rq .= "'".htmlentities($ret["col_bot"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["default_tpl1"]["default_tpl1"]) && $ret["default_tpl1"]["default_tpl1"] != NULL ? $rq .= "'".$ret["default_tpl1"]["default_tpl1"]."', ": $rq .= "NULL, ";
		isset($ret["split_component"]["split_component"]) && $ret["split_component"]["split_component"] != NULL ? $rq .= "'".$ret["split_component"]["split_component"]."', ": $rq .= "NULL, ";
		isset($ret["stacked"]["stacked"]) && $ret["stacked"]["stacked"] != NULL ? $rq .= "'".htmlentities($ret["stacked"]["stacked"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES)."'": $rq .= "NULL";
		$rq .= ")";
		$pearDB->query($rq);
		defaultOreonGraph();
		$res =& $pearDB->query("SELECT MAX(graph_id) FROM giv_graphs_template");
		$graph_id = $res->fetchRow();
		return ($graph_id["MAX(graph_id)"]);
	}
	
	function updateGraphTemplate($graph_id = null)	{
		if (!$graph_id) return;
		global $form, $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		if ($ret["default_tpl1"]["default_tpl1"])
			noDefaultOreonGraph();
		$rq = "UPDATE giv_graphs_template ";
		$rq .= "SET name = ";
		isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "'".htmlentities($ret["name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= 	"vertical_label = ";
		isset($ret["vertical_label"]) && $ret["vertical_label"] != NULL ? $rq .= "'".htmlentities($ret["vertical_label"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "width = ";
		isset($ret["width"]) && $ret["width"] != NULL ? $rq .= "'".$ret["width"]."', ": $rq .= "NULL, ";
		$rq .= "height = ";
		isset($ret["height"]) && $ret["height"] != NULL ? $rq .= "'".$ret["height"]."', ": $rq .= "NULL, ";
		$rq .= "base = ";
		isset($ret["base"]) && $ret["base"] != NULL ? $rq .= "'".$ret["base"]."', ": $rq .= "NULL, ";
		$rq .= "lower_limit = ";
		isset($ret["lower_limit"]) && $ret["lower_limit"] != NULL ? $rq .= "'".$ret["lower_limit"]."', ": $rq .= "NULL, ";
		$rq .= "upper_limit = ";
		isset($ret["upper_limit"]) && $ret["upper_limit"] != NULL ? $rq .= "'".$ret["upper_limit"]."', ": $rq .= "NULL, ";
		$rq .= "bg_grid_color = ";
		isset($ret["bg_grid_color"]) && $ret["bg_grid_color"] != NULL ? $rq .= "'".htmlentities($ret["bg_grid_color"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "bg_color = ";
		isset($ret["bg_color"]) && $ret["bg_color"] != NULL ? $rq .= "'".htmlentities($ret["bg_color"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "police_color = ";
		isset($ret["police_color"]) && $ret["police_color"] != NULL ? $rq .= "'".htmlentities($ret["police_color"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "grid_main_color = ";
		isset($ret["grid_main_color"]) && $ret["grid_main_color"] != NULL ? $rq .= "'".htmlentities($ret["grid_main_color"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "grid_sec_color = ";
		isset($ret["grid_sec_color"]) && $ret["grid_sec_color"] != NULL ? $rq .= "'".htmlentities($ret["grid_sec_color"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "contour_cub_color = ";
		isset($ret["contour_cub_color"]) && $ret["contour_cub_color"] != NULL ? $rq .= "'".htmlentities($ret["contour_cub_color"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "col_arrow = ";
		isset($ret["col_arrow"]) && $ret["col_arrow"] != NULL ? $rq .= "'".htmlentities($ret["col_arrow"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "col_top = ";
		isset($ret["col_top"]) && $ret["col_top"] != NULL ? $rq .= "'".htmlentities($ret["col_top"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "col_bot = ";
		isset($ret["col_bot"]) && $ret["col_bot"] != NULL ? $rq .= "'".htmlentities($ret["col_bot"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .= "default_tpl1 = ";
		isset($ret["default_tpl1"]["default_tpl1"]) && $ret["default_tpl1"]["default_tpl1"] != NULL ? $rq .= "'".$ret["default_tpl1"]["default_tpl1"]."', ": $rq .= "NULL, ";
		$rq .= "split_component = ";
		isset($ret["split_component"]["split_component"]) && $ret["split_component"]["split_component"] != NULL ? $rq .= "'".$ret["split_component"]["split_component"]."', ": $rq .= "NULL, ";
		$rq .= "stacked = ";
		isset($ret["stacked"]["stacked"]) && $ret["stacked"]["stacked"] != NULL ? $rq .= "'".$ret["stacked"]["stacked"]."', ": $rq .= "NULL, ";
		$rq .= "comment = ";
		isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= "WHERE graph_id = '".$graph_id."'";
		$pearDB->query($rq);
		defaultOreonGraph();
	}			
	
	function updateComponentChilds($graph_id = null)	{
		if (!$graph_id) return;
		global $form, $pearDB;
		$pearDB->query("DELETE FROM giv_graphT_componentT_relation WHERE gg_graph_id = '".$graph_id."'");
		$ret = array();
		$ret = $form->getSubmitValue("graph_compos");
		for($i = 0; $i < count($ret); $i++)	{
			$rq = "INSERT INTO giv_graphT_componentT_relation ";
			$rq .= "(gg_graph_id, gc_compo_id) ";
			$rq .= "VALUES ";
			$rq .= "('".$graph_id."', '".$ret[$i]."')";
			$pearDB->query($rq);
		}
	}
?>