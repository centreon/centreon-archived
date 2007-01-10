<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Cedrick Facon

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

	require_once ("../../oreon.conf.php");
	require_once ("../../DBconnect.php");
	require_once ("../../$classdir/Session.class.php");
	require_once ("../../$classdir/Oreon.class.php");
	
	Session::start();
	
	$session =& $pearDB->query("SELECT * FROM `session` WHERE session_id = '".session_id()."'");
	if (!$session->numRows())
		exit;

	if (isset($_GET["path"]) && $_GET["path"] && is_file($_GET["path"]))    {
		$fd=fopen($_GET["path"], "r");
		$buffer=NULL;
		while (!feof($fd))
		    $buffer.=fgets($fd, 4096);
		fclose ($fd);
		print $buffer;
	}
?>