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
/*
	function getDefaultGraph ()	{
		global $pearDB;
		$gt = array("graph_id"=>NULL, "name"=>NULL);
		$res =& $pearDB->query("SELECT graph_id, name FROM giv_graphs_template WHERE default_tpl1 = '1' LIMIT 1");
		if ($res->numRows())	{
			$gt =& $res->fetchRow();
			$res->free();
			return $gt;
		}
		$res =& $pearDB->query("SELECT graph_id, name FROM giv_graphs_template LIMIT 1");
		if ($res->numRows())	{
			$gt =& $res->fetchRow();
			$res->free();
			return $gt;
		}
		return $gt;
	}
	
	function getDefaultDS ($graph_id = NULL, $current_ds = NULL)	{
		if (!$graph_id) return NULL;
		global $pearDB;
		$ds = array();
		$res =& $pearDB->query("SELECT gct.compo_id, gct.name FROM giv_components_template gct, giv_graphT_componentT_relation ggcr WHERE ggcr.gg_graph_id = '".$graph_id."' AND ggcr.gc_compo_id = gct.compo_id ORDER BY gct.ds_order");
		$cpt = 0;
		$sum = $res->numRows();
		while ($res->fetchInto($ds))	{
			if ($current_ds == $cpt)
				return $ds;
			$cpt++;				 
		}
		$res =& $pearDB->query("SELECT compo_id, name FROM giv_components_template WHERE default_tpl1 = '1' LIMIT 1");
		if ($res->numRows())	{
			$ds =& $res->fetchRow();
			return $ds;
		}
		$res =& $pearDB->query("SELECT compo_id, name FROM giv_components_template LIMIT 1");
		if ($res->numRows())	{
			$ds =& $res->fetchRow();
			return $ds;
		}
		return NULL;
	}
*/
?>