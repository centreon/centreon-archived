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

require_once(APP_ROOT . '/includes/os/class.BSD.common.inc.php');

echo "<p align=center><b>Note: The Darwin version of phpSysInfo is work in progress, some things currently don't work</b></p>";

class sysinfo extends bsd_common {
  var $cpu_regexp;
  var $scsi_regexp; 
  // Our contstructor
  // this function is run on the initialization of this class
  function sysinfo () {
    // $this->cpu_regexp = "CPU: (.*) \((.*)-MHz (.*)\)";
    // $this->scsi_regexp1 = "^(.*): <(.*)> .*SCSI.*device";
  } 

  function grab_key ($key) {
    $s = execute_program('sysctl', $key);
    $s = ereg_replace($key . ': ', '', $s);
    $s = ereg_replace($key . ' = ', '', $s); // fix Apple set keys
    
    return $s;
  } 

  function grab_ioreg ($key) {
    $s = execute_program('ioreg', '-cls "' . $key . '" | grep "' . $key . '"'); //ioreg -cls "$key" | grep "$key"
    $s = ereg_replace('\|', '', $s);
    $s = ereg_replace('\+\-\o', '', $s);
    $s = ereg_replace('[ ]+', '', $s);
    $s = ereg_replace('<[^>]+>', '', $s); // remove possible XML conflicts

    return $s;
  } 

  function get_sys_ticks () {
    $a = execute_program('sysctl', '-n kern.boottime'); // get boottime (value in seconds) 
    $sys_ticks = time() - $a;

    return $sys_ticks;
  } 

  function cpu_info () {
    $results = array(); 
    // $results['model'] = $this->grab_key('hw.model'); // need to expand this somehow...
    // $results['model'] = $this->grab_key('hw.machine');
    $results['model'] = ereg_replace('Processor type: ', '', execute_program('hostinfo', '| grep "Processor type"')); // get processor type
    $results['cpus'] = $this->grab_key('hw.ncpu');
    $results['cpuspeed'] = round($this->grab_key('hw.cpufrequency') / 1000000); // return cpu speed - Mhz
    $results['busspeed'] = round($this->grab_key('hw.busfrequency') / 1000000); // return bus speed - Mhz
    $results['cache'] = round($this->grab_key('hw.l2cachesize') / 1024); // return l2 cache
    
    return $results;
  } 
  // get the pci device information out of ioreg
  function pci () {
    $results = array();
    $s = $this->grab_ioreg('IOPCIDevice');

    $lines = split("\n", $s);
    for ($i = 0, $max = sizeof($lines); $i < $max; $i++) {
      $ar_buf = preg_split("/\s+/", $lines[$i], 19);
      $results[$i] = $ar_buf[0];
    } 
    asort($results);
    return array_values(array_unique($results));
  } 
  // get the ide device information out of ioreg
  function ide () {
    $results = array(); 
    // ioreg | grep "Media  <class IOMedia>"
    $s = $this->grab_ioreg('IOATABlockStorageDevice'); 

    $lines = split("\n", $s);
    $j = 0;
    for ($i = 0, $max = sizeof($lines); $i < $max; $i++) {
      $ar_buf = preg_split("/\/\//", $lines[$i], 19);

      if ($ar_buf[1] == 'class IOMedia' && preg_match('/Media/', $ar_buf[0])) {
        $results[$j++]['model'] = $ar_buf[0];
      } 
    } 
    asort($results);
    return array_values(array_unique($results));
  } 

