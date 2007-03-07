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

For information : contact@oreon-project.org
*/
	
	if (isset($_GET["p"]))
		$p = $_GET["p"];
	else if (isset($_POST["p"]))
		$p = $_POST["p"];
	else
		$p = NULL;
	
	if (isset($_GET["o"]))
		$o = $_GET["o"];
	else if (isset($_POST["o"]))
		$o = $_POST["o"];
	else
		$o = NULL;
		
	if (isset($_GET["min"]))
		$min = $_GET["min"];
	else if (isset($_POST["min"]))
		$min = $_POST["min"];
	else
		$min = NULL;

	if (isset($_GET["AutoLogin"]) && $_GET["AutoLogin"])
		print $_GET["AutoLogin"];
		 
	# header html
	require_once ("./header.php");

	# function 
	function get_path($abs_path){
		$len = strlen($abs_path);
		for ($i = 0, $flag = 0; $i < $len; $i++){
			if ($flag == 3)
				break;
			if ($abs_path{$i} == "/")
				$flag++;
		}
		return substr($abs_path, 0, $i);
	}
	
	function get_child($id_page, $lcaTStr){
		global $pearDB;
		$rq = "	SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt 
				FROM topology 
				WHERE  topology_id IN ($lcaTStr) 
				AND topology_parent = '".$id_page."' AND topology_page IS NOT NULL AND topology_show = '1' 
				ORDER BY topology_order, topology_group "; 
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$DBRESULT->fetchInto($redirect);
		return $redirect;
	}

	require_once ("./include/common/common-Func.php");

	# LCA Init Common Var
	global $isRestreint;
	$isRestreint = HadUserLca($pearDB);
	
	# Menu
	if (!$min)
		require_once ("menu/Menu.php");

	$rq = "SELECT topology_parent,topology_name,topology_id,topology_url,topology_page FROM topology WHERE topology_page = '".$p."'";
	$DBRESULT =& $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$redirect =& $DBRESULT->fetchRow();
	
	if($min != 1)
		include("pathWay.php");

	$nb_page = NULL;
	if ($isRestreint){
		$rq = "SELECT topology_id FROM topology WHERE topology_id IN (".$oreon->user->lcaTStr.") AND topology_page = '".$p."'";
		$DBRESULT =& $pearDB->query($rq);
		if (PEAR::isError($DBRESULT))
			print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
		$nb_page =& $DBRESULT->numRows();
		
		if (!$nb_page)
			require_once("./alt_error.php");
	} else {
		$nb_page = 1;	
	}
	
	function reset_search_page($url){
		# Clean Vars
		global $oreon;
		if (isset($_GET["search"]) && isset($oreon->historySearch[$url]) && $_GET["search"] != $oreon->historySearch[$url]){		
			$_POST["num"] = 0;
			$_GET["num"] = 0;
		}	
	}
	
	# init URL 
	$url = "";
	if ((isset($nb_page) && $nb_page) || !$isRestreint){	
		if ($redirect["topology_page"] < 100){
			$ret = get_child($redirect["topology_page"], $oreon->user->lcaTStr);
			if (!$ret['topology_page']){
				if (file_exists($redirect["topology_url"])){
					$url = $redirect["topology_url"];
					reset_search_page($url);
					require_once($redirect["topology_url"]);
				} else 
					require_once("./alt_error.php");		
			} else {
				$ret2 = get_child($ret['topology_page'], $oreon->user->lcaTStr);	
				if ($ret2["topology_url_opt"]){
					$tab = split("\=", $ret2["topology_url_opt"]);
					if (!isset($_GET["o"]))
						$o = $tab[1];
					$p = $ret2["topology_page"];
				}
				if (file_exists($ret2["topology_url"])){
					$url = $ret2["topology_url"];
					reset_search_page($url);
					require_once($ret2["topology_url"]);
				} else
					require_once("./alt_error.php");
			} 
		} else if ($redirect["topology_page"] >= 100 && $redirect["topology_page"] < 1000) {
			$ret = get_child($redirect["topology_page"], $oreon->user->lcaTStr);	
			if (!$ret['topology_page']){
				if (file_exists($redirect["topology_url"])){
					$url = $redirect["topology_url"];
					reset_search_page($url);
					require_once($redirect["topology_url"]);
				} else 
					require_once("./alt_error.php");		
			} else {
				if ($ret["topology_url_opt"]){
					$tab = split("\=", $ret["topology_url_opt"]);
					if (!isset($_GET["o"]))
						$o = $tab[1];
					$p = $ret["topology_page"];
				} 
				if (file_exists($ret["topology_url"])){
					$url = $ret["topology_url"];
					reset_search_page($url);
					require_once($ret["topology_url"]);
				} else 
					require_once("./alt_error.php");
			}
		} else if ($redirect["topology_page"] >= 1000) {
			$ret = get_child($redirect["topology_page"], $oreon->user->lcaTStr);
				if (!$ret['topology_page']){
				if (file_exists($redirect["topology_url"])){		
					$url = $redirect["topology_url"];
					reset_search_page($url);
					require_once($redirect["topology_url"]);
				} else 
					require_once("./alt_error.php");		
			} else { 
				if (file_exists($redirect["topology_url"]) && $ret['topology_page']){	
					$url = $redirect["topology_url"];
					reset_search_page($url);
					require_once($redirect["topology_url"]);
				} else 
					require_once("./alt_error.php");		
				if (isset($_GET["o"]))
					$o = $_GET["o"];
			}
		} else {
			print "Unknown operation...";
		}
	}
	
	if (!isset($oreon->historyPage)){
		$oreon->createHistory();	
	} 
	
	if ($url){
		if (isset($_GET["num"]))
			$oreon->historyPage[$url] = $_GET["num"];
		if (isset($_POST["num"]))
			$oreon->historyPage[$url] = $_POST["num"];		
		if (isset($_GET["search"]))
			$oreon->historySearch[$url] = $_GET["search"];
		if (isset($_POST["search"]))
			$oreon->historySearch[$url] = $_POST["search"];
		if (isset($_GET["limit"]))
			$oreon->historyLimit[$url] = $_GET["limit"];
		if (isset($_POST["search"]))
			$oreon->historyLimit[$url] = $_POST["limit"];
	}

	# Display Legend
	$lg_path = get_path($path);
	if (file_exists($lg_path."legend.ihtml")){
		$tpl = new Smarty();
		$tpl = initSmartyTpl("./", $tpl);
		$tpl->assign('lang', $lang);
		$tpl->display($lg_path."legend.ihtml");
	}
?>
			</td>
		</tr>
	</table>
</div><!-- end contener -->
<?
	if (!$min)
		require_once("footer.php");
?>