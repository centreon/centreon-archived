<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
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

	global $oreon;

	if (!isset($oreon))
		exit();

	global $num, $limit, $search, $url, $pearDB;
	global $search_type_service, $search_type_host, $host_name;
	
	isset ($_GET["type"]) ? $type = $_GET["type"] : $stype = NULL;
	isset ($_GET["o"]) ? $o = $_GET["o"] : $o = NULL;

	global $rows, $p, $lang, $gopt, $pagination;
	
	$tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc"); 	

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

	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./include/common/");


	$page_max = ceil($rows / $limit);
	if ($num > $page_max)
		$num = $page_max - 1;
		
	$pageArr = array();
	$istart = 0;
	for($i = 5, $istart = $num; $istart && $i > 0; $i--)
		$istart--;
	for($i2 = 0, $iend = $num; ( $iend <  ($rows / $limit -1)) && ( $i2 < (5 + $i)); $i2++)
		$iend++;
	for ($i = $istart; $i <= $iend; $i++){
		$pageArr[$i] = array("url_page"=>"./oreon.php?p=".$p."&num=$i&limit=".$limit."&search=".$search."&type=".$type."&o=" . $o . $url_var, "label_page"=>"<b>".($i +1)."</b>","num"=> $i);
	}
	if ($i > 1)							
		$tpl->assign("pageArr", $pageArr);

	$tpl->assign("num", $num);
	$tpl->assign("previous", $lang["previous"]);
	$tpl->assign("next", $lang["next"]);

	if (($prev = $num - 1) >= 0)
		$tpl->assign('pagePrev', ("./oreon.php?p=".$p."&num=$prev&limit=".$limit."&search=".$search."&type=".$type."&o=" . $o .$url_var));
	
	if (($next = $num + 1) < ($rows/$limit))
		$tpl->assign('pageNext', ("./oreon.php?p=".$p."&num=$next&limit=".$limit."&search=".$search."&type=".$type."&o=" . $o .$url_var));
	
	if (($rows / $limit) > 0)
		$tpl->assign('pageNumber', ($num +1)."/".ceil($rows / $limit));
	else
		$tpl->assign('pageNumber', ($num)."/".ceil($rows / $limit));

	#Select field to change the number of row on the page

	for ($i = 10; $i <= 100; $i = $i +10)
		$select[$i]=$i;
	$select[$gopt[$pagination]]=$gopt[$pagination];
	$select[$rows]=$rows;
	ksort($select);
		
	?>
	<SCRIPT LANGUAGE="JavaScript">
	function setL(_this){
		var _l = document.getElementsByName('l');
		document.forms['form'].elements['limit'].value = _this;
		_l[0].value = _this;
		_l[1].value = _this;
	}
	</SCRIPT>
	<?
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p."&search_type_service=" . $search_type_service."&search_type_host=" . $search_type_host);
	$selLim =& $form->addElement('select', 'l', $lang['nbr_per_page'], $select, array("onChange" => "setL(this.value);  this.form.submit()"));
	$selLim->setSelected($limit);
	
	#Element we need when we reload the page
	$form->addElement('hidden', 'p');
	$form->addElement('hidden', 'search');
	$form->addElement('hidden', 'num');
	$form->addElement('hidden', 'order');
	$form->addElement('hidden', 'type');
	$form->addElement('hidden', 'sort_types');
	$form->addElement('hidden', 'search_type_service');
	$form->addElement('hidden', 'search_type_host');
	$tab = array ("p" => $p, "search" => $search, "num"=>$num);
	$form->setDefaults($tab);

	# Init QuickForm
	$renderer =& new HTML_QuickForm_Renderer_ArraySmarty($tpl);
	$form->accept($renderer);
	
	isset($_GET["host_name"]) ? $host_name = $_GET["host_name"] : $host_name = NULL;
	$tpl->assign("host_name", $host_name);
	
	isset($_GET["status"]) ? $status = $_GET["status"] : $status = NULL;
	
	$tpl->assign("status", $status);
	$tpl->assign("limite", $limite);
	$tpl->assign("begin", $num);
	$tpl->assign("end", $limit);
	$tpl->assign("lang", $lang);
	$tpl->assign("order", $_GET["order"]);
	$tpl->assign("tab_order", $tab_order);
	$tpl->assign('form', $renderer->toArray());
	$tpl->display("pagination.ihtml");
?>