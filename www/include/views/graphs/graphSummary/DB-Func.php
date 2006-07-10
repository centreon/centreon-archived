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
	if (!isset ($oreon))
		exit ();
		
	function getHostID($host_name){
		global $pearDB;
		if (!$host_name) 
			return NULL;
		$res =& $pearDB->query("SELECT * FROM host WHERE host_name = '".$host_name."' LIMIT 1");
		$res->fetchInto($host_id);
		return $host_id["host_id"];
	}
	
	function getServiceID($host_name, $service_description){
		global $pearDB;
		if (!$host_name) 
			return NULL;
		$res =& $pearDB->query("SELECT service.service_id FROM host,service,host_service_relation WHERE host.host_name = '".$host_name."' AND service.service_description = '".$service_description."' AND host_service_relation.host_host_id = host.host_id AND host_service_relation.service_service_id = service.service_id");
		$res->fetchInto($service_id);
		return $service_id["service_id"];
	}
?>