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

	global $oreon;

	if (!isset($oreon))
		exit();

	global $num, $limit, $search, $url, $pearDB, $search_type_service, $search_type_host, $host_name, $rows, $p, $gopt, $pagination;
	
	isset($_GET["type"]) ? $type = $_GET["type"] : $stype = NULL;
	isset($_GET["o"]) ? $o = $_GET["o"] : $o = NULL;
	
	if (isset($_GET["num"]))
		$num = $_GET["num"];
	else if (!isset($_GET["num"]) && isset($oreon->historyPage[$url]) && $oreon->historyPage[$url])
		$num = $oreon->historyPage[$url];
	else
		$num = 0;
	
	$num = mysql_real_escape_string($num);
	
	$tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc"); 	

	if (isset($_GET["search_type_service"])){
		$search_type_service = $_GET["search_type_service"];
		$oreon->search_type_service = $_GET["search_type_service"];
	} else if (isset($oreon->search_type_service))
		 $search_type_service = $oreon->search_type_service;
	else
		$search_type_service = NULL;

	if (isset($_GET["search_type_host"])){
		$search_type_host = $_GET["search_type_host"];
		$oreon->search_type_host = $_GET["search_type_host"];
	} else if (isset($oreon->search_type_host))
		 $search_type_host = $oreon->search_type_host;
	else
		$search_type_host = NULL;
	
	if (!isset($_GET["search_type_host"]) && !isset($oreon->search_type_host) && !isset($_GET["search_type_service"]) && !isset($oreon->search_type_service)){
		$search_type_host = 1;
		$oreon->search_type_host = 1;
		$search_type_service = 1;
		$oreon->search_type_service = 1;
	}

	$url_var = "";
	$url_var .= "&search_type_service=" . $search_type_service;
	$url_var .= "&search_type_host=" . $search_type_host;
	
	if (isset($_GET["order"])){
		$url_var .= "&order=".$_GET["order"];
		$order = $_GET["order"];
	}
	if (isset($_GET["sort_types"])){
		$url_var .= "&sort_types=".$_GET["sort_types"];
		$sort_type = $_GET["sort_types"];
	}
	
	/*
	 * Smarty template Init
	 */
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./include/common/");

	$page_max = ceil($rows / $limit);
	if ($num > $page_max && $rows)
		$num = $page_max - 1;
		
	$pageArr = array();
	$istart = 0;
	for ($i = 5, $istart = $num; $istart && $i > 0; $i--)
		$istart--;
	for ($i2 = 0, $iend = $num; ( $iend <  ($rows / $limit - 1)) && ( $i2 < (5 + $i)); $i2++)
		$iend++;
	for ($i = $istart; $i <= $iend; $i++)
		$pageArr[$i] = array("url_page"=>"./main.php?p=".$p."&num=$i&limit=".$limit."&search=".$search."&type=".$type."&o=" . $o . $url_var, "label_page"=>"<b>".($i +1)."</b>","num"=> $i);
	
	if ($i > 1)							
		$tpl->assign("pageArr", $pageArr);

	$tpl->assign("num", $num);
	$tpl->assign("first", _("First page"));
	$tpl->assign("previous", _("Previous page"));
	$tpl->assign("next", _("Next page"));
	$tpl->assign("last", _("Last page"));
	
	if (($prev = $num - 1) >= 0)
		$tpl->assign('pagePrev', ("./main.php?p=".$p."&num=$prev&limit=".$limit."&search=".$search."&type=".$type."&o=" . $o .$url_var));
	
	if (($next = $num + 1) < ($rows/$limit))
		$tpl->assign('pageNext', ("./main.php?p=".$p."&num=$next&limit=".$limit."&search=".$search."&type=".$type."&o=" . $o .$url_var));
	
	$pageNumber = ceil($rows / $limit);
	if (($rows / $limit) > 0)
		$tpl->assign('pageNumber', ($num +1)."/".$pageNumber);
	else
		$tpl->assign('pageNumber', ($num)."/".$pageNumber);
	
	if ($page_max > 5 && $num != 0)
		$tpl->assign('firstPage', ("./main.php?p=".$p."&num=0&limit=".$limit."&search=".$search."&type=".$type."&o=" . $o .$url_var));
	if ($page_max > 5 && $num != ($pageNumber-1))
		$tpl->assign('lastPage', ("./main.php?p=".$p."&num=".($pageNumber-1)."&limit=".$limit."&search=".$search."&type=".$type."&o=" . $o .$url_var));
	
	/*
	 * Select field to change the number of row on the page
	 */
	for ($i = 10; $i <= 100; $i = $i +10)
		$select[$i]=$i;
	if (isset($gopt[$pagination]) && $gopt[$pagination])
		$select[$gopt[$pagination]] = $gopt[$pagination];
	if (isset($rows) && $rows)
		$select[$rows] = $rows;
	ksort($select);
	
	?><script type="text/javascript">
	function setL(_this){
		var _l = document.getElementsByName('l');
		document.forms['form'].elements['limit'].value = _this;
		_l[0].value = _this;
		_l[1].value = _this;
	}
	</SCRIPT>
	<?php
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p."&search_type_service=" . $search_type_service."&search_type_host=" . $search_type_host);
	$selLim =& $form->addElement('select', 'l', _("number per page"), $select, array("onChange" => "setL(this.value);  this.form.submit()"));
	$selLim->setSelected($limit);
	
	/*
	 * Element we need when we reload the page
	 */
	$form->addElement('hidden', 'p');
	$form->addElement('hidden', 'search');
	$form->addElement('hidden', 'num');
	$form->addElement('hidden', 'order');
	$form->addElement('hidden', 'type');
	$form->addElement('hidden', 'sort_types');
	$form->setDefaults(array("p" => $p, "search" => $search, "num"=>$num));

	/*
	 * Init QuickForm
	 */
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);

	isset($_GET["host_name"]) ? $host_name = $_GET["host_name"] : $host_name = NULL;
	isset($_GET["status"]) ? $status = $_GET["status"] : $status = NULL;
	
	$tpl->assign("host_name", $host_name);
	$tpl->assign("status", $status);
	$tpl->assign("limite", $limite);
	$tpl->assign("begin", $num);
	$tpl->assign("end", $limit);
	$tpl->assign("pagin_page", _("Page"));
	$tpl->assign("order", $_GET["order"]);
	$tpl->assign("tab_order", $tab_order);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("pagination.ihtml");
?>