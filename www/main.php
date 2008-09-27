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
	
/*
 * SVN: $URL: http://svn.centreon.com/trunk/centreon/www/main.php $
 * SVN: $Id: main.php 5223 2008-05-21 12:44:13Z jmathis $
 */
	
	/* 
	 * Define Local Functions
	 */

	function getParameters($str){
		if (isset($_GET[$str]))
			$var = $_GET[$str];
		else if (isset($_POST[$str]))
			$var = $_POST[$str];
		else
			$var = NULL;
		return $var;
	}
	
 	/*
 	 * Purge Values 
 	 */
 	 
	if (function_exists('filter_var')){	
		foreach ($_GET as $key => $value){
			if (!is_array($value)){
				$value = filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
				$_GET[$key] = $value;
			}
		}
	}
	
	$p = getParameters("p");
	$o = getParameters("o");
	$min = getParameters("min");
	
	/*
	 * Include all func
	 */
	
	include_once ("./basic-functions.php");
	include_once ("./include/common/common-Func.php");
	include_once ("./header.php");

	/*
	 * LCA Init Common Var
	 */
	  
	global $is_admin;
	$is_admin = isUserAdmin(session_id());
	
	$DBRESULT =& $pearDB->query("SELECT topology_parent,topology_name,topology_id,topology_url,topology_page FROM topology WHERE topology_page = '".$p."'");
	if (PEAR::isError($DBRESULT)) 
		print "DB Error : ".$DBRESULT->getDebugInfo()."<br />";
	$redirect =& $DBRESULT->fetchRow();

	$nb_page = NULL;
	if (!$is_admin){
		if (!count(!$oreon->user->lcaTopo) || !isset($oreon->user->lcaTopo[$p])){
			$nb_page = 0;
			include_once "./alt_error.php";
		} else
			$nb_page = 1;
	} else
		$nb_page = 1;

	/*
	 * Init URL
	 */
	 
	$url = "";
	if (!isset($_GET["doc"])){
		if ((isset($nb_page) && $nb_page) || $is_admin){
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
					} else {
						$url = "./alt_error.php";
					}
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

	/*
	 *  Header HTML
	 */
	include_once "./htmlHeader.php";

	/*
	 * Display Menu
	 */
	if (!$min)
		include_once "menu/Menu.php";

	/*
	 * Display PathWay
	 */
	if ($min != 1)
		include_once "pathWay.php";

	/*
	 * Go on our page
	 */
	if (isset($url) && $url)
    	include_once $url;

	if (!isset($oreon->historyPage))
		$oreon->createHistory();
	
	/*
	 * Keep in memory all informations about pagination, keyword for search... 
	 */
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

	print "\t\t\t</td>\t\t</tr>\t</table>\n</div>";
	print "<!-- Footer -->";
	
	/*
	 * Display Footer
	 */
	if (!$min)
		include_once "footer.php";
?>