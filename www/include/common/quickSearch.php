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
	
	/*
	 * Init Flag
	 */
		
	$displayHSOptions = 0;
	
	$form_search = new HTML_QuickForm('quickSearchForm', 'GET', "?p=".$p."&o=".$o);
	if (isset($_GET["search"]))
		$search = $_GET["search"];
	else if (isset($oreon->historySearch[$url]))
		$search = $oreon->historySearch[$url];
	else
		$search = NULL; 
	
	if (isset($search) && $search)
		$attrsText = array("size"=>"25", "id"=>"input_search", "class"=>"search_input_active", "style" => "padding-top:1px;padding-bottom:1px;");
	else
		$attrsText = array("size"=>"25", "id"=>"input_search", "class"=>"search_input", "style" => "padding-top:1px;padding-bottom:1px;");
	
	if (!isset($limit))
		$limit = 20;
	
	$tab = array ("search" => $search, "p"=>$p, "o"=>$o, "limit"=>$limit, "search_type_host"=>1, "search_type_service"=>1);
	
	$form_search->addElement('text', 'search', _("Quick Search"), $attrsText);
	$form_search->addElement('hidden', 'p');
	$form_search->addElement('hidden', 'limit');
	$form_search->addElement('hidden', 'list');
	$form_search->addElement('hidden', 'o', $o);

	$tabQuickSearch = array(602 => 1, 60201 => 1, 20201 => 1, 20202 => 1, 20207 => 1, 2020201 => 1, 2020202 => 1, 2020203 => 1, 202 => 1,
							2 => 1, 2020101 => 1, 20203 => 1, 2020301 => 1, 2020302 => 1, 2020303 => 1, 20204 => 1, 2020401 => 1, 
							2020402 => 1, 2020403 => 1, 20205 => 1, 2020501 => 1, 2020502 => 1, 2020503 => 1, 20208 => 1,
							2020801 => 1, 2020802 => 1, 2020803 => 1, 20209 => 1, 2020901 => 1, 2020902 => 1, 2020903 => 1, 
							20210 => 1, 2021001 => 1, 2021002 => 1, 2021003 => 1, 20211 => 1, 2021101 => 1, 2021102 => 1, 
							2021103 => 1, 20212 => 1, 2021201 => 1, 2021202 => 1, 2021203 => 1, 20213 => 1, 2021301 => 1, 
							2021302 => 1, 2021303 => 1);

	if (isset($tabQuickSearch[$p])) {
		$form_search->addElement('advcheckbox', 'search_type_host', 	'host', 	'', 'class=mini_checkbox');
		$form_search->addElement('advcheckbox', 'search_type_service', 	'service', 	'', 'class=mini_checkbox');		
		$displayHSOptions = 1;
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
	$tpl->assign("displayHSOptions", $displayHSOptions);

	$tpl->display("quickSearch.ihtml");
?>