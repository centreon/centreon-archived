<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */
	global $search;

	if (!isset($oreon)) {
		exit();
	}

	/*
	 * Init Flag
	 */
	$displayHSOptions = 0;

	$form_search = new HTML_QuickForm('quickSearchForm', 'POST', "?p=".$p."&o=".$o);
	if (isset($_POST["search"])) {
		$search = $_POST["search"];
	} elseif (isset($_GET["search"])) {
        $search = $_GET["search"];
	} elseif (isset($oreon->historySearch[$url])) {
		$search = $oreon->historySearch[$url];
	} else {
		$search = null;
	}

    $searchRaw = $search;
	$search = mysql_real_escape_string($search);

	if (!isset($search_service)) {
		$search_service = "";
		$search_serviceRaw = "";
	} else {
		$search_serviceRaw = $search_service;
	    $search_service = mysql_real_escape_string($search_service);
	}

	if (isset($search) && $search) {
		if ($p == "4" || $p == "402" || $p == "203")
			$attrsText = array("size"=>"15", "id"=>"input_search", "class"=>"search_input_active_host", "style" => "padding-top:1px;padding-bottom:1px;");
		else
			$attrsText = array("size"=>"15", "id"=>"input_search", "class"=>"search_input_active", "style" => "padding-top:1px;padding-bottom:1px;");
	} else {
		if ($p == "4" || $p == "402" || $p == "203")
			$attrsText = array("size"=>"15", "id"=>"input_search", "class"=>"search_input_host", "style" => "padding-top:1px;padding-bottom:1px;");
		else
			$attrsText = array("size"=>"15", "id"=>"input_search", "class"=>"search_input", "style" => "padding-top:1px;padding-bottom:1px;");
	}

	if (isset($search_service) && $search_service) {
		$attrsText2 = array("size"=>"15", "id"=>"input_service", "class"=>"search_input_active_service", "style" => "padding-top:1px;padding-bottom:1px;", "title" => _("Service Description Search Key"));
	} else {
		$attrsText2 = array("size"=>"15", "id"=>"input_service", "class"=>"search_input_service", "style" => "padding-top:1px;padding-bottom:1px;", "title" => _("Service Description Search Key"));
	}
	$attrsText["title"] = _("Search");
	$attrsSubmit = array("style"=>"display:none;");

	if (!isset($limit)) {
		$limit = 20;
	}

	$tab = array ("search"              => $searchRaw,
				  "search_service"      => $search_serviceRaw,
				  "p"                   => $p,
				  "o"                   => $o,
				  "limit"               => $limit,
				  "search_type_host"    => 1,
				  "search_type_service" => 1);

	$form_search->addElement('text', 'search', _("Quick Search"), $attrsText);
	if (isset($FlagSearchService) && $FlagSearchService) {
		$form_search->addElement('text', 'search_service', _("Quick Search"), $attrsText2);
	}
	$form_search->addElement('submit', 'submit', _("Go"), $attrsSubmit);
	$form_search->addElement('hidden', 'p');
	$form_search->addElement('hidden', 'limit');
	$form_search->addElement('hidden', 'list');
	//$form_search->addElement('hidden', 'o', $o);

	/*
	 * Add specific options for search in commands
	 */
	if ($p == '608' || $p == '60801' || $p == '60802' || $p == '60803') {
	    $form_search->addElement('hidden', 'type');
	    $tab['type'] = 2;
	    if (isset($type)) {
	        $tab['type'] = $type;
	    }
	}

	$tabQuickSearch = array(602 => 1, 60201 => 1, 2020201 => 1, 2020202 => 1, 2020203 => 1, 202 => 1,
							2 => 1, 2020101 => 1, 20203 => 1, 2020301 => 1, 2020302 => 1, 2020303 => 1, 20208 => 1,
							2020801 => 1, 2020802 => 1, 2020803 => 1, 20211 => 1,
							2021101 => 1, 2021102 => 1, 2021103 => 1);


	if (isset($tabQuickSearch[$p])) {
		$form_search->addElement('advcheckbox', 'search_type_host', 	_("host"), 	'', 'class=mini_checkbox');
		$form_search->addElement('advcheckbox', 'search_type_service', 	_("service"), 	'', 'class=mini_checkbox');
		$displayHSOptions = 1;
	}
	$form_search->setDefaults($tab);

	/*
	 * Render with a smarty template
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl("./include/common/", $tpl);

	$renderer = new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form_search->accept($renderer);
	$tpl->assign('form_search', $renderer->toArray());
	$tpl->assign('p', $p);
	$tpl->assign("displayHSOptions", $displayHSOptions);
	$tpl->assign("cleanSearch", _("Reset filters"));
	if (isset($FlagSearchService) && $FlagSearchService) {
		$tpl->assign("FlagSearchService", $FlagSearchService);
	}
	$tpl->display("quickSearch.ihtml");
?>