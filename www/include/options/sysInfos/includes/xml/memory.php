<?php
//
// phpSysInfo - A PHP System Information Script
// http://phpsysinfo.sourceforge.net/
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
//
// $Id$

//
// xml_memory()
//
function xml_memory () {
    global $sysinfo;
    $mem = $sysinfo->memory();

    $_text = "  <Memory>\n"
           . "    <Free>" . htmlspecialchars($mem['ram']['t_free'], ENT_QUOTES, "UTF-8") . "</Free>\n"
           . "    <Used>" . htmlspecialchars($mem['ram']['t_used'], ENT_QUOTES, "UTF-8") . "</Used>\n"
           . "    <Total>" . htmlspecialchars($mem['ram']['total'], ENT_QUOTES, "UTF-8") . "</Total>\n"
           . "    <Percent>" . htmlspecialchars($mem['ram']['percent'], ENT_QUOTES, "UTF-8") . "</Percent>\n";
	   
    if (isset($mem['ram']['app_percent']))
      $_text .= "    <App>" . htmlspecialchars($mem['ram']['app'], ENT_QUOTES, "UTF-8") . "</App>\n    <AppPercent>" . htmlspecialchars($mem['ram']['app_percent'], ENT_QUOTES, "UTF-8") . "</AppPercent>\n";
    if (isset($mem['ram']['buffers_percent']))
      $_text .= "    <Buffers>" . htmlspecialchars($mem['ram']['buffers'], ENT_QUOTES, "UTF-8") . "</Buffers>\n    <BuffersPercent>" . htmlspecialchars($mem['ram']['buffers_percent'], ENT_QUOTES, "UTF-8") . "</BuffersPercent>\n";
    if (isset($mem['ram']['cached_percent']))
      $_text .= "    <Cached>" . htmlspecialchars($mem['ram']['cached'], ENT_QUOTES, "UTF-8") . "</Cached>\n    <CachedPercent>" . htmlspecialchars($mem['ram']['cached_percent'], ENT_QUOTES, "UTF-8") . "</CachedPercent>\n";
      
    $_text .= "  </Memory>\n"
           . "  <Swap>\n"
           . "    <Free>" . htmlspecialchars($mem['swap']['free'], ENT_QUOTES, "UTF-8") . "</Free>\n"
           . "    <Used>" . htmlspecialchars($mem['swap']['used'], ENT_QUOTES, "UTF-8") . "</Used>\n"
           . "    <Total>" . htmlspecialchars($mem['swap']['total'], ENT_QUOTES, "UTF-8") . "</Total>\n"
           . "    <Percent>" . htmlspecialchars($mem['swap']['percent'], ENT_QUOTES, "UTF-8") . "</Percent>\n"
           . "  </Swap>\n"
	   . "  <Swapdevices>\n";
    $i = 0;
    foreach ($mem['devswap'] as $device) {
	$_text .="    <Mount>\n"
	       . "     <MountPointID>" . htmlspecialchars($i++, ENT_QUOTES, "UTF-8") . "</MountPointID>\n"
	       . "     <Type>Swap</Type>"
	       . "     <Device><Name>" . htmlspecialchars($device['dev'], ENT_QUOTES, "UTF-8") . "</Name></Device>\n"
    	       . "     <Percent>" . htmlspecialchars($device['percent'], ENT_QUOTES, "UTF-8") . "</Percent>\n"
    	       . "     <Free>" . htmlspecialchars($device['free'], ENT_QUOTES, "UTF-8") . "</Free>\n"
    	       . "     <Used>" . htmlspecialchars($device['used'], ENT_QUOTES, "UTF-8") . "</Used>\n"
    	       . "     <Size>" . htmlspecialchars($device['total'], ENT_QUOTES, "UTF-8") . "</Size>\n"
    	       . "    </Mount>\n";
    }
    $_text .= "  </Swapdevices>\n";

    return $_text;
}

