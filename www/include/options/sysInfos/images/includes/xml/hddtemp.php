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

function xml_hddtemp($hddtemp) {
  global $hddtemp_avail;
  $data = $hddtemp->temperature($hddtemp_avail);

  $_text = "  <HDDTemp>\n";
    if (sizeof($data) > 0) {
      for ($i=0, $max = sizeof($data); $i < $max; $i++) {
        $_text .= "     <Item>\n";
        $_text .= "        <Label>" . htmlspecialchars($data[$i]['label'], ENT_QUOTES, "UTF-8") . "</Label>\n";
        $_text .= "        <Value>" . htmlspecialchars($data[$i]['value'], ENT_QUOTES, "UTF-8") . "</Value>\n";
        $_text .= "        <Model>" . htmlspecialchars($data[$i]['model'], ENT_QUOTES, "UTF-8") . "</Model>\n";
        $_text .= "     </Item>\n";
      }
    }
    $_text .= "  </HDDTemp>\n";
    
    return $_text;
}

function html_hddtemp() {
  global $XPath;
  global $text;
  global $sensor_program;

  $textdir = direction();  
  $scale_factor = 2;
  $_text = "";
  $maxvalue = "+60";

  if ($XPath->match("/phpsysinfo/HDDTemp")) {
    for ($i=1, $max = sizeof($XPath->getDataParts('/phpsysinfo/HDDTemp')); $i < $max; $i++) {
      if( $XPath->getData("/phpsysinfo/HDDTemp/Item[$i]/Value") != 0) {
        $_text .= "  <tr>\n";
        $_text .= "    <td align=\"" . $textdir['left'] . "\" valign=\"top\"><font size=\"-1\">". $XPath->getData("/phpsysinfo/HDDTemp/Item[$i]/Model") . "</font></td>\n";
        $_text .= "    <td align=\"" . $textdir['left'] . "\" valign=\"top\" nowrap><font size=\"-1\">";
        $_text .= create_bargraph($XPath->getData("/phpsysinfo/HDDTemp/Item[$i]/Value"), $maxvalue, $scale_factor);
        $_text .= "&nbsp;" . round($XPath->getData("/phpsysinfo/HDDTemp/Item[$i]/Value")) . " " . $text['degree_mark'] . "</font></td>\n";
        $_text .= "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\">". $maxvalue . " " . $text['degree_mark'] . "</font></td></tr>\n";
      }
    }
  };
  if (strlen($_text) > 0 && empty($sensor_program)) {
    $_text = "  <tr>\n"
           . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['s_label'] . "</b></font></td>\n"
	   . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['s_value'] . "</b></font></td>\n"
	   . "    <td align=\"" . $textdir['right'] . "\" valign=\"top\"><font size=\"-1\"><b>" . $text['s_limit'] . "</b></font></td>\n"
	   . "  </tr>" . $_text;
  }
  return $_text;
};

?>
