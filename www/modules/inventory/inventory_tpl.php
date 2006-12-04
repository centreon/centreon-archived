<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
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
?>

<div>
	<div class='text12b' style='padding-top:15px;'><img src="img/picto1.gif"> Host</div>
	<div style='padding-left:20px;float:left;width:140px;'>Name : </div><div><? print $sysName; ?>&nbsp;</div>
	<div style='padding-left:20px;float:left;width:140px;'>Description : </div><div><? print $sysDescr; ?>&nbsp;</div>
	<div style='padding-left:20px;float:left;width:140px;'>Contact : </div><div><? print $sysContact; ?>&nbsp;</div>
	<div style='padding-left:20px;float:left;width:140px;'>Location : </div><div><? print $sysLocation; ?>&nbsp;</div>
	<div style='padding-left:20px;float:left;width:140px;'>Uptime : </div><div><? print $sysUpTime; ?>&nbsp;</div>
<?	if (!strcmp("NetRessource", $_GET["Type"])) { ?>
	<div style='padding-left:20px;float:left;width:140px;'>Health : </div><div><? print $Health; ?>&nbsp;</div>
	<div style='padding-left:20px;float:left;width:140px;'>CPU Use : </div><div><? print $CPUStat; ?>%&nbsp;</div>
	<div style='padding-left:20px;float:left;width:140px;'>Telnet Enable : </div><div><? print $enable[$TelnetEnable]; ?>&nbsp;</div>
	<div style='padding-left:20px;float:left;width:140px;'>SSH Enable : </div><div><? print $enable[$SSHEnable]." (Port : $SSHPort)"; ?>&nbsp;</div>
	<div style='padding-left:20px;float:left;width:140px;'>Serial Number : </div><div><? print $SerialNumber . " ($GlobalDeviceUniqueID)"; ?>&nbsp;</div>
	<div style='padding-left:20px;float:left;width:140px;'>Manufacturer : </div><div><? print $Manufacturer; ?>&nbsp;</div>
	<div style='padding-left:20px;float:left;width:140px;'>Serie Type : </div><div><? print $SerieType; ?>&nbsp;</div>
	<div style='padding-left:20px;float:left;width:140px;'>Rom Version : </div><div><? print $RomVersion; ?>&nbsp;</div>
	<div style='padding-left:20px;float:left;width:140px;'>Switch Version : </div><div><? print $SwitchVersion; ?>&nbsp;</div>
<? 	
	} ?>
	<div class='text12b' style='padding-top:10px;'><img style="cursor: hand;" id="handle1" name="handle1" src="img/picto1.gif" alt="" onclick='hideobject("InvNetWork", "showInvNetWork", "handle1", "./img/picto1.gif");'>&nbsp;&nbsp;<a onclick="hideobject('InvNetWork', 'showInvNetWork', 'handle1', './img/picto1.gif');">Network</a></div>
	<div style="display: none;padding:0px;padding-top:15px;" id="InvNetWork">
	<?
	
	$tab_unit = array("0"=>"bits", "1"=>"Kbits","2"=>"Mbits","3"=>"Gbits");
	
	if ($ifTab)
		foreach ($ifTab as $it){?>
		<div style='padding-top:15px;'>
			<div style='padding-left:10px;width:100px;'><span class='text11b'>Name :</span> <? print $it["ifDescr"]; ?>&nbsp; (<? $r = preg_match("/([A-Za-z]+)\([0-9]+\)/", $it["ifType"], $matches); print $matches["1"]; ?>)</div>
			<?
				for ($cpt = 0,$value = $it["ifSpeed"]; $value >= 1000 ; $value /= 1000)
					$cpt++;
			?>
			<div style='padding-left:10px;width:100px;'><span class='text11b'>Speed :</span> <? print $value . "&nbsp;".$tab_unit[$cpt]; ?>&nbsp;</div>
			<div style='padding-left:10px;width:100px;'><span class='text11b'>IP Address :</span> <? if ($ipInterface[$it["ifIndex"]]["ipIP"]){ print $ipInterface[$it["ifIndex"]]["ipIP"]."&nbsp;/&nbsp;".$ipInterface[$it["ifIndex"]]["ipNetMask"];} else {print "Not Defined";} ?></div>
			<div style='padding-left:10px;width:100px;'><span class='text11b'>PhysAddress :</span> <? print $it["ifPhysAddress"]; ?>&nbsp;</div>
			<div style='padding-left:10px;width:100px;'><span class='text11b'>Status :</span> <? print $it["ifAdminStatus"]; ?> / <? print $it["ifOperStatus"]; ?>&nbsp;</div>
		</div>
	<? 	}	?>
	</div>
