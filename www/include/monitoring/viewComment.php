<?
/** 
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon.org
*/
	if (!isset($oreon))
		exit();

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
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);	
	$tpl->assign('form', $renderer->toArray());
	
	$tpl->display("comments.ihtml");
?>