  function memory () {
    $s = $this->grab_key('hw.physmem');

    $results['ram'] = array();

    $pstat = execute_program('vm_stat'); // use darwin's vm_stat
    $lines = split("\n", $pstat);
    for ($i = 0, $max = sizeof($lines); $i < $max; $i++) {
      $ar_buf = preg_split("/\s+/", $lines[$i], 19);

      if ($i == 1) {
        $results['ram']['free'] = $ar_buf[2] * 4; // calculate free memory from page sizes (each page = 4MB)
      } 
    } 

    $results['ram']['total'] = $s / 1024;
    $results['ram']['shared'] = 0;
    $results['ram']['buffers'] = 0;
    $results['ram']['used'] = $results['ram']['total'] - $results['ram']['free'];
    $results['ram']['cached'] = 0;
    $results['ram']['t_used'] = $results['ram']['used'];
    $results['ram']['t_free'] = $results['ram']['free'];

    $results['ram']['percent'] = round(($results['ram']['used'] * 100) / $results['ram']['total']); 
    // need to fix the swap info...
    $pstat = execute_program('swapinfo', '-k');
    $lines = split("\n", $pstat);

    for ($i = 0, $max = sizeof($lines); $i < $max; $i++) {
      $ar_buf = preg_split("/\s+/", $lines[$i], 6);

      if ($i == 0) {
        $results['swap']['total'] = 0;
        $results['swap']['used'] = 0;
        $results['swap']['free'] = 0;
      } else {
        $results['swap']['total'] = $results['swap']['total'] + $ar_buf[1];
        $results['swap']['used'] = $results['swap']['used'] + $ar_buf[2];
        $results['swap']['free'] = $results['swap']['free'] + $ar_buf[3];
      } 
    } 
    $results['swap']['percent'] = round(($results['swap']['used'] * 100) / $results['swap']['total']);

    return $results;
  } 

  function network () {
    $netstat = execute_program('netstat', '-nbdi | cut -c1-24,42- | grep Link');
    $lines = split("\n", $netstat);
    $results = array();
    for ($i = 0, $max = sizeof($lines); $i < $max; $i++) {
      $ar_buf = preg_split("/\s+/", $lines[$i]);
      if (!empty($ar_buf[0])) {
        $results[$ar_buf[0]] = array();

        $results[$ar_buf[0]]['rx_bytes'] = $ar_buf[5];
        $results[$ar_buf[0]]['rx_packets'] = $ar_buf[3];
        $results[$ar_buf[0]]['rx_errs'] = $ar_buf[4];
        $results[$ar_buf[0]]['rx_drop'] = $ar_buf[10];

        $results[$ar_buf[0]]['tx_bytes'] = $ar_buf[8];
        $results[$ar_buf[0]]['tx_packets'] = $ar_buf[6];
        $results[$ar_buf[0]]['tx_errs'] = $ar_buf[7];
        $results[$ar_buf[0]]['tx_drop'] = $ar_buf[10];

        $results[$ar_buf[0]]['errs'] = $ar_buf[4] + $ar_buf[7];
        $results[$ar_buf[0]]['drop'] = $ar_buf[10];
      } 
    } 
    return $results;
  } 

  function filesystems () {
    $df = execute_program('df', '-k');
    $mounts = split("\n", $df);
    $fstype = array();

    $s = execute_program('mount');
    $lines = explode("\n", $s);

    $i = 0;
    while (list(, $line) = each($lines)) {
      ereg('(.*) \((.*)\)', $line, $a);

      $m = explode(' ', $a[0]);
      $fsdev[$m[0]] = $a[2];
    } 

    for ($i = 1, $j = 0, $max = sizeof($mounts); $i < $max; $i++) {
      $ar_buf = preg_split("/\s+/", $mounts[$i], 6);

      switch ($ar_buf[0]) {
        case 'automount': // skip the automount entries
        case 'devfs': // skip the dev filesystem
        case 'fdesc': // skip the fdesc
        case 'procfs': // skip the proc filesystem
        case '<volfs>': // skip the vol filesystem
          continue 2;
          break;
      } 
      if (hide_mount($ar_buf[5])) {
        continue;
      }													

      $results[$j] = array();

      $results[$j]['disk'] = $ar_buf[0];
      $results[$j]['size'] = $ar_buf[1];
      $results[$j]['used'] = $ar_buf[2];
      $results[$j]['free'] = $ar_buf[3];
      $results[$j]['percent'] = $ar_buf[4];
      $results[$j]['mount'] = $ar_buf[5];
      ($fstype[$ar_buf[5]]) ? $results[$j]['fstype'] = $fstype[$ar_buf[5]] : $results[$j]['fstype'] = $fsdev[$ar_buf[0]];
      $j++;
    } 
    return $results;
  } 
  
  function distroicon () {
    $result = 'Darwin.png';
    return($result);
  }

} 

?>
