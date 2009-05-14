<?php 

// phpSysInfo - A PHP System Information Script
// http://phpsysinfo.sourceforge.net/

// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.

// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

// $Id$

function xml_hardware (&$hddtemp_devices)
{
    global $sysinfo;
    global $text;
    $pci_devices = ""; $ide_devices = ""; $usb_devices = ""; $scsi_devices = "";    

    $sys = $sysinfo->cpu_info();

    $ar_buf = $sysinfo->pci();

    if (count($ar_buf)) {
        for ($i = 0, $max = sizeof($ar_buf); $i < $max; $i++) {
            if ($ar_buf[$i]) {
                $pci_devices .= "      <Device><Name>" . htmlspecialchars(chop($ar_buf[$i]), ENT_QUOTES) . "</Name></Device>\n";
            } 
        } 
    } 

    $ar_buf = $sysinfo->ide();

    ksort($ar_buf);

    if (count($ar_buf)) {
        while (list($key, $value) = each($ar_buf)) {
            $ide_devices .= "      <Device>\n<Name>" . htmlspecialchars($key . ': ' . $ar_buf[$key]['model'], ENT_QUOTES) . "</Name>\n";
            if (isset($ar_buf[$key]['capacity'])) {
                $ide_devices .= '<Capacity>' . htmlspecialchars($ar_buf[$key]['capacity'], ENT_QUOTES) . '</Capacity>';
            }
	    $hddtemp_devices[] = $key;
	    $ide_devices .= "</Device>\n";
        } 
    } 

    $ar_buf = $sysinfo->scsi();
    ksort($ar_buf);

    if (count($ar_buf)) {
        while (list($key, $value) = each($ar_buf)) {
	    $scsi_devices .= "<Device>\n";
            if ($key >= '0' && $key <= '9') {
                $scsi_devices .= "      <Name>" . htmlspecialchars($ar_buf[$key]['model'], ENT_QUOTES) . "</Name>\n";
            } else {
                $scsi_devices .= "      <Name>" . htmlspecialchars($key . ': ' . $ar_buf[$key]['model'], ENT_QUOTES) . "</Name>\n";
            } 
            if (isset($ar_buf[$key]['capacity'])) {
                $scsi_devices .= '<Capacity>' . htmlspecialchars($ar_buf[$key]['capacity'], ENT_QUOTES) . '</Capacity>';
            } 
            $scsi_devices .= "</Device>\n";
        } 
    } 

    $ar_buf = $sysinfo->usb();

    if (count($ar_buf)) {
        for ($i = 0, $max = sizeof($ar_buf); $i < $max; $i++) {
            if ($ar_buf[$i]) {
                $usb_devices .= "      <Device><Name>" . htmlspecialchars(chop($ar_buf[$i]), ENT_QUOTES) . "</Name></Device>\n";
            } 
        } 
    } 

/* disabled since we output this information
    $ar_buf = $sysinfo->sbus();
    if (count($ar_buf)) {
        for ($i = 0, $max = sizeof($ar_buf); $i < $max; $i++) {
            if ($ar_buf[$i]) {
                $sbus_devices .= "      <Device>" . htmlspecialchars(chop($ar_buf[$i]), ENT_QUOTES) . "</Device>\n";
            } 
        } 
    } 
*/
    $_text = "  <Hardware>\n";
    $_text .= "    <CPU>\n";
    if (isset($sys['cpus'])) {
        $_text .= "      <Number>" . htmlspecialchars($sys['cpus'], ENT_QUOTES) . "</Number>\n";
    } 
    if (isset($sys['model'])) {
        $_text .= "      <Model>" . htmlspecialchars($sys['model'], ENT_QUOTES) . "</Model>\n";
    } 
    if (isset($sys['cpuspeed'])) {
        $_text .= "      <Cpuspeed>" . htmlspecialchars($sys['cpuspeed'], ENT_QUOTES) . "</Cpuspeed>\n";
    } 
    if (isset($sys['busspeed'])) {
        $_text .= "      <Busspeed>" . htmlspecialchars($sys['busspeed'], ENT_QUOTES) . "</Busspeed>\n";
    } 
    if (isset($sys['cache'])) {
        $_text .= "      <Cache>" . htmlspecialchars($sys['cache'], ENT_QUOTES) . "</Cache>\n";
    } 
    if (isset($sys['bogomips'])) {
        $_text .= "      <Bogomips>" . htmlspecialchars($sys['bogomips'], ENT_QUOTES) . "</Bogomips>\n";
    } 
    $_text .= "    </CPU>\n";

    $_text .= "    <PCI>\n";
    if ($pci_devices) {
        $_text .= $pci_devices;
    } 
    $_text .= "    </PCI>\n";

    $_text .= "    <IDE>\n";
    if ($ide_devices) {
        $_text .= $ide_devices;
    } 
    $_text .= "    </IDE>\n";

    $_text .= "    <SCSI>\n";
    if ($scsi_devices) {
        $_text .= $scsi_devices;
    } 
    $_text .= "    </SCSI>\n";

    $_text .= "    <USB>\n";
    if ($usb_devices) {
        $_text .= $usb_devices;
    } 
    $_text .= "    </USB>\n";

/*
    $_text .= "    <SBUS>\n";
    if ($sbus_devices) {
        $_text .= $sbus_devices;
    } 
    $_text .= "    </SBUS>\n";
*/

    $_text .= "  </Hardware>\n";

    return $_text;
} 

