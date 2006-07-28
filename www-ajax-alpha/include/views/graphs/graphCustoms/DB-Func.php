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

For information : contact@oreon-project.org
*/

	function testExistence ($name = NULL)	{
		global $pearDB;
		global $form;
		$id = NULL;
		if (isset($form))
			$id = $form->getSubmitValue('graph_id');
		$res =& $pearDB->query("SELECT graph_id, name FROM giv_graphs WHERE name = '".htmlentities($name, ENT_QUOTES)."'");
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
	
	function deleteGraphInDB ($graphs = array())	{
		global $pearDB;
		foreach($graphs as $key=>$value)
			$pearDB->query("DELETE FROM giv_graphs WHERE graph_id = '".$key."'");
	}
	
	function deleteMetricInDB ($compos = array())	{
		global $pearDB;
		foreach($compos as $key=>$value)
			$pearDB->query("DELETE FROM giv_components WHERE compo_id = '".$key."'");
	}
	
	function upMetricInDB($graph_id = NULL, $compo_id = NULL)	{
		global $pearDB;
		if (!$compo_id || !$graph_id) return;
		$rq = "SELECT compo_id, ds_order FROM giv_components gc WHERE gc.graph_id = '".$graph_id."' ORDER BY ds_order";
		$res =& $pearDB->query($rq);
		$cpt = 20;
		while ($res->fetchInto($compo))	{
			$up = 0;
			if ($compo_id == $compo["compo_id"])
				$up = 15;
			$rq = "UPDATE giv_components SET ds_order = '".($cpt-$up)."' WHERE compo_id = '".$compo["compo_id"]."'";
			$pearDB->query($rq);
			$cpt += 10;
		}
		$res->free();
	}
	
	function downMetricInDB($graph_id = NULL, $compo_id = NULL)	{
		global $pearDB;
		if (!$compo_id || !$graph_id) return;
		$rq = "SELECT compo_id, ds_order FROM giv_components gc WHERE gc.graph_id = '".$graph_id."' ORDER BY ds_order";
		$res =& $pearDB->query($rq);
		$cpt = 20;
		while ($res->fetchInto($compo))	{
			$up = 0;
			if ($compo_id == $compo["compo_id"])
				$up = 15;
			$rq = "UPDATE giv_components SET ds_order = '".($cpt+$up)."' WHERE compo_id = '".$compo["compo_id"]."'";
			$pearDB->query($rq);
			$cpt += 10;
		}
		$res->free();
		
	}
	
	function multipleGraphInDB ($graphs = array(), $nbrDup = array())	{
		foreach($graphs as $key=>$value)	{
			global $pearDB;
			$res =& $pearDB->query("SELECT * FROM giv_graphs WHERE graph_id = '".$key."' LIMIT 1");
			$row = $res->fetchRow();
			$row["graph_id"] = '';
			for ($i = 1; $i <= $nbrDup[$key]; $i++)	{
				$val = null;
				foreach ($row as $key2=>$value2)	{
					$key2 == "name" ? ($name = $value2 = $value2."_".$i) : null;
					$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
				}
				if (testExistence($name))	{
					$val ? $rq = "INSERT INTO giv_graphs VALUES (".$val.")" : $rq = null;
					$pearDB->query($rq);
					$res =& $pearDB->query("SELECT MAX(graph_id) FROM giv_graphs");
					$maxId =& $res->fetchRow();
					if (isset($maxId["MAX(graph_id)"]))	{
						$res =& $pearDB->query("SELECT * FROM giv_components WHERE graph_id = '".$key."'");
						while($res->fetchInto($compo))	{
							$val = null;
							$compo["compo_id"] = '';
							$compo["graph_id"] = $maxId["MAX(graph_id)"];			
							foreach ($compo as $key2=>$value2)
								$val ? $val .= ($value2!=NULL?(", '".$value2."'"):", NULL") : $val .= ($value2!=NULL?("'".$value2."'"):"NULL");
							$val ? $rq = "INSERT INTO giv_components VALUES (".$val.")" : $rq = null;
							$pearDB->query($rq);
						}
					}
				}
			}
		}
	}
	
	function updateGraphInDB ($graph_id = NULL)	{
		if (!$graph_id) return;
		updateGraph($graph_id);
	}	
	
	function insertGraphInDB ()	{
		$graph_id = insertGraph();
		return ($graph_id);
	}	
	
	function insertMetricsInDB ()	{
		insertMetric();
	}
	
	function updateMetricsInDB ($compo_id)	{
		updateMetric($compo_id);
	}
	
	function insertGraph()	{
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "INSERT INTO `giv_graphs` ( `graph_id` , `name`, `grapht_graph_id` , `comment` ) ";
		$rq .= "VALUES (";
		$rq .= "NULL, ";
		isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "'".htmlentities($ret["name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		isset($ret["grapht_graph_id"]) && $ret["grapht_graph_id"] != NULL ? $rq .= "'".$ret["grapht_graph_id"]."', ": $rq .= "NULL, ";
		isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES)."'": $rq .= "NULL";
		$rq .= ")";
		$pearDB->query($rq);
		$res =& $pearDB->query("SELECT MAX(graph_id) FROM giv_graphs");
		$graph_id = $res->fetchRow();
		return ($graph_id["MAX(graph_id)"]);
	}
	
	function updateGraph($graph_id = null)	{
		if (!$graph_id) return;
		global $form;
		global $pearDB;
		$ret = array();
		$ret = $form->getSubmitValues();
		$rq = "UPDATE giv_graphs ";
		$rq .= "SET name = ";
		isset($ret["name"]) && $ret["name"] != NULL ? $rq .= "'".htmlentities($ret["name"], ENT_QUOTES)."', ": $rq .= "NULL, ";
		$rq .=	"grapht_graph_id = ";
		isset($ret["grapht_graph_id"]) && $ret["grapht_graph_id"] != NULL ? $rq .= "'".$ret["grapht_graph_id"]."', ": $rq .= "NULL, ";
		$rq .= "comment = ";
		isset($ret["comment"]) && $ret["comment"] != NULL ? $rq .= "'".htmlentities($ret["comment"], ENT_QUOTES)."' ": $rq .= "NULL ";
		$rq .= "WHERE graph_id = '".$graph_id."'";
		$pearDB->query($rq);
	}
	
	function insertMetric()	{
		global $form1;
		global $pearDB;
		$ret = array();
		$ret = $form1->getSubmitValues();
		$arr1 = array();
		$arr2 = array();
		$arr3 = array();
		if (isset($ret["metric_sel1"]))
			$arr1 =& $ret["metric_sel1"];
		if (isset($ret["metric_sel2"]))
			$arr2 =& $ret["metric_sel2"];
		if (isset($ret["metric_sel3"]))
			$arr3 =& $ret["metric_sel3"];
		
		if ($arr1)	{
			$rq = "INSERT INTO `giv_components` ( `compo_id` , `ds_order`, `graph_id` , `compot_compo_id`, `pp_metric_id` ) ";
			$rq .= "VALUES (";
			$rq .= "NULL, NULL,";
			isset($ret["graph_id"]) && $ret["graph_id"] != NULL ? $rq .= "'".$ret["graph_id"]."', ": $rq .= "NULL, ";
			isset($ret["compot_1"]) && $ret["compot_1"] != NULL ? $rq .= "'".$ret["compot_1"]."', ": $rq .= "NULL, ";
			isset($arr1[1]) && $arr1[1] != NULL ? $rq .= "'".$arr1[1]."'": $rq .= "NULL";
			$rq .= ")";
			$pearDB->query($rq);				
		}
		if ($arr2)	{
			$rq = "INSERT INTO `giv_components` ( `compo_id` , `ds_order`, `graph_id` , `compot_compo_id`, `pp_metric_id` ) ";
			$rq .= "VALUES (";
			$rq .= "NULL, NULL,";
			isset($ret["graph_id"]) && $ret["graph_id"] != NULL ? $rq .= "'".$ret["graph_id"]."', ": $rq .= "NULL, ";
			isset($ret["compot_2"]) && $ret["compot_2"] != NULL ? $rq .= "'".$ret["compot_2"]."', ": $rq .= "NULL, ";
			isset($arr2) && $arr2 != NULL ? $rq .= "'".$arr2."'": $rq .= "NULL";
			$rq .= ")";
			$pearDB->query($rq);				
		}
		if ($arr3)	{
			$rq = "INSERT INTO `giv_components` ( `compo_id` , `ds_order`, `graph_id` , `compot_compo_id`, `pp_metric_id` ) ";
			$rq .= "VALUES (";
			$rq .= "NULL, NULL,";
			isset($ret["graph_id"]) && $ret["graph_id"] != NULL ? $rq .= "'".$ret["graph_id"]."', ": $rq .= "NULL, ";
			isset($ret["compot_3"]) && $ret["compot_3"] != NULL ? $rq .= "'".$ret["compot_3"]."', ": $rq .= "NULL, ";
			isset($arr3) && $arr3 != NULL ? $rq .= "'".$arr3."'": $rq .= "NULL";
			$rq .= ")";
			$pearDB->query($rq);				
		}
	}
	
	function updateMetric($compo_id)	{
		global $form1;
		global $pearDB;
		$ret = array();
		$ret = $form1->getSubmitValues();
		$arr1 = array();
		$arr2 = array();
		$arr3 = array();
		if (isset($ret["metric_sel1"]))
			$arr1 =& $ret["metric_sel1"];
		if (isset($ret["metric_sel2"]))
			$arr2 =& $ret["metric_sel2"];
		if (isset($ret["metric_sel3"]))
			$arr3 =& $ret["metric_sel3"];
		
		if ($arr1)	{
			$rq = "UPDATE `giv_components` SET ";
			$rq .= "`compot_compo_id` = ";
			isset($ret["compot_1"]) && $ret["compot_1"] != NULL ? $rq .= "'".$ret["compot_1"]."', ": $rq .= "NULL, ";
			$rq .= "`pp_metric_id` = ";
			isset($arr1[1]) && $arr1[1] != NULL ? $rq .= "'".$arr1[1]."' ": $rq .= "NULL ";
			$rq .= "WHERE compo_id = '".$compo_id."'";
			$pearDB->query($rq);
		}
		if ($arr2)	{
			$rq = "UPDATE `giv_components` SET ";
			$rq .= "`compot_compo_id` = ";
			isset($ret["compot_2"]) && $ret["compot_2"] != NULL ? $rq .= "'".$ret["compot_2"]."', ": $rq .= "NULL, ";
			$rq .= "`pp_metric_id` = ";
			isset($arr2) && $arr2 != NULL ? $rq .= "'".$arr2."' ": $rq .= "NULL ";
			$rq .= "WHERE compo_id = '".$compo_id."'";
			$pearDB->query($rq);	
		}
		if ($arr3)	{
			$rq = "UPDATE `giv_components` SET ";
			$rq .= "`compot_compo_id` = ";
			isset($ret["compot_3"]) && $ret["compot_3"] != NULL ? $rq .= "'".$ret["compot_3"]."', ": $rq .= "NULL, ";
			$rq .= "`pp_metric_id` = ";
			isset($arr3) && $arr3 != NULL ? $rq .= "'".$arr3."' ": $rq .= "NULL ";
			$rq .= "WHERE compo_id = '".$compo_id."'";
			$pearDB->query($rq);		
		}
	}
/*	
	function getDefaultGraph ($graph_id = NULL)	{		
		if (!$graph_id)	return;
		global $pearDB;
		$gt = array("graph_id"=>NULL, "name"=>NULL);
		$res =& $pearDB->query("SELECT ggt.graph_id, ggt.name FROM giv_graphs_template ggt, giv_graphs gg WHERE gg.graph_id = '".$graph_id."' AND gg.grapht_graph_id = ggt.graph_id");
		if ($res->numRows())	{
			$gt =& $res->fetchRow();
			$res->free();
			return $gt;
		}
		$res =& $pearDB->query("SELECT graph_id, name FROM giv_graphs_template WHERE default_tpl1 = '1' LIMIT 1");
		if ($res->numRows())	{
			$gt =& $res->fetchRow();
			$res->free();
			return $gt;
		} else	{
			$res =& $pearDB->query("SELECT graph_id, nameFROM giv_graphs_template LIMIT 1");
			if ($res->numRows())	{
				$gt =& $res->fetchRow();
				$res->free();
				return $gt;
			}			
		}
		return $gt;
	}
	
	
	function getDefaultDS ($graph_id = NULL, $current_ds = NULL)	{
		if (!$graph_id) return NULL;
		global $pearDB;
		$ds = array();
		$res =& $pearDB->query("SELECT gct.compo_id FROM giv_components_template gct, giv_graphT_componentT_relation ggcr WHERE ggcr.gg_graph_id = '".$graph_id."' AND ggcr.gc_compo_id = gct.compo_id ORDER BY gct.ds_order");
		$cpt = 0;
		$sum = $res->numRows();
		while ($res->fetchInto($ds))	{
			if ($current_ds == $cpt)
				return $ds["compo_id"];
			$cpt++;				 
		}
		$res =& $pearDB->query("SELECT compo_id FROM giv_components_template WHERE default_tpl1 = '1' LIMIT 1");
		if ($res->numRows())	{
			$ds =& $res->fetchRow();
				return $ds["compo_id"];
		}
		$res =& $pearDB->query("SELECT compo_id FROM giv_components_template LIMIT 1");
		if ($res->numRows())	{
			$ds =& $res->fetchRow();
				return $ds["compo_id"];
		}
		return NULL;
	}
*/
?>