<?php
/**
Centreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
Developped by : Julien Mathis - Romain Le Merlus

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

	/*
	 * The type of interface, distinguished according to
     * the physical/link protocol(s) immediately `below'
     * the network layer in the protocol stack.
	 */

	$ifType[1] = "other";
	$ifType[2] = "regular1822";
	$ifType[3] = "hdh1822";
	$ifType[4] = "ddn-x25";
	$ifType[5] = "rfc877-x25";
	$ifType[6] = "ethernet-csmacd";
	$ifType[7] = "iso88023-csmacd";
	$ifType[8] = "iso88024-tokenBus";
	$ifType[9] = "iso88025-tokenRing";
	$ifType[10] = "iso88026-man";
	$ifType[11] = "starLan";
	$ifType[12] = "proteon-10Mbit";
	$ifType[13] = "proteon-80Mbit";
	$ifType[14] = "hyperchannel";
	$ifType[15] = "fddi";
	$ifType[16] = "lapb";
	$ifType[17] = "sdlc";
	$ifType[18] = "ds1";
	$ifType[19] = "e1";
	$ifType[20] = "basicISDN";
	$ifType[21] = "primaryISDN";
	$ifType[22] = "propPointToPointSerial";
	$ifType[23] = "ppp";
	$ifType[24] = "softwareLoopback";
	$ifType[25] = "eon";
	$ifType[26] = "ethernet-3Mbit";
	$ifType[27] = "nsip";
	$ifType[28] = "slip";
	$ifType[29] = "ultra";
	$ifType[30] = "ds3";
	$ifType[31] ="sip";
	$ifType[32] = "frame-relay";

	$ifAdminStatus[1] = "Up";
	$ifAdminStatus[2] = "Down";
	$ifAdminStatus[3] = "Testing";

	$ifOperStatus["up(1)"] = "Up";
	$ifOperStatus["down(2)"] = "Down";
	$ifOperStatus["testing(3)"] = "Testing";

	$ifOperStatus[1] = "Up";
	$ifOperStatus[2] = "Down";
	$ifOperStatus[3] = "Testing";


?>