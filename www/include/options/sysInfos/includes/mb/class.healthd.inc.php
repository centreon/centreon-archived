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
    $results = array();

    if (!isset($this->lines)) {
      $this->lines = execute_program('healthdc', '-t');
    }

    $ar_buf = preg_split("/\t+/", $this->lines);

    $results[0]['label'] = 'temp1';
    $results[0]['value'] = $ar_buf[1];
    $results[0]['limit'] = '70.0';
    $results[0]['percent'] = $results[0]['value'] * 100 / $results[0]['limit'];
    $results[1]['label'] = 'temp2';
    $results[1]['value'] = $ar_buf[2];
    $results[1]['limit'] = '70.0';
    $results[1]['percent'] = $results[1]['value'] * 100 / $results[1]['limit'];
    $results[2]['label'] = 'temp3';
    $results[2]['value'] = $ar_buf[3];
    $results[2]['limit'] = '70.0';
    $results[2]['percent'] = $results[2]['value'] * 100 / $results[2]['limit'];
    return $results;
  } 

  function fans() {
    $ar_buf = array();
    $results = array();

    if (!isset($this->lines)) {
      $this->lines = execute_program('healthdc', '-t');
    }

    $ar_buf = preg_split("/\t+/", $this->lines);

    $results[0]['label'] = 'fan1';
    $results[0]['value'] = $ar_buf[4];
    $results[0]['min'] = '3000';
    $results[0]['div'] = '2';
    $results[1]['label'] = 'fan2';
    $results[1]['value'] = $ar_buf[5];
    $results[1]['min'] = '3000';
    $results[1]['div'] = '2';
    $results[2]['label'] = 'fan3';
    $results[2]['value'] = $ar_buf[6];
    $results[2]['min'] = '3000';
    $results[2]['div'] = '2';

    return $results;
  } 

  function voltage() {
    $ar_buf = array();
    $results = array();

    if (!isset($this->lines)) {
      $this->lines = execute_program('healthdc', '-t');
    }

    $ar_buf = preg_split("/\t+/", $this->lines);

    $results[0]['label'] = 'Vcore1';
    $results[0]['value'] = $ar_buf[7];
    $results[0]['min'] = '0.00';
    $results[0]['max'] = '0.00';
    $results[1]['label'] = 'Vcore2';
    $results[1]['value'] = $ar_buf[8];
    $results[1]['min'] = '0.00';
    $results[1]['max'] = '0.00';
    $results[2]['label'] = '3volt';
    $results[2]['value'] = $ar_buf[9];
    $results[2]['min'] = '0.00';
    $results[2]['max'] = '0.00';
    $results[3]['label'] = '+5Volt';
    $results[3]['value'] = $ar_buf[10];
    $results[3]['min'] = '0.00';
    $results[3]['max'] = '0.00';
    $results[4]['label'] = '+12Volt';
    $results[4]['value'] = $ar_buf[11];
    $results[4]['min'] = '0.00';
    $results[4]['max'] = '0.00';
    $results[5]['label'] = '-12Volt';
    $results[5]['value'] = $ar_buf[12];
    $results[5]['min'] = '0.00';
    $results[5]['max'] = '0.00';
    $results[6]['label'] = '-5Volt';
    $results[6]['value'] = $ar_buf[13];
    $results[6]['min'] = '0.00';
    $results[6]['max'] = '0.00';

    return $results;
  } 
} 

?>
