<?php
/*
 * Copyright 2005-2010 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */
	global $search;

	if (!isset($oreon))
		exit();

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
	}
	else {
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
	$attrsText["title"] = _("Host Name Search Key");
	$attrsSubmit = array("style"=>"display:none;");

	if (!isset($limit))
		$limit = 20;

	$tab = array ("search"              => $searchRaw,
				  "search_service"      => $search_serviceRaw,
				  "p"                   => $p,
				  "o"                   => $o,
				  "limit"               => $limit,
				  "search_type_host"    => 1,
				  "search_type_service" => 1);

	$form_search->addElement('text', 'search', _("Quick Search"), $attrsText);
	if (isset($FlagSearchService) && $FlagSearchService)
		$form_search->addElement('text', 'search_service', _("Quick Search"), $attrsText2);
	$form_search->addElement('submit', 'submit', _("Go"), $attrsSubmit);
	$form_search->addElement('hidden', 'p');
	$form_search->addElement('hidden', 'limit');
	$form_search->addElement('hidden', 'list');
	$form_search->addElement('hidden', 'o', $o);

	$tabQuickSearch = array(602 => 1, 60201 => 1, 20207 => 1, 2020201 => 1, 2020202 => 1, 2020203 => 1, 202 => 1,
							2 => 1, 2020101 => 1, 20203 => 1, 2020301 => 1, 2020302 => 1, 2020303 => 1, 20208 => 1, 2020801 => 1, 2020802 => 1,
							2020803 => 1, 20211 => 1,
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

	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form_search->accept($renderer);
	$tpl->assign('form_search', $renderer->toArray());
	$tpl->assign('p', $p);
	$tpl->assign("displayHSOptions", $displayHSOptions);
	if (isset($FlagSearchService) && $FlagSearchService)
		$tpl->assign("FlagSearchService", $FlagSearchService);
	$tpl->display("quickSearch.ihtml");
?>