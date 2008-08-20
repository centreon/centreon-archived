<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	global $search;
	
	if (!isset($oreon))
		exit();
		
	$form_search = new HTML_QuickForm('quickSearchForm', 'GET', "?p=".$p."&o=".$o);
	if (isset($_GET["search"]))
		$search = $_GET["search"];
	else if (isset($oreon->historySearch[$url]))
		$search = $oreon->historySearch[$url];
	else
		$search = NULL; 
	
	if (isset($search) && $search)
		$attrsText = array("size"=>"25", "id"=>"input_search", "class"=>"search_input_active");
	else
		$attrsText = array("size"=>"25", "id"=>"input_search", "class"=>"search_input");
	
	if (!isset($limit))
		$limit = 20;
	
	$tab = array ("search" => $search, "p"=>$p, "o"=>$o, "limit"=>$limit, "search_type_host"=>1, "search_type_service"=>1);
	
	$form_search->addElement('text', 'search', _("Quick Search"), $attrsText);
	$form_search->addElement('hidden', 'p');
	$form_search->addElement('hidden', 'limit');
	$form_search->addElement('hidden', 'list');
	$form_search->addElement('hidden', 'o', $o);

	$tabQuickSearch = array(602, 60201, 20201, 20202, 20207, 2020201, 2020202, 2020203, 202,
							2, 2020101, 20203, 2020301, 2020302, 2020303, 20204, 2020401, 
							2020402, 2020403, 20205, 2020501, 2020502, 2020503, 20208,
							2020801, 2020802, 2020803, 20209, 2020901, 2020902, 2020903, 
							20210, 2021001, 2021002, 2021003, 20211, 2021101, 2021102, 
							2021103, 20212, 2021201, 2021202, 2021203, 20213, 2021301, 
							2021302, 2021303);

	if (isset($tabQuickSearch[$p])) {
		$form_search->addElement('advcheckbox', 'search_type_host', 	'host', 	'', 'class=mini_checkbox');
		$form_search->addElement('advcheckbox', 'search_type_service', 	'service', 	'', 'class=mini_checkbox');		
	}	
	$form_search->setDefaults($tab);
	
	/*
	 * Render with a smarty template
	 */

	$tpl = new Smarty();
	$tpl = initSmartyTpl("./include/common/", $tpl);
	
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form_search->accept($renderer);	
	$tpl->assign('form_search', $renderer->toArray());
	$tpl->assign('p', $p);

	$tpl->display("quickSearch.ihtml");
?>