<? 	if ($hrStorageIndex) { ?>
	<div class='text12b'><img style="cursor: hand;" id="handle1" name="handle1" src="img/picto1.gif" alt="" onclick='hideobject("InvStorage", "showInvStorage", "handle1", "./img/picto1.gif");'>&nbsp;&nbsp;<a onclick="hideobject('InvStorage', 'showInvStorage', 'handle1', './img/picto1.gif');">Storage Device</a></div>
	<div style="display: none;padding:0px;" id="InvStorage">
	<?
		foreach ($hrStorageIndex as $SI){?>
		<div style='padding-top:15px;'>
			<div style='padding-left:10px;float:left;width:100px;' class='text10b'>Name</div><div><? print $SI["hsStorageDescr"]; ?>&nbsp;</div>
			<div style='padding-left:10px;float:left;width:100px;' class='text10b'>Type</div><div><? print $SI["hsStorageType"]; if ($SI["hsFSBootable"] == "true(1)") print "(Bootable)"; ?>&nbsp;</div>
			<div style='padding-left:10px;float:left;width:100px;' class='text10b'>Size</div><div><? print $SI["hsStorageSize"]; ?>&nbsp;</div>
			<div style='padding-left:10px;float:left;width:100px;' class='text10b'>Used</div><div><? print $SI["hsStorageUsed"]; ?>&nbsp;</div>
		<? 	if ($SI["hrFSAccess"]) { ?>
			<div style='padding-left:10px;float:left;width:100px;' class='text10b'>Acces Permissions</div><div><? print $SI["hrFSAccess"]; ?>&nbsp;</div>
		<?	}	?>
		</div>
	<? 	}	?>
	</div>
<?	}
 	if ($hrSWRun) { ?>
	<div class='text12b'><img style="cursor: hand;" id="handle1" name="handle1" src="img/picto1.gif" alt="" onclick='hideobject("InvRunProc", "showInvRunProc", "handle1", "./img/picto1.gif");'>&nbsp;&nbsp;<a onclick="hideobject('InvRunProc', 'showInvRunProc', 'handle1', './img/picto1.gif');">Running Process</a></div>
	<div style="display: none;padding:0px;padding-top:15px;" id="InvRunProc">
	<?
		foreach ($hrSWRun as $SWR){?>
		<div style='padding-top:15px;'>
			<div style='padding-left:10px;float:left;width:100px;' class='text10b'>Name</div><div><? print $SWR["hrSWRunName"]; ?>&nbsp;</div>
			<div style='padding-left:10px;float:left;width:100px;' class='text10b'>Path</div><div><? print $SWR["hrSWRunPath"]; if ($SI["hrSWRunParameters"]) print " " . $SWR["hrSWRunParameters"]; ?>&nbsp;</div>
			<div style='padding-left:10px;float:left;width:100px;' class='text10b'>Status</div><div><? print $SWR["hrSWRunStatus"]; ?>&nbsp;</div>
			<div style='padding-left:10px;float:left;width:100px;' class='text10b'>Memory Used</div><div><? print $SWR["hrSWRunPerfMem"]; ?>&nbsp;</div>
		<? 	if ($SIWR["hrSWRunPerfCPU"]) { ?>
			<div style='padding-left:10px;float:left;width:100px;' class='text10b'>Acces Permissions</div><div><? print $SWR["hrSWRunPerfCPU"]; ?>&nbsp;</div>
		<?	}	?>
		</div>
	<? 	}	?>
	</div>
<?	}	?>
	
</div>
<script language="Javascript">
if (!Get_Cookie('showInvNetWork')) {
	Set_Cookie('showInvNetWork','true',30,'/','','');
}
var show = Get_Cookie('showInvNetWork');

if (show == 'true') {
	this.document.getElementById('InvNetWork').style.display='inline';
	document['handle1'].src = './img/picto1.gif';
} else {
	this.document.getElementById('InvNetWork').style.display='none';
	document['handle1'].src = './img/picto1.gif';	
}
if (!Get_Cookie('showInvStorage')) {
	Set_Cookie('showInvStorage','true',30,'/','','');
}
var show = Get_Cookie('showInvStorage');

if (show == 'true') {
	this.document.getElementById('InvStorage').style.display='inline';
	document['handle1'].src = './img/picto1.gif';
} else {
	this.document.getElementById('InvStorage').style.display='none';
	document['handle1'].src = './img/picto1.gif';	
}
if (!Get_Cookie('showInvRunProc')) {
	Set_Cookie('showInvRunProc','true',30,'/','','');
}
var show = Get_Cookie('showInvRunProc');

if (show == 'true') {
	this.document.getElementById('InvRunProc').style.display='inline';
	document['handle1'].src = './img/picto1.gif';
} else {
	this.document.getElementById('InvRunProc').style.display='none';
	document['handle1'].src = './img/picto1.gif';	
}
</script>