<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

This unit, called « Oreon Inventory » is developped by Merethis company for Lafarge Group,
under the direction of Jean Baptiste Sarrodie <jean-baptiste@sarrodie.org>

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the
quality,
safety, contents, performance, merchantability, non-infringement or
suitability for
any particular or intended purpose of the Software found on the OREON web
site.
In no event will OREON be liable for any direct, indirect, punitive,
special,
incidental or consequential damages however they may arise and even if OREON
has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

	if (!$oreon)
		exit();

	/*
	 * Ventilo
	 */

	$oid["hp"]["SensorIndex"] = ".1.3.6.1.4.1.11.2.14.11.1.2.6.id.1";
	$oid["hp"]["SensorDescr"] = ".1.3.6.1.4.1.11.2.14.11.1.2.6.id.7";
	$oid["hp"]["SensorStatus"] = ".1.3.6.1.4.1.11.2.14.11.1.2.6.id.4";

	/*
	 * Version
	 */

	$oid["hp"]["SwitchVersion"] = ".1.3.6.1.4.1.11.2.14.11.5.1.1.3.0";
	$oid["hp"]["RomVersion"] = ".1.3.6.1.4.1.11.2.14.11.5.1.1.4.0";
	$oid["cisco"]["SwitchVersion"] = "1.3.6.1.4.1.9.3.6.5.0";
	$oid["cisco"]["RomVersion"] = "1.3.6.1.4.1.9.3.6.4.0";
	$oid["3com"]["SwitchVersion"] = "1.3.6.1.4.1.43.10.27.1.1.1.10.1.0";
	$oid["3com"]["RomVersion"] = "1.3.6.1.4.1.43.10.27.1.1.1.12.1.0";
	$oid["ciscolinksys"]["SwitchVersion"] = "1.3.6.1.4.1.3955.1.2";
	$oid["ciscolinksys"]["RomVersion"] = "1.3.6.1.4.1.3955.1.3";

	/*
	 * Telnet enabled
	 */

	$oid["hp"]["TelnetEnabled"] = ".1.3.6.1.4.1.11.2.14.11.5.1.7.1.2.1.0";

	/*
	 * SSH Enabled
	 */

	$oid["hp"]["SSH"] = ".1.3.6.1.4.1.11.2.14.11.5.1.7.1.20.1.0";
	$oid["hp"]["SSHPort"] = ".1.3.6.1.4.1.11.2.14.11.5.1.7.1.20.4.0";

	/*
	 * CPU Stat
	 */

	$oid["hp"]["CPUStat"] = ".1.3.6.1.4.1.11.2.14.11.5.1.9.6.1.0";

	/*
	 * Spanning Tree protocole
	 */

	$oid["hp"]["SpanningTreeProtocol"] = ".1.3.6.1.4.1.11.2.14.11.5.1.9.5.1.0";

	/*
	 * Ports Switchs
	 */

	$oid["hp"]["VlanAssign"] = "";
	$oid["cisco"]["VlanAssign"] = "1.3.6.1.4.1.9.9.68.1.2.2.1.2.";

	/*
	 * Serial Number
	 */

	$oid["hp"]["manufacturer"] = ".1.3.6.1.4.1.11.2.36.1.1.2.4.0";
	$oid["hp"]["SerialNumber"] = ".1.3.6.1.4.1.11.2.36.1.1.2.9.0";
	$oid["hp"]["SerieType"] = ".1.3.6.1.4.1.11.2.36.1.1.5.1.1.9.1";

	$oid["cisco"]["manufacturer"] = ".1.3.6.1.4.1.3.6.14.0";
	$oid["cisco"]["SerialNumber"] = ".1.3.6.1.4.1.9.3.6.3.0";
	$oid["cisco"]["SerieType"] = "1.3.6.1.4.1.9.9.23.1.2.1.1.8.1.1";

	$oid["3com"]["manufacturer"] = "";
	$oid["3com"]["SerialNumber"] = "1.3.6.1.4.1.43.10.27.1.1.1.19.1";
	$oid["3com"]["SerieType"] = "1.3.6.1.4.1.43.10.27.1.1.1.5.1";

	$oid["ciscolinksys"]["manufacturer"] = "";
	$oid["ciscolinksys"]["SerialNumber"] = "";
	$oid["ciscolinksys"]["SerieType"] = "";


?>