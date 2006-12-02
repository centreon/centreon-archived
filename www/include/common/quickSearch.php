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

	/* start quickSearch form */	
	$form_search = new HTML_QuickForm('quickSearchForm', 'GET', "?p=".$p."&o=".$o);
	
	$tab = array ("search" => $search, "p"=>$p, "o"=>$o, "limit"=>$limit, "search_type_host"=>1, "search_type_service"=>1);
	$form_search->addElement('text', 'search', $lang["quicksearch"]);
	$form_search->addElement('hidden', 'p');
	$form_search->addElement('hidden', 'limit');
	$form_search->addElement('hidden', 'list');
	$form_search->addElement('hidden', 'o', $o);

	if ($p == 602 || $p == 60201 || $p == 20201 || $p == 20202|| $p == 202|| $p == 2) {
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
	$tpl->display("quickSearch.ihtml");

	/* end quickSearch form*/	
?>