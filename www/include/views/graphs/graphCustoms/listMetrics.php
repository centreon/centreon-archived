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

	require_once("./DBPerfparseConnect.php");
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl);

	# LCA 
	$lcaHostByName = getLcaHostByName($pearDB);
	
	$form1 = new HTML_QuickForm('select_form', 'POST', "?p=".$p);

	if ($o == "mm" && $compo_id)	{
		$res =& $pearDB->query("SELECT DISTINCT compot_compo_id, pp_metric_id FROM giv_components WHERE compo_id = '".$compo_id."'");
		$compo =& $res->fetchRow();
		$res =& $pearDBpp->query("SELECT * FROM perfdata_service_metric WHERE metric_id = '".$compo["pp_metric_id"]."'");
		if ($res->numRows())	{
			$metric =& $res->fetchRow();
			$def = array();
			if ($metric["host_name"] == "Meta_Module")	{
				$form1->setDefaults(array("meta_service"=>$metric["service_description"], "metric_sel2"=>$metric["metric"], "compot_2"=>$compo["compot_compo_id"]));
				$meta_service = $metric["service_description"];
			}
			else if ($metric["host_name"] == "OSL_Module")	{
				$form1->setDefaults(array("osl"=>$metric["service_description"], "metric_sel3"=>$metric["metric"], "compot_3"=>$compo["compot_compo_id"]));
				$osl = $metric["service_description"];
			}
			else	{
				if (IsHostReadable($lcaHostByName, $metric["host_name"])){
					$form1->setDefaults(array("host_name"=>$metric["host_name"], "metric_sel1"=>array(0=>$metric["service_description"], 1=>$metric["metric"]), "compot_1"=>$compo["compot_compo_id"]));
					$host_name = $metric["host_name"];
				}
			}
		}
		
		$res->free();
	}
		
	if (isset($_POST["submitA"]) && $_POST["submitA"] && $form1->validate() && $compo_id)	{
		updateMetricsInDB($compo_id);
		$o = "cm";
		$compo_id = NULL;
	}	
	else if (isset($_POST["submitA"]) && $_POST["submitA"] && $form1->validate())	{
		insertMetricsInDB();
	}
	
	#
	## Form
	#
	#
		## Database retrieve information for differents elements list we need on the page

		#	
		# Components from DB -> Store in $compos Array
		$compos = array(NULL=>NULL);
		$res =& $pearDB->query("SELECT DISTINCT compo_id, name FROM giv_components_template ORDER BY name");
		while($res->fetchInto($compo))
			$compos[$compo["compo_id"]] = $compo["name"];
		$res->free();		

		#	
		# Resources comes from DB -> Store in $ppHosts Array
		$ppHosts = array(NULL=>NULL);
		$res =& $pearDBpp->query("SELECT DISTINCT host_name FROM perfdata_service_metric ORDER BY host_name");
		while($res->fetchInto($ppHost))	{
			if (IsHostReadable($lcaHostByName, $ppHost["host_name"]))
				$ppHosts[$ppHost["host_name"]] = $ppHost["host_name"]; 
			else if ($ppHost["host_name"] == "Meta_Module")	{
				
			}
		}
		$res->free();		
		$ppServices1 = array();
		$ppServices2 = array();
		if ($host_name)	{
			# Perfparse Host comes from DB -> Store in $ppHosts Array
			$ppServices = array(NULL=>NULL);
			$res =& $pearDBpp->query("SELECT DISTINCT metric_id, service_description, metric, unit FROM perfdata_service_metric WHERE host_name = '".$host_name."' ORDER BY host_name");
			while($res->fetchInto($ppService))	{
				$ppServices1[$ppService["service_description"]] = $ppService["service_description"];
				$ppServices2[$ppService["service_description"]][$ppService["metric_id"]] = $ppService["metric"]."  (".$ppService["unit"].")";
			}
			$res->free();		
		}
		
		# Perfparse Meta Services comes from DB -> Store in $ppMSs Array
		$ppMSs = array(NULL=>NULL);
		$res =& $pearDBpp->query("SELECT DISTINCT service_description FROM perfdata_service_metric WHERE host_name = 'Meta_Module' ORDER BY service_description");
		while($res->fetchInto($ppMS))	{
			$id = explode("_", $ppMS["service_description"]);
			$res2 =& $pearDB->query("SELECT meta_name FROM meta_service WHERE meta_id = '".$id[1]."'");
			$meta =& $res2->fetchRow();
			$ppMSs[$ppMS["service_description"]] = $meta["meta_name"];
			$res2->free();
		}
		$res->free();
		$ppServices3 = array();
		if ($meta_service)	{
			$ppServices = array(NULL=>NULL);
			$res =& $pearDBpp->query("SELECT DISTINCT metric_id, metric, unit FROM perfdata_service_metric WHERE host_name = 'Meta_Module' AND service_description = '".$meta_service."' ORDER BY metric");
			while($res->fetchInto($ppService))	
				$ppServices3[$ppService["metric_id"]] = $ppService["metric"]."  (".$ppService["unit"].")";
			$res->free();		
		}
		
		# Perfparse OSL comes from DB -> Store in $ppOSLs Array
		$ppOSLs = array(NULL=>NULL);
		$res =& $pearDBpp->query("SELECT DISTINCT service_description FROM perfdata_service_metric WHERE host_name = 'OSL_Module' ORDER BY service_description");
		while($res->fetchInto($ppOSL))	{
			$id = explode("_", $ppOSL["service_description"]);
			$res2 =& $pearDB->query("SELECT name FROM osl WHERE osl_id = '".$id[1]."'");
			$OSL =& $res2->fetchRow();
			$ppOSLs[$ppOSL["service_description"]] = $OSL["name"];
			$res2->free();
		}
		$res->free();
		$ppServices4 = array();
		if ($osl)	{
			$ppServices = array(NULL=>NULL);
			$res =& $pearDBpp->query("SELECT DISTINCT metric_id, metric, unit FROM perfdata_service_metric WHERE host_name = 'OSL_Module' AND service_description = '".$osl."' ORDER BY metric");
			while($res->fetchInto($ppService))	
				$ppServices4[$ppService["metric_id"]] = $ppService["metric"]."  (".$ppService["unit"].")";
			$res->free();		
		}
	
	
	$form1->addElement('header', 'title', $lang["mss_add"]);
	$form1->addElement('select', 'host_name', $lang["h"], $ppHosts, array("onChange"=>"this.form.submit()"));
	$sel =& $form1->addElement('hierselect', 'metric_sel1', $lang["sv"]);
	$sel->setOptions(array($ppServices1, $ppServices2));
	$form1->addElement('select', 'compot_1', $lang['giv_ct_name'], $compos);
	$form1->addElement('select', 'meta_service', $lang["ms"], $ppMSs, array("onChange"=>"this.form.submit()"));
	$form1->addElement('select', 'metric_sel2', NULL, $ppServices3);
	$form1->addElement('select', 'compot_2', $lang['giv_ct_name'], $compos);
	$form1->addElement('select', 'osl', $lang["giv_sr_osl"], $ppOSLs, array("onChange"=>"this.form.submit()"));
	$form1->addElement('select', 'metric_sel3', NULL, $ppServices4);
	$form1->addElement('select', 'compot_3', $lang['giv_ct_name'], $compos);
	$form1->addElement('submit', 'submitA', $lang["save"]);
	$form1->addElement('reset', 'reset', $lang["reset"]);

	#
	## Metric List
	#
	
		# start header menu
		$tpl->assign("headerMenu_icone", "<img src='./img/icones/16x16/pin_red.gif'>");
		$tpl->assign("headerMenu_name", $lang['name']);
		$tpl->assign("headerMenu_desc", $lang['description']);
		$tpl->assign("headerMenu_compo", $lang["giv_gg_tpl"]);
		$tpl->assign("headerMenu_metric", $lang['giv_ct_metric']);
		$tpl->assign("headerMenu_options", $lang['options']);
		# end header menu
	
		#List
		$rq = "SELECT compo_id, pp_metric_id, compot_compo_id FROM giv_components gc WHERE gc.graph_id = '".$graph_id."' ORDER BY ds_order";
		$res = & $pearDB->query($rq);
		
		$form2 = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
		#Different style between each lines
		$style = "one";
		#Fill a tab with a mutlidimensionnal Array we put in $tpl
		$elemArr = array();
		for ($i = 0; $res->fetchInto($metric); $i++) {
			$res1 =& $pearDBpp->query("SELECT DISTINCT host_name, service_description, metric FROM perfdata_service_metric WHERE metric_id = '".$metric["pp_metric_id"]."'");
			$ppMetric =& $res1->fetchRow();
			$selectedElements =& $form2->addElement('checkbox', "select[".$metric['compo_id']."]");	
			$moptions = "<a href='oreon.php?p=".$p."&graph_id=".$graph_id."&compo_id=".$metric['compo_id']."&o=up'><img src='img/icones/16x16/arrow_up_green.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
			$moptions .= "<a href='oreon.php?p=".$p."&graph_id=".$graph_id."&compo_id=".$metric['compo_id']."&o=down'><img src='img/icones/16x16/arrow_down_green.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
			$moptions .= "<a href='oreon.php?p=".$p."&graph_id=".$graph_id."&compo_id=".$metric['compo_id']."&o=mm'><img src='img/icones/16x16/document_edit.gif' border='0' alt='".$lang['modify']."'></a>&nbsp;&nbsp;";
			$moptions .= "<a href='oreon.php?p=".$p."&graph_id=".$graph_id."&compo_id=".$metric['compo_id']."&o=dm&select[".$metric['compo_id']."]=1' onclick=\"return confirm('".$lang['confirm_removing']."')\"><img src='img/icones/16x16/delete.gif' border='0' alt='".$lang['delete']."'></a>&nbsp;&nbsp;";
			if ($ppMetric["host_name"] == "Meta_Module")	{
				$name = explode("_", $ppMetric["service_description"]);
				$res2 =& $pearDB->query("SELECT DISTINCT meta_name FROM meta_service WHERE meta_id = '".$name[1]."'");
				$name =& $res2->fetchRow();
				$ppMetric["service_description"] = $name["meta_name"];
				$host_name = $lang["giv_gg_ms"];
			}
			else if ($ppMetric["host_name"] == "OSL_Module")	{
				$host_name = $lang["giv_gg_osl"];
				$name = explode("_", $ppMetric["service_description"]);
				$res2 =& $pearDB->query("SELECT DISTINCT name FROM osl WHERE osl_id = '".$name[1]."'");
				$name =& $res2->fetchRow();
				$ppMetric["service_description"] = $name["name"];
			}
			else
				$host_name = $ppMetric["host_name"];
			$elemArr[$i] = array("MenuClass"=>"list_".$style, 
							"RowMenu_select"=>$selectedElements->toHtml(),
							"RowMenu_name"=> $host_name,
							"RowMenu_link"=>"?p=".$p."&o=w&graph_id=".$metric['compo_id'],
							"RowMenu_desc"=>$ppMetric["service_description"],
							"RowMenu_metric"=>$ppMetric["metric"],
							"RowMenu_compo"=>$metric["compot_compo_id"] ? $lang["yes"] : $lang["no"],
							"RowMenu_options"=>$moptions);
			$style != "two" ? $style = "two" : $style = "one";
		}
		$tpl->assign("elemArr", $elemArr);
		#Different messages we put in the template
		$tpl->assign('msg', array ("addL"=>"?p=".$p."&o=a", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));
	
	#Element we need when we reload the page
	$form1->addElement('hidden', 'p');
	$form2->addElement('hidden', 'p');
	$form1->addElement('hidden', 'o');
	$form1->addElement('hidden', 'graph_id');
	if ($o== "mm")
		$form1->addElement('hidden', 'compo_id');
	$tab1 = array ("p" => $p, "o" => $o, "graph_id"=>$graph_id, "compo_id"=>$compo_id);
	$tab2 = array ("p" => $p);
	$form1->setDefaults($tab1);	
	$form2->setDefaults($tab2);	

	#
	##Apply a template definition
	#
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form2->accept($renderer);	
	$form1->accept($renderer);
	$tpl->assign('form1', $renderer->toArray());
	$tpl->assign('form2', $renderer->toArray());
	$tpl->display("listMetrics.ihtml");
?>
