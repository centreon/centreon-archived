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
	# xdebug_start_trace();
	
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

	# Menu
	if (!$min)
		require_once ("menu/Menu.php");

	$rq = "SELECT topology_parent,topology_name,topology_id, topology_url FROM topology WHERE topology_page = '".$p."'";
	$res =& $pearDB->query($rq);
	$redirect =& $res->fetchRow();
	
	if($min != 1)
		include("pathWay.php");
	
	if (file_exists($redirect["topology_url"]) && array_key_exists($redirect["topology_id"], $oreon->user->lcaTopo))
		require_once($redirect["topology_url"]);
	else
		require_once("./alt_error.php");
?>

			</td>
		</tr>
	</table>
</div><!-- end contener -->
<?
	if (!$min)
		require_once("footer.php");
?>