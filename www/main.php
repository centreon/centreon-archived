<?php
/*
 * Copyright 2005-2011 MERETHIS
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

 	ini_set("display_errors", "Off");
    ini_set("error_reporting", "E_ALL & ~E_DEPRECATED");

	/*
	 * Define Local Functions
	 *   - remove SQL Injection : Thanks to Julien CAYSSOL
	 */

	function getParameters($str){
		$var = NULL;
		if (isset($_GET[$str]))
			$var = $_GET[$str];
		if (isset($_POST[$str]))
			$var = $_POST[$str];
		if ($var == "")
			$var = NULL;
		return htmlentities($var, ENT_QUOTES, "UTF-8");
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
	$type = getParameters("type");
	$search = getParameters("search");
	$limit = getParameters("limit");
	$num = getParameters("num");

	/*
	 * Include all func
	 */

	include_once ("./basic-functions.php");
	include_once ("./include/common/common-Func.php");
	include_once ("./header.php");

	require_once $centreon_path . "www/autoloader.php";

	/*
	 * LCA Init Common Var
	 */
	global $is_admin;
	$is_admin = $centreon->user->admin;

	$DBRESULT = $pearDB->query("SELECT topology_parent,topology_name,topology_id,topology_url,topology_page FROM topology WHERE topology_page = '".$p."'");
	$redirect = $DBRESULT->fetchRow();

	/*
	 * Init URL
	 */
	$url = "";
	if (!isset($_GET["doc"])){
		$acl_page = $centreon->user->access->page($p);
		if ($acl_page == 1 || $acl_page == 2) {
			if ($redirect["topology_page"] < 100){
				$ret = get_child($redirect["topology_page"], $centreon->user->access->topologyStr);
				if (!$ret['topology_page']){
					if (file_exists($redirect["topology_url"])){
						$url = $redirect["topology_url"];
						reset_search_page($url);
					} else {
						$url = "./alt_error.php";
					}
				} else {
					$ret2 = get_child($ret['topology_page'], $centreon->user->access->topologyStr);
					if ($ret2["topology_url_opt"])	{
						if (!$o) {
							$tab = preg_split("/\=/", $ret2["topology_url_opt"]);
							$o = $tab[1];
						}
						$p = $ret2["topology_page"];
					}
					if (file_exists($ret2["topology_url"])){
						$url = $ret2["topology_url"];
						reset_search_page($url);
						if ($ret2["topology_url_opt"]){
							$tab = preg_split("/\=/", $ret2["topology_url_opt"]);
							$o = $tab[1];
						}
					} else {
						$url = "./alt_error.php";
					}
				}
			} else if ($redirect["topology_page"] >= 100 && $redirect["topology_page"] < 1000) {
				$ret = get_child($redirect["topology_page"], $centreon->user->access->topologyStr);
				if (!$ret['topology_page']) {
					if (file_exists($redirect["topology_url"])){
						$url = $redirect["topology_url"];
						reset_search_page($url);
					} else {
						$url = "./alt_error.php";
					}
				} else {
					if ($ret["topology_url_opt"]){
						if (!$o) {
							$tab = preg_split("/\=/", $ret["topology_url_opt"]);
							$o = $tab[1];
						}
						$p = $ret["topology_page"];
					}
					if (file_exists($ret["topology_url"])){
						$url = $ret["topology_url"];
						reset_search_page($url);
					} else {
						$url = "./alt_error.php";
					}
				}
			} else if ($redirect["topology_page"] >= 1000) {
				$ret = get_child($redirect["topology_page"], $centreon->user->access->topologyStr);
				if (!$ret['topology_page']){
					if (file_exists($redirect["topology_url"])){
						$url = $redirect["topology_url"];
						reset_search_page($url);
					} else {
						$url = "./alt_error.php";
					}
				} else {
					if (file_exists($redirect["topology_url"]) && $ret['topology_page']){
						$url = $redirect["topology_url"];
						reset_search_page($url);
					} else {
						$url = "./alt_error.php";
					}
				}
			}
			if (isset($o) && $acl_page == 2) {
				if ($o == 'c') {
					$o = 'w';
				} elseif ($o == 'a') {
					$url = "./alt_error.php";
				}
			}
		} else {
			$url = "./alt_error.php";
		}
	} else {
		$url = "./include/doc/index.php";
	}

	/*
	 *  Header HTML
	 */
	include_once "./htmlHeader.php";

	/*
	 * Display Menu
	 */
	if (!$min) {
		include_once "menu/Menu.php";
	}

	if (!$centreon->user->showDiv("header")) { ?> <script type="text/javascript">new Effect.toggle('header', 'appear', { duration : 0, afterFinish: function() { setQuickSearchPosition(); } });</script> <?php }
	if (!$centreon->user->showDiv("menu_3")) { ?> <script type="text/javascript">new Effect.toggle('menu_3', 'appear', { duration : 0 });</script> <?php }
	if (!$centreon->user->showDiv("menu_2")) { ?> <script type="text/javascript">new Effect.toggle('menu_2', 'appear', { duration : 0 });</script> <?php }

	/*
	 * Display PathWay
	 */
	if ($min != 1) {
		include_once "pathWay.php";
	}

	/*
	 * Go on our page
	 */
	if ($min != 1) {
    	require_once ("./class/centreonMsg.class.php");
    	$msg = new CentreonMsg();
    	if (!$centreon->user->admin && !count($centreon->user->access->getAccessGroups())) {
    		$msg->setImage("./img/icones/16x16/warning.gif");
    		$msg->setTextStyle("bold");
    		$msg->setText(_("You are not in an access group"));
    		$msg->setTimeOut("3");
    	}
	}

	if (isset($url) && $url) {
    	include_once $url;
	}

	if (!isset($centreon->historyPage)) {
		$centreon->createHistory();
	}

	/*
	 * Keep in memory all informations about pagination, keyword for search...
	 */
	if (isset($url) && $url) {
		if (isset($_GET["num"]))
			$centreon->historyPage[$url] = $_GET["num"];
		if (isset($_POST["num"]))
			$centreon->historyPage[$url] = $_POST["num"];
		if (isset($_GET["search"]))
			$centreon->historySearch[$url] = $_GET["search"];
		if (isset($_POST["search"]))
			$centreon->historySearch[$url] = $_POST["search"];
		if (isset($_GET["search_service"]))
			$centreon->historySearchService[$url] = $_GET["search_service"];
		if (isset($_POST["search_service"]))
			$centreon->historySearchService[$url] = $_POST["search_service"];
		if (isset($_GET["limit"]))
			$centreon->historyLimit[$url] = $_GET["limit"];
		if (isset($_POST["limit"]))
			$centreon->historyLimit[$url] = $_POST["limit"];
	}

	print "\t\t\t</td>\t\t</tr>\t</table>\n</div>";
	print "<!-- Footer -->";

	/*
	 * Display Footer
	 */
	if (!$min) {
		include_once "footer.php";
	}

?>