//
// xml_memory()
//
function html_memory () {
    global $XPath;
    global $text;

    $textdir = direction();
    $scale_factor = 2;

    $ram = create_bargraph($XPath->getData("/phpsysinfo/Memory/Used"), $XPath->getData("/phpsysinfo/Memory/Total"), $scale_factor);
    $ram .= "&nbsp;&nbsp;" . $XPath->getData("/phpsysinfo/Memory/Percent") . "% ";

    $swap = create_bargraph($XPath->getData("/phpsysinfo/Swap/Used"), $XPath->getData("/phpsysinfo/Swap/Total"), $scale_factor);
    $swap .= "&nbsp;&nbsp;" . $XPath->getData("/phpsysinfo/Swap/Percent") . "% ";

    if ($XPath->match("/phpsysinfo/Memory/AppPercent")) {
	$app = create_bargraph($XPath->getData("/phpsysinfo/Memory/App"), $XPath->getData("/phpsysinfo/Memory/Total"), $scale_factor);
        $app .= "&nbsp;&nbsp;" . $XPath->getData("/phpsysinfo/Memory/AppPercent") . "% ";
    }
    if ($XPath->match("/phpsysinfo/Memory/BuffersPercent")) {
	$buffers = create_bargraph($XPath->getData("/phpsysinfo/Memory/Buffers"), $XPath->getData("/phpsysinfo/Memory/Total"), $scale_factor);
        $buffers .= "&nbsp;&nbsp;" . $XPath->getData("/phpsysinfo/Memory/BuffersPercent") . "% ";
    }
    if ($XPath->match("/phpsysinfo/Memory/CachedPercent")) {
	$cached = create_bargraph($XPath->getData("/phpsysinfo/Memory/Cached"), $XPath->getData("/phpsysinfo/Memory/Total"), $scale_factor);
        $cached .= "&nbsp;&nbsp;" . $XPath->getData("/phpsysinfo/Memory/CachedPercent") . "% ";
    }

    $_text = "<table border=\"0\" width=\"100%\" align=\"center\">\n"
           . "  <tr>\n"
	   . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['type'] . "</b></font></td>\n"
           . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['percent'] . "</b></font></td>\n"
           . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['free'] . "</b></font></td>\n"
           . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['used'] . "</b></font></td>\n"
           . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['size'] . "</b></font></td>\n"
	   . "  </tr>\n"
	   
           . "  <tr>\n"
	   . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $text['phymem'] . "</font></td>\n"
           . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $ram . "</font></td>\n"
           . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Memory/Free")) . "</font></td>\n"
           . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Memory/Used")) . "</font></td>\n"
           . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Memory/Total")) . "</font></td>\n"
	   . "  </tr>\n";

    if (isset($app)) {
      $_text .= "  <tr>\n"
    	      . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $text['app'] . "</font></td>\n"
    	      . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $app . "</font></td>\n"
	      . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">&nbsp;</font></td>\n"
	      . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Memory/App")) . "</font></td>\n"
	      . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">&nbsp;</font></td>\n"
	      . "  </tr>\n";
    }

    if (isset($buffers)) {
      $_text .= "  <tr>\n"
    	      . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $text['buffers'] . "</font></td>\n"
    	      . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $buffers . "</font></td>\n"
	      . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">&nbsp;</font></td>\n"
	      . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Memory/Buffers")) . "</font></td>\n"
	      . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">&nbsp;</font></td>\n"
	      . "  </tr>\n";
    }

    if (isset($cached)) {
      $_text .= "  <tr>\n"
    	      . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $text['cached'] . "</font></td>\n"
    	      . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $cached . "</font></td>\n"
	      . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">&nbsp;</font></td>\n"
	      . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Memory/Cached")) . "</font></td>\n"
	      . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">&nbsp;</font></td>\n"
	      . "  </tr>\n";
    }

    $_text .= "  <tr>\n"
            . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $text['swap'] . "</font></td>\n"
            . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $swap . "</font></td>\n"
            . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Swap/Free")) . "</font></td>\n"
            . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Swap/Used")) . "</font></td>\n"
            . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Swap/Total")) . "</font></td>\n"
	    . "  </tr>\n";

    if (($max = sizeof($XPath->getDataParts("/phpsysinfo/Swapdevices"))) > 2) {
      for($i = 1; $i < $max; $i++) {
        $swapdev = create_bargraph($XPath->getData("/phpsysinfo/Swapdevices/Mount[$i]/Used"), $XPath->getData("/phpsysinfo/Swapdevices/Mount[$i]/Size"), $scale_factor);
        $swapdev .= "&nbsp;&nbsp;" . $XPath->getData("/phpsysinfo/Swapdevices/Mount[$i]/Percent") . "% ";
        $_text .= "  <tr>\n"
		. "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\"> - " . $XPath->getData("/phpsysinfo/Swapdevices/Mount[$i]/Device/Name") . "</font></td>\n"
                . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $swapdev . "</font></td>\n"
                . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Swapdevices/Mount[$i]/Free")) . "</font></td>\n"
                . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Swapdevices/Mount[$i]/Used")) . "</font></td>\n"
                . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/Swapdevices/Mount[$i]/Size")) . "</font></td>\n"
		. "  </tr>\n";
      }
    }
    $_text .= "</table>";

    return $_text;
}

?>
