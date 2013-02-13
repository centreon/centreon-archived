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
// Note: Make sure you set MBM5 Interval Logging to csv and to the root of PHPSysInfo.
// Also make sure MBM5 doesn't at symbols to the values. Did is a Quick MBM5 log parser,
// need more csv logs to make it better.
//
class mbinfo {
    var $buf_label;
    var $buf_value;

  function temperature() {
    $results = array();

    if (!isset($this->buf_label)) {
      if ($fp = fopen('MBM5.csv', 'r')) {
	    $this->buf_label = split(';', fgets($fp));
        $this->buf_value = split(';', fgets($fp));
        fclose($fp);
      }
    }
    
    $results[0]['label'] = $this->buf_label[3];
    $results[0]['value'] = $this->buf_value[3];
    $results[0]['limit'] = '70.0';
    $results[0]['percent'] = $results[0]['value'] * 100 / $results[0]['limit'];
    $results[1]['label'] = $this->buf_label[4];
    $results[1]['value'] = $this->buf_value[4];
    $results[1]['limit'] = '70.0';
    $results[1]['percent'] = $results[1]['value'] * 100 / $results[1]['limit'];
    $results[2]['label'] = $this->buf_label[5];
    $results[2]['value'] = $this->buf_value[5];
    $results[2]['limit'] = '70.0';
    $results[2]['percent'] = $results[2]['value'] * 100 / $results[2]['limit'];
    return $results;
  } 

  function fans() {
    $results = array();

    if (!isset($this->buf_label)) {
      if ($fp = fopen('MBM5.csv', 'r')) {
	    $this->buf_label = split(';', fgets($fp));
        $this->buf_value = split(';', fgets($fp));
        fclose($fp);
      }
    }
    
    $results[0]['label'] = $this->buf_label[13];
    $results[0]['value'] = $this->buf_value[13];
    $results[0]['min'] = '3000';
    $results[0]['div'] = '2';
    $results[1]['label'] = $this->buf_label[14];
    $results[1]['value'] = $this->buf_value[14];
    $results[1]['min'] = '3000';
    $results[1]['div'] = '2';
    $results[2]['label'] = $this->buf_label[15];
    $results[2]['value'] = $this->buf_value[15];
    $results[2]['min'] = '3000';
    $results[2]['div'] = '2';

    return $results;
  } 

  function voltage() {
    $results = array();

    if (!isset($this->buf_label)) {
      if ($fp = fopen('MBM5.csv', 'r')) {
	    $this->buf_label = split(';', fgets($fp));
        $this->buf_value = split(';', fgets($fp));
        fclose($fp);
      }
    }
   
    $results[0]['label'] = $this->buf_label[6];
    $results[0]['value'] = $this->buf_value[6];
    $results[0]['min'] = '0.00';
    $results[0]['max'] = '0.00';
    $results[1]['label'] = $this->buf_label[7];
    $results[1]['value'] = $this->buf_value[7];
    $results[1]['min'] = '0.00';
    $results[1]['max'] = '0.00';
    $results[2]['label'] = $this->buf_label[8];
    $results[2]['value'] = $this->buf_value[8];
    $results[2]['min'] = '0.00';
    $results[2]['max'] = '0.00';
    $results[3]['label'] = $this->buf_label[9];
    $results[3]['value'] = $this->buf_value[9];
    $results[3]['min'] = '0.00';
    $results[3]['max'] = '0.00';
    $results[4]['label'] = $this->buf_label[10];
    $results[4]['value'] = $this->buf_value[10];
    $results[4]['min'] = '0.00';
    $results[4]['max'] = '0.00';
    $results[5]['label'] = $this->buf_label[11];
    $results[5]['value'] = $this->buf_value[11];
    $results[5]['min'] = '0.00';
    $results[5]['max'] = '0.00';
    $results[6]['label'] = $this->buf_label[12];
    $results[6]['value'] = $this->buf_value[12];
    $results[6]['min'] = '0.00';
    $results[6]['max'] = '0.00';

    return $results;
  } 
} 

?>
