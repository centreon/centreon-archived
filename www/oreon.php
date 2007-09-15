<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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

	/* POST have priority on GET due to option in listing configuration form */
	if (isset($_POST["o"]))
		$o = $_POST["o"];
	else if (isset($_GET["o"]))
		$o = $_GET["o"];
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
		 
	# Include all func
	require_once ("./func.php");
	require_once ("./include/common/common-Func.php");
	
	require_once ("./header.php");
	
	# LCA Init Common Var
	global $isRestreint;
	$isRestreint = HadUserLca($pearDB);

	$DBRESULT =& $pearDB->query("SELECT topology_parent,topology_name,topology_id,topology_url,topology_page FROM topology WHERE topology_page = '".$p."'");
	if (PEAR::isError($DBRESULT)) print "DB Error : ".$DBRESULT->getDebugInfo()."<br>";
	$redirect =& $DBRESULT->fetchRow();

	$nb_page = NULL;
	if ($isRestreint){
		if (!count(!$oreon->user->lcaTopo) || !isset($oreon->user->lcaTopo[$p])){
			$nb_page = 0;
			require_once("./alt_error.php");
		} else
			$nb_page = 1;	
	} else
		$nb_page = 1;

	# Init URL 
	$url = "";
	if (!isset($_GET["doc"])){
		if ((isset($nb_page) && $nb_page) || !$isRestreint){	
			if ($redirect["topology_page"] < 100){
				$ret = get_child($redirect["topology_page"], $oreon->user->lcaTStr);
				if (!$ret['topology_page']){
					if (file_exists($redirect["topology_url"])){
						$url = $redirect["topology_url"];
						reset_search_page($url);
					} else 
						$url = "./alt_error.php";		
				} else {
					$ret2 = get_child($ret['topology_page'], $oreon->user->lcaTStr);	
					if ($ret2["topology_url_opt"])	{
						if (!$o) {
							$tab = split("\=", $ret2["topology_url_opt"]);
							$o = $tab[1];
						}
						$p = $ret2["topology_page"];
					}
					if (file_exists($ret2["topology_url"])){
						$url = $ret2["topology_url"];
						reset_search_page($url);
						if ($ret2["topology_url_opt"]){
							$tab = split("\=", $ret2["topology_url_opt"]);
							$o = $tab[1];
						}
					} else
						$url = "./alt_error.php";
				} 
			} else if ($redirect["topology_page"] >= 100 && $redirect["topology_page"] < 1000) {
				$ret = get_child($redirect["topology_page"], $oreon->user->lcaTStr);	
				if (!$ret['topology_page']){
					if (file_exists($redirect["topology_url"])){
						$url = $redirect["topology_url"];
						reset_search_page($url);
					} else 
						$url = "./alt_error.php";		
				} else {
					if ($ret["topology_url_opt"]){
						if (!$o) {
							$tab = split("\=", $ret["topology_url_opt"]);
							$o = $tab[1];
						}
						$p = $ret["topology_page"];
					} 
					if (file_exists($ret["topology_url"])){
						$url = $ret["topology_url"];
						reset_search_page($url);
					} else 
						$url = "./alt_error.php";
				}
			} else if ($redirect["topology_page"] >= 1000) {
				$ret = get_child($redirect["topology_page"], $oreon->user->lcaTStr);
				if (!$ret['topology_page']){
					if (file_exists($redirect["topology_url"])){		
						$url = $redirect["topology_url"];
						reset_search_page($url);
					} else 
						$url = "./alt_error.php";		
				} else { 
					if (file_exists($redirect["topology_url"]) && $ret['topology_page']){	
						$url = $redirect["topology_url"];
						reset_search_page($url);
					} else 
						$url = "./alt_error.php";
				}
			}
		}
	} else
		$url = "./include/doc/index.php";

	# Header HTML
	require_once ("./htmlHeader.php");
			
	# Display Menu
	if (!$min)
		require_once ("menu/Menu.php");

	# Display PathWay	
	if($min != 1)
		include("pathWay.php");
	
	# Go on our page 
	if (isset($url) && $url)
    	require_once($url);
    else
        ;//echo "Problem with url generated";


	if (!isset($oreon->historyPage))
		$oreon->createHistory();	 
	
	if (isset($url) && $url){
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
		if (isset($_POST["limit"]))
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

	print "\t\t\t</td>\t\t</tr>\t</table>\n</div><!-- end contener -->";
	
	# Display Footer
	if (!$min)
		require_once("footer.php");
?>