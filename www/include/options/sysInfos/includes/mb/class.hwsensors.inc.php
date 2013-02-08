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

class mbinfo {
    var $lines;

  function temperature() {
    $ar_buf = array();
    $lines = array();
    $results = array();

    if (!isset($this->lines) ) {
        $this->lines = execute_program('sysctl', '-w hw.sensors');
    }

    $lines = explode("\n", $this->lines);

    for ($i = 0, $j = 0, $max = sizeof($lines); $i < $max; $i++) {
      $ar_buf = preg_split("/[\s,]+/", $lines[$i]);

      if ($ar_buf[2] == 'temp') {
        $results[$j]['label'] = $ar_buf[1];
        $results[$j]['value'] = $ar_buf[3];
        $results[$j]['limit'] = '70.0';
        $results[$j]['percent'] = $results[$j]['value'] * 100 / $results[$j]['limit'];
        $j++;
      }
    }

    return $results;
  } 

  function fans() {
    $ar_buf = array();
    $lines = array();
    $results = array();

    if (!isset($this->lines) ) {
        $this->lines = execute_program('sysctl', '-w hw.sensors');
    }

    $lines = explode("\n", $this->lines);

    for ($i = 0, $j = 0, $max = sizeof($lines); $i < $max; $i++) {
      $ar_buf = preg_split("/[\s,]+/", $lines[$i]);

      if ($ar_buf[2] == 'fanrpm') {
        $results[$j]['label'] = $ar_buf[1];
        $results[$j]['value'] = $ar_buf[3];
        $j++;
      }
    }

    return $results;
  } 

  function voltage() {
    $ar_buf = array();
    $lines = array();
    $results = array();

    if (!isset($this->lines) ) {
        $this->lines = execute_program('sysctl', '-w hw.sensors');
    }

    $lines = explode("\n", $this->lines);

    for ($i = 0, $j = 0, $max = sizeof($lines); $i < $max; $i++) {
      $ar_buf = preg_split("/[\s,]+/", $lines[$i]);

      if ($ar_buf[2] == 'volts_dc') {
        $results[$j]['label'] = $ar_buf[1];
        $results[$j]['value'] = $ar_buf[3];
        $results[$j]['min'] = '0.00';
        $results[$j]['max'] = '0.00';
        $j++;
      }
    }

    return $results;
  } 
} 

?>
