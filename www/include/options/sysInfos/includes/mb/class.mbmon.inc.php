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

// This class was created by Z. Frombach ( zoltan at frombach dot com )

// $Id$

class mbinfo {
	var $lines;

  function temperature() {
    $results = array();

	if (!isset($this->lines) ) {
	    $this->lines = explode("\n", execute_program('mbmon', '-c 1 -r'));
	}

    $i = 0;
    foreach($this->lines as $line) {
      if (preg_match('/^(TEMP\d*)\s*:\s*(.*)$/D', $line, $data)) {
        if ($data[2]<>'0') {
          $results[$i]['label'] = $data[1];
          $results[$i]['limit'] = '70.0';
	  if($data[2] > 250) {
	    $results[$i]['value'] = 0;
	    $results[$i]['percent'] = 0;
	  } else {
            $results[$i]['value'] = $data[2];
            $results[$i]['percent'] = $results[$i]['value'] * 100 / $results[$i]['limit'];
	  }
          $i++;
        }
      }
    }
    return $results;
  }

  function fans() {
    $results = array();

	if (!isset($this->lines) ) {
	    $this->lines = explode("\n", execute_program('mbmon', '-c 1 -r'));
	}

    $i = 0;
    foreach($this->lines as $line) {
      if (preg_match('/^(FAN\d*)\s*:\s*(.*)$/D', $line, $data)) {
        if ($data[2]<>'0') {
          $results[$i]['label'] = $data[1];
          $results[$i]['value'] = $data[2];
          $results[$i]['min'] = '3000';
          $results[$i]['div'] = '2';
          $i++;
        }
      }
    }
    return $results;
  }

  function voltage() {
    $results = array();

	if (!isset($this->lines) ) {
	    $this->lines = explode("\n", execute_program('mbmon', '-c 1 -r'));
	}

    $i = 0;
    foreach($this->lines as $line) {
      if (preg_match('/^(V.*)\s*:\s*(.*)$/D', $line, $data)) {
        if ($data[2]<>'+0.00') {
          $results[$i]['label'] = $data[1];
          $results[$i]['value'] = $data[2];
          $results[$i]['min'] = '0.00';
          $results[$i]['max'] = '0.00';
          $i++;
        }
      }
    }

    return $results;
  }
}

?>
