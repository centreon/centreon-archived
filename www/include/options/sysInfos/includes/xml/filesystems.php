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
// xml_filesystems()
//
function xml_filesystems () {
    global $sysinfo;
    global $show_mount_point;
    
    $fs = $sysinfo->filesystems();

    $_text = "  <FileSystem>\n";
    for ($i=0, $max = sizeof($fs); $i < $max; $i++) {
        $_text .= "    <Mount>\n";
        $_text .= "      <MountPointID>" . htmlspecialchars($i, ENT_QUOTES, "UTF-8") . "</MountPointID>\n";

        if ($show_mount_point) {
          $_text .= "      <MountPoint>" . htmlspecialchars($fs[$i]['mount'], ENT_QUOTES, "UTF-8") . "</MountPoint>\n";
        }

        $_text .= "      <Type>" . htmlspecialchars($fs[$i]['fstype'], ENT_QUOTES, "UTF-8") . "</Type>\n"
                . "      <Device><Name>" . htmlspecialchars($fs[$i]['disk'], ENT_QUOTES, "UTF-8") . "</Name></Device>\n"
                . "      <Percent>" . htmlspecialchars($fs[$i]['percent'], ENT_QUOTES, "UTF-8") . "</Percent>\n"
                . "      <Free>" . htmlspecialchars($fs[$i]['free'], ENT_QUOTES, "UTF-8") . "</Free>\n"
                . "      <Used>" . htmlspecialchars($fs[$i]['used'], ENT_QUOTES, "UTF-8") . "</Used>\n"
                . "      <Size>" . htmlspecialchars($fs[$i]['size'], ENT_QUOTES, "UTF-8") . "</Size>\n";
	if (isset($fs[$i]['options']))
	    $_text .= "      <Options>" . htmlspecialchars($fs[$i]['options'], ENT_QUOTES, "UTF-8") . "</Options>\n";
        $_text  .= "    </Mount>\n";
    }
    $_text .= "  </FileSystem>\n";
    return $_text;
}

//
// html_filesystems()
//
function html_filesystems () {
    global $XPath;
    global $text;
    global $show_mount_point;
    
    $textdir = direction();
    
    $sum = array("size" => 0, "used" => 0, "free" => 0);

    $counted_devlist = array();
    $scale_factor = 2;

    $_text  = "<table border=\"0\" width=\"100%\" align=\"center\">\n";
    $_text .= "  <tr>\n";

    if ($show_mount_point) {
      $_text .= "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['mount'] . "</b></font></td>\n";
    }

    $_text .= "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['type'] . "</b></font></td>\n"
            . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['partition'] . "</b></font></td>\n"
            . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['percent'] . "</b></font></td>\n"
            . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['free'] . "</b></font></td>\n"
            . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['used'] . "</b></font></td>\n"
            . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['size'] . "</b></font></td>\n  </tr>\n";

    for ($i=1, $max = sizeof($XPath->getDataParts('/phpsysinfo/FileSystem')); $i < $max; $i++) {
        if ($XPath->match("/phpsysinfo/FileSystem/Mount[$i]/MountPointID")) {
	  if (!$XPath->match("/phpsysinfo/FileSystem/Mount[$i]/Options") || !stristr($XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Options"), "bind")) {
	    if (!in_array($XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Device/Name"), $counted_devlist)) {
              $sum['size'] += $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Size");
              $sum['used'] += $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Used");
              $sum['free'] += $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Free");
	      if (PHP_OS != "WINNT")
	        $counted_devlist[] = $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Device/Name");
	      else
	        $counted_devlist[] = $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/MountPoint");
	    }
	  }
            $_text .= "  <tr>\n";

            if ($show_mount_point) {
              $_text .= "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/MountPoint") . "</font></td>\n";
            }
            $_text .= "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Type") . "</font></td>\n"
                    . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">" . $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Device/Name") . "</font></td>\n"
                    . "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">"
                    . create_bargraph($XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Used"), $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Size"), $scale_factor, $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Type"))
                    . "&nbsp;" . $XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Percent") . "</font></td>\n"
                    . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Free")) . "</font></td>\n"
                    . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Used")) . "</font></td>\n"
                    . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($XPath->getData("/phpsysinfo/FileSystem/Mount[$i]/Size")) . "</font></td>\n"
                    . "  </tr>\n";
        }
    }

    $_text .= "  <tr>\n";

    if ($show_mount_point) {
      $_text .= "  <td colspan=\"3\" align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\"><i>" . $text['totals'] . " :&nbsp;&nbsp;</i></font></td>\n";
    } else {
      $_text .= "  <td colspan=\"2\" align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\"><i>" . $text['totals'] . " :&nbsp;&nbsp;</i></font></td>\n";
    }

    $_text .= "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">"
            . create_bargraph($sum['used'], $sum['size'], $scale_factor)
            . "&nbsp;" . round(100 / $sum['size'] *  $sum['used']) . "%" .  "</font></td>\n"
            . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($sum['free']) . "</font></td>\n"
            . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($sum['used']) . "</font></td>\n"
            . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">" . format_bytesize($sum['size']) . "</font></td>\n  </tr>\n"
            . "</table>\n";

    return $_text;
}
?>