function html_hardware ()
{
    global $XPath;
    global $text;
    $pci_devices = ""; $ide_devices = ""; $usb_devices = ""; $scsi_devices = "";
    $textdir = direction();

    for ($i = 1, $max = sizeof($XPath->getDataParts('/phpsysinfo/Hardware/PCI')); $i < $max; $i++) {
        if ($XPath->match("/phpsysinfo/Hardware/PCI/Device[$i]/Name")) {
            $pci_devices .= $XPath->getData("/phpsysinfo/Hardware/PCI/Device[$i]/Name") . '<br />';
        } 
    } 

    for ($i = 1, $max = sizeof($XPath->getDataParts('/phpsysinfo/Hardware/IDE')); $i < $max; $i++) {
        if ($XPath->match("/phpsysinfo/Hardware/IDE/Device[$i]")) {
            $ide_devices .= $XPath->getData("/phpsysinfo/Hardware/IDE/Device[$i]/Name");
	    if ($XPath->match("/phpsysinfo/Hardware/IDE/Device[$i]/Capacity")) {
		$ide_devices .= " (" . $text['capacity'] . ": " . format_bytesize($XPath->getData("/phpsysinfo/Hardware/IDE/Device[$i]/Capacity") / 2) . ")";
	    }
	    $ide_devices .= '<br />';
        } 
    } 

    for ($i = 1, $max = sizeof($XPath->getDataParts('/phpsysinfo/Hardware/SCSI')); $i < $max; $i++) {
        if ($XPath->match("/phpsysinfo/Hardware/SCSI/Device[$i]")) {
            $scsi_devices .= $XPath->getData("/phpsysinfo/Hardware/SCSI/Device[$i]/Name");
	    if ($XPath->match("/phpsysinfo/Hardware/SCSI/Device[$i]/Capacity")) {
		$scsi_devices .= " (" . $text['capacity'] . ": " . format_bytesize($XPath->getData("/phpsysinfo/Hardware/SCSI/Device[$i]/Capacity") / 2) . ")";
	    }
	    $scsi_devices .= '<br />';
        } 
    } 

    for ($i = 1, $max = sizeof($XPath->getDataParts('/phpsysinfo/Hardware/USB')); $i < $max; $i++) {
        if ($XPath->match("/phpsysinfo/Hardware/USB/Device[$i]/Name")) {
            $usb_devices .= $XPath->getData("/phpsysinfo/Hardware/USB/Device[$i]/Name") . '<br />';
        } 
    } 

    $_text = "<table border=\"0\" width=\"100%\" align=\"center\">\n";

    if ($XPath->match("/phpsysinfo/Hardware/CPU/Number")) {
        $_text .= "  <tr>\n    <td valign=\"top\"><font size=\"-1\">" . $text['numcpu'] . "</font></td>\n    <td><font size=\"-1\">" . $XPath->getData("/phpsysinfo/Hardware/CPU/Number") . "</font></td>\n  </tr>\n";
    } 
    if ($XPath->match("/phpsysinfo/Hardware/CPU/Model")) {
        $_text .= "  <tr>\n    <td valign=\"top\"><font size=\"-1\">" . $text['cpumodel'] . "</font></td>\n    <td><font size=\"-1\">" . $XPath->getData("/phpsysinfo/Hardware/CPU/Model") . "</font></td>\n  </tr>\n";
    } 

    if ($XPath->match("/phpsysinfo/Hardware/CPU/Cpuspeed")) {
        $tmp_speed = $XPath->getData("/phpsysinfo/Hardware/CPU/Cpuspeed");
        if ($tmp_speed < 1000) {
            $_text .= "  <tr>\n    <td valign=\"top\"><font size=\"-1\">" . $text['cpuspeed'] . "</font></td>\n    <td><font size=\"-1\">" . $tmp_speed . " MHz</font></td>\n  </tr>\n";
        } else {
            $_text .= "  <tr>\n    <td valign=\"top\"><font size=\"-1\">" . $text['cpuspeed'] . "</font></td>\n    <td><font size=\"-1\">" . round($tmp_speed / 1000, 2) . " GHz</font></td>\n  </tr>\n";
        } 
    } 
    if ($XPath->match("/phpsysinfo/Hardware/CPU/Busspeed")) {
        $tmp_speed = $XPath->getData("/phpsysinfo/Hardware/CPU/Busspeed");
        if ($tmp_speed < 1000) {
            $_text .= "  <tr>\n    <td valign=\"top\"><font size=\"-1\">" . $text['busspeed'] . "</font></td>\n    <td><font size=\"-1\">" . $tmp_speed . " MHz</font></td>\n  </tr>\n";
        } else {
            $_text .= "  <tr>\n    <td valign=\"top\"><font size=\"-1\">" . $text['busspeed'] . "</font></td>\n    <td><font size=\"-1\">" . round($tmp_speed / 1000, 2) . " GHz</font></td>\n  </tr>\n";
        } 
    } 
    if ($XPath->match("/phpsysinfo/Hardware/CPU/Cache")) {
        $_text .= "  <tr>\n    <td valign=\"top\"><font size=\"-1\">" . $text['cache'] . "</font></td>\n    <td><font size=\"-1\">" . $XPath->getData("/phpsysinfo/Hardware/CPU/Cache") . "</font></td>\n  </tr>\n";
    } 
    if ($XPath->match("/phpsysinfo/Hardware/CPU/Bogomips")) {
        $_text .= "  <tr>\n    <td valign=\"top\"><font size=\"-1\">" . $text['bogomips'] . "</font></td>\n    <td><font size=\"-1\">" . $XPath->getData("/phpsysinfo/Hardware/CPU/Bogomips") . "</font></td>\n  </tr>\n";
    } 

    $_text .= "  <tr>\n    <td valign=\"top\"><font size=\"-1\">" . $text['pci'] . "</font></td>\n    <td><font size=\"-1\">";
    if ($pci_devices) {
        $_text .= $pci_devices;
    } else {
        $_text .= "<i>" . $text['none'] . "</i>";
    } 
    $_text .= "</font></td>\n  </tr>\n";

    $_text .= "  <tr>\n    <td valign=\"top\"><font size=\"-1\">" . $text['ide'] . "</font></td>\n    <td><font size=\"-1\">";
    if ($ide_devices) {
        $_text .= $ide_devices;
    } else {
        $_text .= "<i>" . $text['none'] . "</i>";
    } 
    $_text .= "</font></td>\n  </tr>\n";

    if ($scsi_devices) {
        $_text .= "  <tr>\n    <td valign=\"top\"><font size=\"-1\">" . $text['scsi'] . "</font></td>\n    <td><font size=\"-1\">" . $scsi_devices . "</font></td>\n  </tr>";
    } 

    if ($usb_devices) {
        $_text .= "  <tr>\n    <td valign=\"top\"><font size=\"-1\">" . $text['usb'] . "</font></td>\n    <td><font size=\"-1\">" . $usb_devices . "</font></td>\n  </tr>";
    } 

    $_text .= "</table>";

    return $_text;
} 

?>
