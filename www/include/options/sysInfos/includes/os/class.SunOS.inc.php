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

echo "<center><b>Note: The SunOS version of phpSysInfo is work in progress, some things currently don't work";

class sysinfo {
  // Extract kernel values via kstat() interface
  function kstat ($key) {
    $m = execute_program('kstat', "-p d $key");
    list($key, $value) = split("\t", trim($m), 2);
    return $value;
  } 

  function vhostname () {
    if (! ($result = getenv('SERVER_NAME'))) {
      $result = 'N.A.';
    } 
    return $result;
  } 
  // get our canonical hostname
  function chostname () {
    if ($result = execute_program('uname', '-n')) {
      $result = gethostbyaddr(gethostbyname($result));
    } else {
      $result = 'N.A.';
    } 
    return $result;
  } 
  // get the IP address of our canonical hostname
  function ip_addr () {
    if (!($result = getenv('SERVER_ADDR'))) {
      $result = gethostbyname($this->chostname());
    } 
    return $result;
  } 

  function kernel () {
    $os = execute_program('uname', '-s');
    $version = execute_program('uname', '-r');
    return $os . ' ' . $version;
  } 

  function uptime () {
    $result = time() - $this->kstat('unix:0:system_misc:boot_time');

    return $result;
  } 

  function users () {
    $who = split('=', execute_program('who', '-q'));
    $result = $who[1];
    return $result;
  } 

  function loadavg ($bar = false) {
    $load1 = $this->kstat('unix:0:system_misc:avenrun_1min');
    $load5 = $this->kstat('unix:0:system_misc:avenrun_5min');
    $load15 = $this->kstat('unix:0:system_misc:avenrun_15min');
    $results['avg'] = array($load1, $load5, $load15);
    return $results;
  } 

  function cpu_info () {
    $results = array();
    $ar_buf = array();

    $results['model'] = execute_program('uname', '-i');
    $results['cpuspeed'] = $this->kstat('cpu_info:0:cpu_info0:clock_MHz');
    $results['cache'] = $this->kstat('cpu_info:0:cpu_info0:cpu_type');
    $results['bogomips'] = 1;
    $results['cpus'] = $this->kstat('unix:0:system_misc:ncpus');

    $keys = array_keys($results);
    $keys2be = array('model', 'cpuspeed', 'cache', 'bogomips', 'cpus');

    while ($ar_buf = each($keys2be)) {
      if (! in_array($ar_buf[1], $keys)) {
        $results[$ar_buf[1]] = 'N.A.';
      } 
    } 
    return $results;
  } 

  function pci () {
    // FIXME
    $results = array();
    return $results;
  } 

  function ide () {
    // FIXME
    $results = array();
    return $results;
  } 

  function scsi () {
    // FIXME
    $results = array();
    return $results;
  } 

  function usb () {
    // FIXME
    $results = array();
    return $results;
  } 

  function sbus () {
    $results = array();
    $_results[0] = "";
    // TODO. Nothing here yet. Move along.
    $results = $_results;
    return $results;
  }

