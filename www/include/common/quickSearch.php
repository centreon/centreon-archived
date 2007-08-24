<?
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

	if (!isset($oreon))
		exit();
		
	$form_search = new HTML_QuickForm('quickSearchForm', 'GET', "?p=".$p."&o=".$o);
	global $search;
	
	if (isset($_GET["search"]))
		$search = $_GET["search"];
	else if (isset($oreon->historySearch[$url]))
		$search = $oreon->historySearch[$url];
	else
		$search = NULL; 
	
	
	if (!isset($limit))
		$limit = 20;
	
	$tab = array ("search" => $search, "p"=>$p, "o"=>$o, "limit"=>$limit, "search_type_host"=>1, "search_type_service"=>1);
	$form_search->addElement('text', 'search', $lang["quicksearch"], 'id=input_search');
	$form_search->addElement('hidden', 'p');
	$form_search->addElement('hidden', 'limit');
	$form_search->addElement('hidden', 'list');
	$form_search->addElement('hidden', 'o', $o);


	if (	$p == 602 || $p == 60201 || $p == 20201 || $p == 20202 || 
			$p == 20207 || $p == 2020201 ||$p == 2020202 ||$p == 2020203 ||	
			$p == 202 || $p == 2 || $p == 2020101 || 
			$p == 20203 || $p == 2020301 ||$p == 2020302 ||$p == 2020303 ||
			$p == 20204 || $p == 2020401 ||$p == 2020402 ||$p == 2020403 ||
			$p == 20205 || $p == 2020501 ||$p == 2020502 ||$p == 2020503 || 
			$p == 20208 || $p == 2020801 ||$p == 2020802 ||$p == 2020803 ||
			$p == 20209 || $p == 2020901 ||$p == 2020902 ||$p == 2020903 ||
			$p == 20210 || $p == 2021001 ||$p == 2021002 ||$p == 2021003 ||
			$p == 20211 || $p == 2021101 ||$p == 2021102 ||$p == 2021103 ||
			$p == 20212 || $p == 2021201 ||$p == 2021202 ||$p == 2021203 ||
			$p == 20213 || $p == 2021301 ||$p == 2021302 ||$p == 2021303
			) {
		$form_search->addElement('advcheckbox', 'search_type_host', 'host', '', 'class=mini_checkbox');
		$form_search->addElement('advcheckbox', 'search_type_service', 'service', '', 'class=mini_checkbox');
	}

	$form_search->setDefaults($tab);
	
	# Render with a smarty template
	$tpl = new Smarty();
	$tpl = initSmartyTpl("./include/common/", $tpl);
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form_search->accept($renderer);	
	$tpl->assign('form_search', $renderer->toArray());
	$tpl->assign('p', $p);
	$tpl->assign("num", 1);	
	$tpl->display("quickSearch.ihtml");
	
?>