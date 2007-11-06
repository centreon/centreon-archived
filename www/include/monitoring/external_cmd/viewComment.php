<?
/**
Oreon is developped with GPL Licence 2.0 :
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

	$tab_comments_host = array();
	$tab_comments_svc = array();

	if (file_exists($oreon->Nagioscfg["comment_file"]))	{
		$log = fopen($oreon->Nagioscfg["comment_file"], "r");
		$i = 0;
		$i2 = 0;
		if ($oreon->user->get_version() == 1){
			while ($str = fgets($log))	{
				$res = preg_split("/;/", $str);
				if (preg_match("/^\[([0-9]*)\] HOST_COMMENT;/", $str, $matches)){
					$selectedElements =& $form->addElement('checkbox', "select[".$res[1]."]");
					$tab_comments_host[$i] = array();
					$tab_comments_host[$i]["id"] = $res[1];
					$tab_comments_host[$i]["host_name"] = $res[2];
					$tab_comments_host[$i]["time"] = date("d-m-Y G:i:s", $matches[1]);
					$tab_comments_host[$i]["author"] = $res[4];
					$tab_comments_host[$i]["comment"] = $res[5];
					$tab_comments_host[$i]["persistent"] = $res[3];
					$i++;
				} else if (preg_match("/^\[([0-9]*)\] SERVICE_COMMENT;/", $str, $matches)){
					$selectedElements =& $form->addElement('checkbox', "select[".$res[1]."]");
					$tab_comments_svc[$i] = array();
					$tab_comments_svc[$i]["id"] = $res[1];
					$tab_comments_svc[$i]["host_name"] = $res[2];
					$tab_comments_svc[$i]["service_descr"] = $res[3];
					$tab_comments_svc[$i]["time"] = date("d-m-Y G:i:s", $matches[1]);
					$tab_comments_svc[$i]["author"] = $res[5];
					$tab_comments_svc[$i]["comment"] = $res[6];
					$tab_comments_svc[$i]["persistent"] = $res[4];
					$i++;
				}
			}
		} else {
			$flag_host = 0;
          	$flag_svc = 0;

			while ($str = fgets($log))	{
                if (preg_match("/^hostcomment/", $str)){
                	$tab_comments_host[$i] = array();
                    $flag_host = 1;
                } else if (preg_match("/^servicecomment /", $str)){
                	$tab_comments_svc[$i2] = array();
                    $flag_svc = 1;
                } else {
                    if($flag_host == 1){
                    	$res = preg_split("/=/", $str);
                      	$res[0] = trim($res[0]);
                      	if (isset($res[1]))
                      		$res[1] = trim($res[1]);
                        if (preg_match('`comment_id$`', $res[0])){
                            $selectedElements =& $form->addElement('checkbox', "select[".$res[1]."]");
                            $tab_comments_host[$i]["id"] = $res[1];}
                        if (preg_match('`host_name$`', $res[0]))
                          $tab_comments_host[$i]["host_name"] = $res[1];
                        if (preg_match('`entry_time$`', $res[0]))
	                        $tab_comments_host[$i]["time"] = date("d-m-Y G:i:s", $res[1]);
                        if (preg_match('`author$`', $res[0]))
                        	$tab_comments_host[$i]["author"] = $res[1];
                        if (preg_match('`comment_data$`', $res[0]))
                        	$tab_comments_host[$i]["comment"] = $res[1];
                        if (preg_match('`persistent$`', $res[0]))
                        	$tab_comments_host[$i]["persistent"] = $res[1];
                        if (preg_match('`}$`', $str)){
                            $flag_host = 0;
                            $i++;
                        }
                    } else if($flag_svc == 1) {
                      	$res = preg_split("/=/", $str);
                      	$res[0] = trim($res[0]);
                      	if (isset($res[1]))
                      		$res[1] = trim($res[1]);
                        if (preg_match('`comment_id$`', $res[0])){
                            $selectedElements =& $form->addElement('checkbox', "select[".$res[1]."]");
                            $tab_comments_svc[$i2]["id"] = $res[1];}
                        if (preg_match('`service_description$`', $res[0])){
                          $tab_comments_svc[$i2]["service_descr"] = $res[1];}
                        if (preg_match('`host_name$`', $res[0]))
                          $tab_comments_svc[$i2]["host_name"] = $res[1];
                        if (preg_match('`entry_time$`', $res[0]))
                        	$tab_comments_svc[$i2]["time"] = date("d-m-Y G:i:s", $res[1]);
                        if (preg_match('`author$`', $res[0]))
                        	$tab_comments_svc[$i2]["author"] = $res[1];
                        if (preg_match('`comment_data$`', $res[0]))
                        	$tab_comments_svc[$i2]["comment"] = $res[1];
                        if (preg_match('`persistent$`', $res[0]))
                        	$tab_comments_svc[$i2]["persistent"] = $res[1];
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
		$tab_comments_host2 = array();
		for($n=0,$i=0; $i < count($tab_comments_host); $i++) {
			if(isset($lcaHostByName["LcaHost"][$tab_comments_host[$i]["host_name"]]))
				$tab_comments_host2[$n++] = $tab_comments_host[$i];
		}
		$tab_comments_svc2 = array();
		for($n=0,$i=0; $i < count($tab_comments_svc); $i++) {
			if(isset($lcaHostByName["LcaHost"][$tab_comments_svc[$i]["host_name"]]))
				$tab_comments_svc2[$n++] = $tab_comments_svc[$i];
		}

		$tab_comments_host = $tab_comments_host2;
		$tab_comments_svc = $tab_comments_svc2;
	}

	#Element we need when we reload the page
	$form->addElement('hidden', 'p');
	$tab = array ("p" => $p);
	$form->setDefaults($tab);

	$tpl->assign('msgh', array ("addL"=>"?p=".$p."&o=ah", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));
	$tpl->assign('msgs', array ("addL"=>"?p=".$p."&o=as", "addT"=>$lang['add'], "delConfirm"=>$lang['confirm_removing']));
	$tpl->assign("p", $p);
	$tpl->assign("tab_comments_host", $tab_comments_host);
	$tpl->assign("tab_comments_svc", $tab_comments_svc);

	$tpl->assign("nb_comments_host", count($tab_comments_host));
	$tpl->assign("nb_comments_svc", count($tab_comments_svc));

	$tpl->assign("no_host_comments", $lang["no_host_comments"]);
	$tpl->assign("no_svc_comments", $lang["no_svc_comments"]);

	$tpl->assign("delete", $lang['delete']);

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("comments.ihtml");
?>