  function network () {
    $results = array();

    $netstat = execute_program('netstat', '-ni | awk \'(NF ==10){print;}\'');
    $lines = split("\n", $netstat);
    $results = array();
    for ($i = 0, $max = sizeof($lines); $i < $max; $i++) {
      $ar_buf = preg_split("/\s+/", $lines[$i]);
      if ((!empty($ar_buf[0])) && ($ar_buf[0] != 'Name')) {
        $results[$ar_buf[0]] = array();

        $results[$ar_buf[0]]['rx_bytes'] = 0;
        $results[$ar_buf[0]]['rx_packets'] = $ar_buf[4];
        $results[$ar_buf[0]]['rx_errs'] = $ar_buf[5];
        $results[$ar_buf[0]]['rx_drop'] = 0;

        $results[$ar_buf[0]]['tx_bytes'] = 0;
        $results[$ar_buf[0]]['tx_packets'] = $ar_buf[6];
        $results[$ar_buf[0]]['tx_errs'] = $ar_buf[7];
        $results[$ar_buf[0]]['tx_drop'] = 0;

        $results[$ar_buf[0]]['errs'] = $ar_buf[5] + $ar_buf[
        7];
        $results[$ar_buf[0]]['drop'] = 0;

        preg_match('/^(\D+)(\d+)$/', $ar_buf[0], $intf);
        $prefix = $intf[1] . ':' . $intf[2] . ':' . $intf[1] . $intf[2] . ':';
        $cnt = $this->kstat($prefix . 'drop');

        if ($cnt > 0) {
          $results[$ar_buf[0]]['rx_drop'] = $cnt;
        } 
        $cnt = $this->kstat($prefix . 'obytes64');

        if ($cnt > 0) {
          $results[$ar_buf[0]]['tx_bytes'] = $cnt;
        } 
        $cnt = $this->kstat($prefix . 'rbytes64');

        if ($cnt > 0) {
          $results[$ar_buf[0]]['rx_bytes'] = $cnt;
        }
      } 
    } 
    return $results;
  } 

  function memory () {
    $results['devswap'] = array();

    $results['ram'] = array();

    $pagesize = $this->kstat('unix:0:seg_cache:slab_size');
    $results['ram']['total'] = $this->kstat('unix:0:system_pages:pagestotal') * $pagesize;
    $results['ram']['used'] = $this->kstat('unix:0:system_pages:pageslocked') * $pagesize;
    $results['ram']['free'] = $this->kstat('unix:0:system_pages:pagesfree') * $pagesize;
    $results['ram']['shared'] = 0;
    $results['ram']['buffers'] = 0;
    $results['ram']['cached'] = 0;

    $results['ram']['t_used'] = $results['ram']['used'] - $results['ram']['cached'] - $results['ram']['buffers'];
    $results['ram']['t_free'] = $results['ram']['total'] - $results['ram']['t_used'];
    $results['ram']['percent'] = round(($results['ram']['used'] * 100) / $results['ram']['total']);

    $results['swap'] = array();
    $results['swap']['total'] = $this->kstat('unix:0:vminfo:swap_avail') / 1024;
    $results['swap']['used'] = $this->kstat('unix:0:vminfo:swap_alloc') / 1024;
    $results['swap']['free'] = $this->kstat('unix:0:vminfo:swap_free') / 1024;
    $results['swap']['percent'] = round(($ar_buf[1] * 100) / $ar_buf[0]);
    $results['swap']['percent'] = round(($results['swap']['used'] * 100) / $results['swap']['total']);
    return $results;
  } 

  function filesystems () {
    $df = execute_program('df', '-k');
    $mounts = split("\n", $df);

    $dftypes = execute_program('df', '-n');
    $mounttypes = split("\n", $dftypes);

    for ($i = 1, $j = 0, $max = sizeof($mounts); $i < $max; $i++) {
      $ar_buf = preg_split('/\s+/', $mounts[$i], 6);
      $ty_buf = split(':', $mounttypes[$i-1], 2);

      if (hide_mount($ar_buf[5])) {
        continue;
      }

      $results[$j] = array();

      $results[$j]['disk'] = $ar_buf[0];
      $results[$j]['size'] = $ar_buf[1];
      $results[$j]['used'] = $ar_buf[2];
      $results[$j]['free'] = $ar_buf[3];
      $results[$j]['percent'] = round(($results[$j]['used'] * 100) / $results[$j]['size']) . '%';
      $results[$j]['mount'] = $ar_buf[5];
      $results[$j]['fstype'] = $ty_buf[1];
      $j++;
    } 
    return $results;
  } 
  
  function distro () {
    $result = 'SunOS';  	
    return($result);
  }

  function distroicon () {
    $result = 'unknown.png';
    return($result);
  }
} 

?>
