<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

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
	
	# Clean Var	
	if (isset($_GET["p"]))
		$p = $_GET["p"];
	else if (isset($_POST["p"]))
		$p = $_POST["p"];
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
	
	function get_child($id_page, $lcaTStr){
		global $pearDB;
		$rq = "SELECT topology_parent,topology_name,topology_id,topology_url,topology_page,topology_url_opt FROM topology WHERE  topology_id IN ($lcaTStr) AND topology_parent = '".$id_page."' ORDER BY topology_order";
		$res =& $pearDB->query($rq);
		$res->fetchInto($redirect);
		return $redirect;
	}

	# Menu
	if (!$min)
		require_once ("menu/Menu.php");

	$rq = "SELECT topology_parent,topology_name,topology_id,topology_url,topology_page FROM topology WHERE topology_page = '".$p."'";
	$res =& $pearDB->query($rq);
	$redirect =& $res->fetchRow();
	
	if($min != 1)
		include("pathWay.php");
	
	$isRestreint = HadUserLca($pearDB);
	
	if ($redirect["topology_page"] < 1000 && ($isRestreint || !$oreon->user->admin)) {
		if ($redirect["topology_page"] < 100){
			$ret = get_child($redirect["topology_page"], $oreon->user->lcaTStr);
			$ret = get_child($ret['topology_page'], $oreon->user->lcaTStr);
			if (file_exists($ret["topology_url"])){
				$o = $ret["topology_url_opt"];
				require_once($ret["topology_url"]);
				print "ok";
			} else {
				if (file_exists($redirect["topology_url"]))
					require_once($redirect["topology_url"]);
				print "ok2";
			}
		} else {
			print "ok3";
			$ret = get_child($redirect["topology_page"], $oreon->user->lcaTStr);			
			if (file_exists($ret["topology_url"])){
				$o = $ret["topology_url_opt"];
				require_once($ret["topology_url"]);
			} else
				require_once("./alt_error.php");
		}
		print $o;
	} else {		
		file_exists($redirect["topology_url"]) ? require_once($redirect["topology_url"]) : require_once("./alt_error.php");
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