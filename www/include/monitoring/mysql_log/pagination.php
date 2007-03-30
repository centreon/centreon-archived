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

	$start = time() - 60*60*24;
	
	$tab_order = array("sort_asc" => "sort_desc", "sort_desc" => "sort_asc"); 	
	if (isset($_GET["start"]) && $_GET["start"])
		if (strpos($_GET["start"], "/"))
			$url_var  .= "&start=" . $_GET["start"];
		else
			$url_var  .= "&start=" . date("m/d/Y", $_GET["start"]);
	else
		$url_var  .= "&start=".date("m/d/Y", $start);
	
	if (isset($_GET["start_time"]) && $_GET["start_time"])
		$url_var  .= "&start_time=" . $_GET["start_time"];
	else
		$url_var  .= "&start_time=".date($lang["time_formatWOs"], $start);
		
	if (isset($_GET["end"]) && $_GET["end"])
		if (strpos($_GET["end"], "/"))
			$url_var  .= "&end=" . $_GET["end"];
		else
			$url_var  .= "&end=" . date("m/d/Y", $_GET["end"]);
	else
		$url_var  .= "&end=".date("m/d/Y");
		
	if (isset($_GET["end_time"]) && $_GET["end_time"])
		$url_var  .= "&end_time=" . $_GET["end_time"];
	else
		$url_var  .= "&end_time=".date($lang["time_formatWOs"], time());
	if (isset($_GET["search1"]) && $_GET["search1"])
		$url_var  .= "&search1=" . $_GET["search1"];
	else
		$url_var  .= "&search1=";
	if (isset($_GET["search2"]) && $_GET["search2"])
		$url_var  .= "&search2=" . $_GET["search2"];
	else
		$url_var  .= "&search2=";
	if (isset($_GET["sort_type1"]) && $_GET["sort_type1"])
		$url_var  .= "&sort_type1=" . $_GET["sort_type1"];
	else
		$url_var  .= "&sort_type1=";
	if (isset($_GET["sort_type2"]) && $_GET["sort_type2"])
		$url_var  .= "&sort_type2=" . $_GET["sort_type2"];
	else
		$url_var  .= "&sort_type2=";	
	
	# Smarty template Init
	$tpl = new Smarty();
	$tpl = initSmartyTpl($path, $tpl, "./include/common/");

	$pageArr = array();
	$istart = 0;
	for($i = 5, $istart = $num; $istart && $i > 0; $i--)
		$istart--;
	for($i2 = 0, $iend = $num; ( $iend <  ($rows / $limit -1)) && ( $i2 < (5 + $i)); $i2++)
		$iend++;
	for ($i = $istart; $i <= $iend; $i++){
		$pageArr[$i] = array("url_page"=>"./oreon.php?p=".$p."&num=$i&limit=".$limit."&o=" . $o . $url_var, "label_page"=>"<b>".($i +1)."</b>","num"=> $i);
	}
	if ($i > 1)							
		$tpl->assign("pageArr", $pageArr);

	$tpl->assign("num", $num);
	$tpl->assign("previous", $lang["previous"]);
	$tpl->assign("next", $lang["next"]);

	if (($prev = $num - 1) >= 0)
		$tpl->assign('pagePrev', ("./oreon.php?p=".$p."&num=$prev&limit=".$limit."&o=" . $o .$url_var));
	
	if (($next = $num + 1) < ($rows/$limit))
		$tpl->assign('pageNext', ("./oreon.php?p=".$p."&num=$next&limit=".$limit."&o=" . $o .$url_var));
	
	if (($rows / $limit) > 0)
		$tpl->assign('pageNumber', ($num +1)."/".ceil($rows / $limit));
	else
		$tpl->assign('pageNumber', ($num)."/".ceil($rows / $limit));

	#Select field to change the number of row on the page
	for ($i = 10; $i <= 100; $i = $i +10)
		$select[$i]=$i;
	if (isset($gopt[$pagination]) && $gopt[$pagination])
		$select[$gopt[$pagination]]=$gopt[$pagination];
	if (isset($rows) && $rows)
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
	$form = new HTML_QuickForm('select_form', 'GET', "?p=".$p);
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