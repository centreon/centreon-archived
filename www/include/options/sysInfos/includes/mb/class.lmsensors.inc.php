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
if (!defined('IN_PHPSYSINFO')) {
    die("No Hacking");
}

require_once(APP_ROOT . "/includes/common_functions.php");

class mbinfo {
  var $lines;

  function mbinfo() {
   $lines = execute_program("sensors", "");
   // Martijn Stolk: Dirty fix for misinterpreted output of sensors, 
   // where info could come on next line when the label is too long.
   $lines = str_replace(":\n", ":", $lines);
   $lines = str_replace("\n\n", "\n", $lines);
   $this->lines = explode("\n", $lines);
  }
  
  function temperature() {
    $ar_buf = array();
    $results = array();

    $sensors_value = $this->lines;

    foreach($sensors_value as $line) {
      $data = array();
      if (ereg("(.*):(.*)\((.*)=(.*),(.*)=(.*)\)(.*)", $line, $data)) ;
      elseif (ereg("(.*):(.*)\((.*)=(.*)\)(.*)", $line, $data)) ;
      else (ereg("(.*):(.*)", $line, $data));
      if (count($data) > 1) {
        $temp = substr(trim($data[2]), -1);
        switch ($temp) {
          case "C";
          case "F":
            array_push($ar_buf, $line);
            break;
        }
      }
    }

    $i = 0;
    foreach($ar_buf as $line) {
      unset($data);
      if (ereg("(.*):(.*).C[ ]*\((.*)=(.*).C,(.*)=(.*).C\)(.*)\)", $line, $data)) ;
      elseif (ereg("(.*):(.*).C[ ]*\((.*)=(.*).C,(.*)=(.*).C\)(.*)", $line, $data)) ;
      elseif (ereg("(.*):(.*).C[ ]*\((.*)=(.*).C\)(.*)", $line, $data)) ;
      else (ereg("(.*):(.*).C", $line, $data));

      $results[$i]['label'] = $data[1];
      $results[$i]['value'] = trim($data[2]);
      $results[$i]['limit'] = isset($data[4]) ? trim($data[4]) : "+60";
      $results[$i]['perce'] = isset($data[6]) ? trim($data[6]) : "+60";
      if ($results[$i]['limit'] < $results[$i]['perce']) {	 	
         $results[$i]['limit'] = $results[$i]['perce'];	 	
       }      
      $i++;
    }

    asort($results);
    return array_values($results);
  }

  function fans() {
    $ar_buf = array();
    $results = array();

    $sensors_value = $this->lines;

    foreach($sensors_value as $line) {
      $data = array();
      if (ereg("(.*):(.*)\((.*)=(.*),(.*)=(.*)\)(.*)", $line, $data));
      elseif (ereg("(.*):(.*)\((.*)=(.*)\)(.*)", $line, $data));
      else ereg("(.*):(.*)", $line, $data);

      if (count($data) > 1) {
        $temp = explode(" ", trim($data[2]));
        if (count($temp) == 1)
          $temp = explode("\xb0", trim($data[2]));
	if(isset($temp[1])) {
          switch ($temp[1]) {
            case "RPM":
              array_push($ar_buf, $line);
              break;
          }
	}
      }
    }

    $i = 0;
    foreach($ar_buf as $line) {
      unset($data);
      if (ereg("(.*):(.*) RPM  \((.*)=(.*) RPM,(.*)=(.*)\)(.*)\)", $line, $data));
      elseif (ereg("(.*):(.*) RPM  \((.*)=(.*) RPM,(.*)=(.*)\)(.*)", $line, $data));
      elseif (ereg("(.*):(.*) RPM  \((.*)=(.*) RPM\)(.*)", $line, $data));
      else ereg("(.*):(.*) RPM", $line, $data);

      $results[$i]['label'] = trim($data[1]);
      $results[$i]['value'] = trim($data[2]);
      $results[$i]['min'] = isset($data[4]) ? trim($data[4]) : 0;
      $results[$i]['div'] = isset($data[6]) ? trim($data[6]) : 0;
      $i++;
    }

    asort($results);
    return array_values($results);
  }

  function voltage() {
    $ar_buf = array();
    $results = array();

    $sensors_value = $this->lines;

    foreach($sensors_value as $line) {
      $data = array();
      if (ereg("(.*):(.*)\((.*)=(.*),(.*)=(.*)\)(.*)", $line, $data));
      else ereg("(.*):(.*)", $line, $data);
      
      if (count($data) > 1) {
        $temp = explode(" ", trim($data[2]));
        if (count($temp) == 1)
          $temp = explode("\xb0", trim($data[2]));
        if (isset($temp[1])) {
          switch ($temp[1]) {
            case "V":
              array_push($ar_buf, $line);
              break;
	  }
        }
      }
    }

    $i = 0;
    foreach($ar_buf as $line) {
      unset($data);
      if (ereg("(.*):(.*) V  \((.*)=(.*) V,(.*)=(.*) V\)(.*)\)", $line, $data));
      elseif (ereg("(.*):(.*) V  \((.*)=(.*) V,(.*)=(.*) V\)(.*)", $line, $data));
      else ereg("(.*):(.*) V$", $line, $data);
      if(isset($data[1])) {
        $results[$i]['label'] = trim($data[1]);
        $results[$i]['value'] = trim($data[2]);
        $results[$i]['min'] = isset($data[4]) ? trim($data[4]) : 0;
        $results[$i]['max'] = isset($data[6]) ? trim($data[6]) : 0;
        $i++;
      }
    }
    return $results;
  }
}

?>
