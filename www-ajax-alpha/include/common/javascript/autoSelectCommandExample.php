<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

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

	# return argument for specific command in txt format
	# use by ajax

	require_once("../../../oreon.conf.php");
	require_once("../../../DBconnect.php");
	
	
	header('Content-type: text/html; charset=iso-8859-1'); 
	 
	if(count($_POST) > 0) 
	{ 
	   foreach($_POST as $v) 
	      $command_id = utf8_decode($v); 
	} 


	if(!is_null($command_id))
	{
		header('Content-Type: text/xml;charset=utf-8');

		$res =& $pearDB->query("SELECT command_example FROM command WHERE" .
			" command_id = '".$command_id."' ");
		if (PEAR::isError($pearDB)) {
			print "Mysql Error : ".$pearDB->getMessage();
		}
		while($res->fetchInto($arg))
			echo $arg["command_example"];

		$pearDB->disconnect();
	}	

?>
