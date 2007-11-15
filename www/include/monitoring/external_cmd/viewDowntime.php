<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

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
	if (!isset($oreon))
		exit();

	# LCA
	$lcaHostByName = getLcaHostByName($pearDB);
	$isRestreint = HadUserLca($pearDB);

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "templates/");
	$tpl->assign("lang", $lang);

	#Pear library
	require_once "HTML/QuickForm.php";
	require_once 'HTML/QuickForm/advmultiselect.php';
	require_once 'HTML/QuickForm/Renderer/ArraySmarty.php';

	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);

	$tab_downtime_host = array();
	$tab_downtime_svc = array();

	if (file_exists($oreon->Nagioscfg["downtime_file"]))	{
		$log = fopen($oreon->Nagioscfg["downtime_file"], "r");
		$i = 0;
		$i2 = 0;
		if ($oreon->user->get_version() == 1)
		{
			while ($str = fgets($log))	{
				$res = preg_split("/;/", $str);
				if (preg_match("/^\[([0-9]*)\] HOST_DOWNTIME;/", $str, $matches) && IsHostReadable($lcaHostByName, $res[2])){
					$selectedElements =& $form->addElement('checkbox', "select[".$res[1]."]");
					$tab_downtime_host[$i] = array();
					$tab_downtime_host[$i]["id"] = $res[1];
					$tab_downtime_host[$i]["host_name"] = $res[2];
					$tab_downtime_host[$i]["time"] = date("d-m-Y G:i:s", $matches[1]);
					$tab_downtime_host[$i]["start"] = date("d-m-Y G:i:s", $res[3]);
					$tab_downtime_host[$i]["end"] = date("d-m-Y G:i:s", $res[4]);
					$tab_downtime_host[$i]["len"] = $res[6];
					$tab_downtime_host[$i]["author"] = $res[7];
					$tab_downtime_host[$i]["comment"] = $res[8];
					$tab_downtime_host[$i]["persistent"] = $res[5];
					$i++;
				} else if (preg_match("/^\[([0-9]*)\] SERVICE_DOWNTIME;/", $str, $matches) && IsHostReadable($lcaHostByName, $res[2])){
					$selectedElements =& $form->addElement('checkbox', "select[".$res[1]."]");
					$tab_downtime_svc[$i] = array();
					$tab_downtime_svc[$i]["id"] = $res[1];
					$tab_downtime_svc[$i]["host_name"] = $res[2];
					$tab_downtime_svc[$i]["svc_name"] = $res[3];
					$tab_downtime_svc[$i]["time"] = date("d-m-Y G:i:s", $matches[1]);
					$tab_downtime_svc[$i]["start"] = date("d-m-Y G:i:s", $res[4]);
					$tab_downtime_svc[$i]["end"] = date("d-m-Y G:i:s", $res[5]);
					$tab_downtime_svc[$i]["len"] = $res[7];
					$tab_downtime_svc[$i]["author"] = $res[8];
					$tab_downtime_svc[$i]["comment"] = $res[9];
					$tab_downtime_svc[$i]["persistent"] = $res[6];
					$i++;
				}
			}
		} else {
			$flag_host = 0;
          	$flag_svc = 0;
			while ($str = fgets($log))	{
                if (preg_match("/^hostdowntime/", $str)){
                	$tab_downtime_host[$i] = array();
                    $flag_host = 1;
                } else if (preg_match("/^servicedowntime/", $str))	{
                    $tab_downtime_svc[$i2] = array();
                    $flag_svc = 1;
                } else {
					if($flag_host == 1) {
                    	$res = preg_split("/=/", $str);
                    	$res[0] = trim($res[0]);
                      	if (isset($res[1]))
                      		$res[1] = trim($res[1]);
                        if (preg_match('`downtime_id$`', $res[0])){
                            $selectedElements =& $form->addElement('checkbox', "select[".$res[1]."]");
                            $tab_downtime_host[$i]["id"] = $res[1];}
                        if (preg_match('`host_name$`', $res[0])){
                          	$tab_downtime_host[$i]["host_name"] = $res[1];}
                        if (preg_match('`entry_time$`', $res[0])){
	                        $tab_downtime_host[$i]["time"] = date("d-m-Y G:i:s", $res[1]);
	                        $cmd = "SELECT downtime_id from downtime where entry_time = ".$res[1]." ";
                        	$result =& $pearDB->query($cmd);
                        	if (PEAR::isError($result))
								print "Mysql Error : ".$result->getMessage();
                        	$result->fetchInto($id_downtime);
                        	$tab_downtime_host[$i]["id_supp"] = $id_downtime["downtime_id"];
                        }
                        if (preg_match('`start_time$`', $res[0])){$tab_downtime_host[$i]["start"] = date("d-m-Y G:i:s", $res[1]);}
                        if (preg_match('`end_time$`', $res[0])){$tab_downtime_host[$i]["end"] = date("d-m-Y G:i:s", $res[1]);}
                        if (preg_match('`triggered_by$`', $res[0])){
    //                      $tab_downtime_host[$i]["..."] = $res[1];
                        }
                        if (preg_match('`fixed$`', $res[0])){$tab_downtime_host[$i]["persistent"] = $res[1];}
                        if (preg_match('`duration$`', $res[0])){$tab_downtime_host[$i]["len"] = $res[1];}
                        if (preg_match('`author$`', $res[0])){$tab_downtime_host[$i]["author"] = $res[1];}
                        if (preg_match('`comment$`', $res[0])){$tab_downtime_host[$i]["comment"] = $res[1];}
                        if (preg_match('`}$`', $str)){
                            $flag_host = 0;
                            $i++;
                        }
                    } else if ($flag_svc == 1){
                      $res = preg_split("/=/", $str);
                      $res[0] = trim($res[0]);
                      if (isset($res[1]))
                      	$res[1] = trim($res[1]);
                        if (preg_match('`downtime_id$`', $res[0])){
                            $selectedElements =& $form->addElement('checkbox', "select[".$res[1]."]");
                            $tab_downtime_svc[$i2]["id"] = $res[1];}
                        if (preg_match('`service_description$`', $res[0])){
                          $tab_downtime_svc[$i2]["svc_name"] = $res[1];}
                        if (preg_match('`host_name$`', $res[0])){
                          $tab_downtime_svc[$i2]["host_name"] = $res[1];}
                        if (preg_match('`entry_time$`', $res[0])){
                        	$tab_downtime_svc[$i2]["time"] = date("d-m-Y G:i:s", $res[1]);
                        	$cmd = "SELECT downtime_id from downtime where entry_time = ".$res[1]." ";
                        	$result =& $pearDB->query($cmd);
                        	if (PEAR::isError($pearDB)) {
								print "Mysql Error : ".$pearDB->getMessage();
							}
                        	$result->fetchInto($id_downtime);
                        	$tab_downtime_svc[$i2]["id_supp"] = $id_downtime["downtime_id"];

                        }
                        if (preg_match('`start_time$`', $res[0])){$tab_downtime_svc[$i2]["start"] = date("d-m-Y G:i:s", $res[1]);}
                        if (preg_match('`end_time$`', $res[0])){$tab_downtime_svc[$i2]["end"] = date("d-m-Y G:i:s", $res[1]);}
                        if (preg_match('`triggered_by$`', $res[0])){
    //                      $tab_downtime_host[$i]["..."] = $res[1];
                        }
                        if (preg_match('`fixed$`', $res[0])){$tab_downtime_svc[$i2]["persistent"] = $res[1];}
                        if (preg_match('`duration$`', $res[0])){$tab_downtime_svc[$i2]["len"] = $res[1];}
                        if (preg_match('`author$`', $res[0])){$tab_downtime_svc[$i2]["author"] = $res[1];}
                        if (preg_match('`comment$`', $res[0])){$tab_downtime_svc[$i2]["comment"] = $res[1];}
                        if (preg_match('`}$`', $str)){
                            $flag_svc = 0;
                        	$i2++;
                        }
                    }
                }
			}
		}
	}


	if (!$oreon->user->admin || $isRestreint){
		$tab_downtime_host2 = array();
		for($n=0,$i=0; $i < count($tab_downtime_host); $i++) {
			if(isset($lcaHostByName["LcaHost"][$tab_downtime_host[$i]["host_name"]]))
				$tab_downtime_host2[$n++] = $tab_downtime_host[$i];
		}
		$tab_downtime_svc2 = array();
		for($n=0,$i=0; $i < count($tab_downtime_svc); $i++) {
			if(isset($lcaHostByName["LcaHost"][$tab_downtime_svc[$i]["host_name"]]))
				$tab_downtime_svc2[$n++] = $tab_downtime_svc[$i];
		}

		$tab_downtime_host = $tab_downtime_host2;
		$tab_downtime_svc = $tab_downtime_svc2;
	}

	#Element we need when we reload the page
	$form->addElement('hidden', 'p');
	$tab = array ("p" => $p);
	$form->setDefaults($tab);

	$tpl->assign('msgh', array ("addL"=>"?p=".$p."&o=ah", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));
	$tpl->assign('msgs', array ("addL"=>"?p=".$p."&o=as", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));
	$tpl->assign("p", $p);
	$tpl->assign("tab_downtime_host", $tab_downtime_host);
	$tpl->assign("tab_downtime_svc", $tab_downtime_svc);
	$tpl->assign("nb_downtime_host", count($tab_downtime_host));
	$tpl->assign("nb_downtime_svc", count($tab_downtime_svc));
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("downtime.ihtml");
?>