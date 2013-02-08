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
class sysinfo {
  var $inifile = "distros.ini";
  var $icon = "unknown.png";
  var $distro = "unknown";

  // get the distro name and icon when create the sysinfo object
  function sysinfo() {
   $list = @parse_ini_file(APP_ROOT . "/" . $this->inifile, true);
   if (!$list) {
    return;
   }
   foreach ($list as $section => $distribution) {
    if (!isset($distribution["Files"])) {
     continue;
    } else {
     foreach (explode(";", $distribution["Files"]) as $filename) {
      if (file_exists($filename)) {
       $fd = fopen($filename, 'r');
       $buf = fgets($fd, 1024);
       fclose($fd);
       $this->icon = isset($distribution["Image"]) ? $distribution["Image"] : $this->icon;
       $this->distro = isset($distribution["Name"]) ? $distribution["Name"] . " " . trim($buf) : trim($buf);
       break 2;
      }
     }
    }
   }
  }

  // get our apache SERVER_NAME or vhost
  function vhostname () {
    if (! ($result = getenv('SERVER_NAME'))) {
      $result = 'N.A.';
    } 
    return $result;
  } 
  // get our canonical hostname
  function chostname () {
    if ($fp = fopen('/proc/sys/kernel/hostname', 'r')) {
      $result = trim(fgets($fp, 4096));
      fclose($fp);
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
    if ($fd = fopen('/proc/version', 'r')) {
      $buf = fgets($fd, 4096);
      fclose($fd);

      if (preg_match('/version (.*?) /', $buf, $ar_buf)) {
        $result = $ar_buf[1];

        if (preg_match('/SMP/', $buf)) {
          $result .= ' (SMP)';
        } 
      } else {
        $result = 'N.A.';
      } 
    } else {
      $result = 'N.A.';
    } 
    return $result;
  } 
  
  function uptime () {
    $fd = fopen('/proc/uptime', 'r');
    $ar_buf = split(' ', fgets($fd, 4096));
    fclose($fd);

    $result = trim($ar_buf[0]);

    return $result;
  } 

  function users () {
    $who = split('=', execute_program('who', '-q'));
    $result = $who[1];
    return $result;
  } 

  function loadavg ($bar = false) {
    if ($fd = fopen('/proc/loadavg', 'r')) {
      $results['avg'] = preg_split("/\s/", fgets($fd, 4096),4);
      unset($results['avg'][3]);	// don't need the extra values, only first three
      fclose($fd);
    } else {
      $results['avg'] = array('N.A.', 'N.A.', 'N.A.');
    } 
    if ($bar) {
      if ($fd = fopen('/proc/stat', 'r')) {
	fscanf($fd, "%*s %Ld %Ld %Ld %Ld", $ab, $ac, $ad, $ae);
	// Find out the CPU load
	// user + sys = load 
	// total = total
	$load = $ab + $ac + $ad;	// cpu.user + cpu.sys
	$total = $ab + $ac + $ad + $ae;	// cpu.total
	fclose($fd);

	// we need a second value, wait 1 second befor getting (< 1 second no good value will occour)
	sleep(1);
	$fd = fopen('/proc/stat', 'r');
	fscanf($fd, "%*s %Ld %Ld %Ld %Ld", $ab, $ac, $ad, $ae);
	$load2 = $ab + $ac + $ad;
	$total2 = $ab + $ac + $ad + $ae;
	$results['cpupercent'] = (100*($load2 - $load)) / ($total2 - $total);
	fclose($fd);
      }
    }
    return $results;
  } 

  function cpu_info () {
    $results = array('cpus' => 0, 'bogomips' => 0);
    $ar_buf = array();

    if ($fd = fopen('/proc/cpuinfo', 'r')) {
      while ($buf = fgets($fd, 4096)) {
       if($buf != "\n") {
        list($key, $value) = preg_split('/\s+:\s+/', trim($buf), 2); 
        // All of the tags here are highly architecture dependant.
        // the only way I could reconstruct them for machines I don't
        // have is to browse the kernel source.  So if your arch isn't
        // supported, tell me you want it written in.
        switch ($key) {
          case 'model name':
            $results['model'] = $value;
            break;
          case 'cpu MHz':
            $results['cpuspeed'] = sprintf('%.2f', $value);
            break;
          case 'cycle frequency [Hz]': // For Alpha arch - 2.2.x
            $results['cpuspeed'] = sprintf('%.2f', $value / 1000000);
            break;
          case 'clock': // For PPC arch (damn borked POS)
            $results['cpuspeed'] = sprintf('%.2f', $value);
            break;
          case 'cpu': // For PPC arch (damn borked POS)
            $results['model'] = $value;
            break;
          case 'L2 cache': // More for PPC
            $results['cache'] = $value;
            break;
          case 'revision': // For PPC arch (damn borked POS)
            $results['model'] .= ' ( rev: ' . $value . ')';
            break;
          case 'cpu model': // For Alpha arch - 2.2.x
            $results['model'] .= ' (' . $value . ')';
            break;
          case 'cache size':
            $results['cache'] = $value;
            break;
          case 'bogomips':
            $results['bogomips'] += $value;
            break;
          case 'BogoMIPS': // For alpha arch - 2.2.x
            $results['bogomips'] += $value;
            break;
          case 'BogoMips': // For sparc arch
            $results['bogomips'] += $value;
            break;
          case 'cpus detected': // For Alpha arch - 2.2.x
            $results['cpus'] += $value;
            break;
          case 'system type': // Alpha arch - 2.2.x
            $results['model'] .= ', ' . $value . ' ';
            break;
          case 'platform string': // Alpha arch - 2.2.x
            $results['model'] .= ' (' . $value . ')';
            break;
          case 'processor':
            $results['cpus'] += 1;
            break;
          case 'Cpu0ClkTck': // Linux sparc64
            $results['cpuspeed'] = sprintf('%.2f', hexdec($value) / 1000000);
            break;
          case 'Cpu0Bogo': // Linux sparc64 & sparc32
            $results['bogomips'] = $value;
            break;
          case 'ncpus probed': // Linux sparc64 & sparc32
            $results['cpus'] = $value;
            break;
        } 
       }
      } 
      fclose($fd);
    } 

    // sparc64 specific code follows
    // This adds the ability to display the cache that a CPU has
    // Originally made by Sven Blumenstein <bazik@gentoo.org> in 2004
    // Modified by Tom Weustink <freshy98@gmx.net> in 2004
    $sparclist = array('SUNW,UltraSPARC@0,0', 'SUNW,UltraSPARC-II@0,0', 'SUNW,UltraSPARC@1c,0', 'SUNW,UltraSPARC-IIi@1c,0', 'SUNW,UltraSPARC-II@1c,0');
    foreach ($sparclist as $name) {
      if (file_exists('/proc/openprom/' . $name . '/ecache-size')) {
        $fd = fopen('/proc/openprom/' . $name . '/ecache-size', 'r');
        $results['cache'] = base_convert(fgets($fd, 32), 16, 10)/1024 . ' KB';
        fclose($fd);
      }
    }
    // sparc64 specific code ends

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
    $results = array();

    if ($_results = execute_program('lspci')) {
      $lines = split("\n", $_results);
      for ($i = 0, $max = sizeof($lines); $i < $max; $i++) {
        list($addr, $name) = explode(' ', trim($lines[$i]), 2);

        if (!preg_match('/bridge/i', $name) && !preg_match('/USB/i', $name)) {
          // remove all the version strings
          $name = preg_replace('/\(.*\)/', '', $name);
          $results[] = $addr . ' ' . $name;
        } 
      } 
    } elseif ($fd = fopen('/proc/pci', 'r')) {
      while ($buf = fgets($fd, 4096)) {
        if (preg_match('/Bus/', $buf)) {
          $device = 1;
          continue;
        } 

        if ($device) {
          list($key, $value) = split(': ', $buf, 2);

          if (!preg_match('/bridge/i', $key) && !preg_match('/USB/i', $key)) {
            $results[] = preg_replace('/\([^\)]+\)\.$/', '', trim($value));
          } 
          $device = 0;
        } 
      } 
    } 
    asort($results);
    return $results;
  } 

  function ide () {
    $results = array();

    $handle = opendir('/proc/ide');

    while ($file = readdir($handle)) {
      if (preg_match('/^hd/', $file)) {
        $results[$file] = array(); 
        // Check if device is CD-ROM (CD-ROM capacity shows as 1024 GB)
        if ($fd = fopen("/proc/ide/$file/media", 'r')) {
          $results[$file]['media'] = trim(fgets($fd, 4096));
          if ($results[$file]['media'] == 'disk') {
            $results[$file]['media'] = 'Hard Disk';
          } 

          if ($results[$file]['media'] == 'cdrom') {
            $results[$file]['media'] = 'CD-ROM';
          } 
          fclose($fd);
        } 

        if ($fd = fopen("/proc/ide/$file/model", 'r')) {
          $results[$file]['model'] = trim(fgets($fd, 4096));
          if (preg_match('/WDC/', $results[$file]['model'])) {
            $results[$file]['manufacture'] = 'Western Digital';
          } elseif (preg_match('/IBM/', $results[$file]['model'])) {
            $results[$file]['manufacture'] = 'IBM';
          } elseif (preg_match('/FUJITSU/', $results[$file]['model'])) {
            $results[$file]['manufacture'] = 'Fujitsu';
          } else {
            $results[$file]['manufacture'] = 'Unknown';
          } 

          fclose($fd);
        } 
	
	if (file_exists("/proc/ide/$file/capacity"))
	    $filename = "/proc/ide/$file/capacity";
	elseif (file_exists("/sys/block/$file/size"))
	    $filename = "/sys/block/$file/size";

        if (isset($filename) && $fd = fopen("/proc/ide/$file/capacity", 'r')) {
          $results[$file]['capacity'] = trim(fgets($fd, 4096));
          if ($results[$file]['media'] == 'CD-ROM') {
            unset($results[$file]['capacity']);
          } 
          fclose($fd);
        } 
      } 
    } 
    closedir($handle);

    asort($results);
    return $results;
  } 

  function scsi () {
    $results = array();
    $dev_vendor = '';
    $dev_model = '';
    $dev_rev = '';
    $dev_type = '';
    $s = 1;
    $get_type = 0;

    if ($fd = fopen('/proc/scsi/scsi', 'r')) {
      while ($buf = fgets($fd, 4096)) {
        if (preg_match('/Vendor/', $buf)) {
          preg_match('/Vendor: (.*) Model: (.*) Rev: (.*)/i', $buf, $dev);
          list($key, $value) = split(': ', $buf, 2);
          $dev_str = $value;
          $get_type = 1;
          continue;
        } 

        if ($get_type) {
          preg_match('/Type:\s+(\S+)/i', $buf, $dev_type);
          $results[$s]['model'] = "$dev[1] $dev[2] ($dev_type[1])";
          $results[$s]['media'] = "Hard Disk";
          $s++;
          $get_type = 0;
        } 
      } 
    } 
    asort($results);
    return $results;
  } 

  function usb () {
    $results = array();
    $devnum = -1;

    if ($fd = fopen('/proc/bus/usb/devices', 'r')) {
      while ($buf = fgets($fd, 4096)) {
        if (preg_match('/^T/', $buf)) {
          $devnum += 1;
	  $results[$devnum] = "";
        } elseif (preg_match('/^S:/', $buf)) {
          list($key, $value) = split(': ', $buf, 2);
          list($key, $value2) = split('=', $value, 2);
	  if (trim($key) != "SerialNumber") {
            $results[$devnum] .= " " . trim($value2);
            $devstring = 0;
	  }
        } 
      } 
    } 
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

    if ($fd = fopen('/proc/net/dev', 'r')) {
      while ($buf = fgets($fd, 4096)) {
        if (preg_match('/:/', $buf)) {
          list($dev_name, $stats_list) = preg_split('/:/', $buf, 2);
          $stats = preg_split('/\s+/', trim($stats_list));
          $results[$dev_name] = array();

          $results[$dev_name]['rx_bytes'] = $stats[0];
          $results[$dev_name]['rx_packets'] = $stats[1];
          $results[$dev_name]['rx_errs'] = $stats[2];
          $results[$dev_name]['rx_drop'] = $stats[3];

          $results[$dev_name]['tx_bytes'] = $stats[8];
          $results[$dev_name]['tx_packets'] = $stats[9];
          $results[$dev_name]['tx_errs'] = $stats[10];
          $results[$dev_name]['tx_drop'] = $stats[11];

          $results[$dev_name]['errs'] = $stats[2] + $stats[10];
          $results[$dev_name]['drop'] = $stats[3] + $stats[11];
        } 
      } 
    } else {
      echo "'/proc/net/dev' not readable";
    }
    return $results;
  } 

  function memory () {
    if ($fd = fopen('/proc/meminfo', 'r')) {
      $results['ram'] = array();
      $results['swap'] = array();
      $results['devswap'] = array();

      while ($buf = fgets($fd, 4096)) {
        if (preg_match('/^MemTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['total'] = $ar_buf[1];
        } else if (preg_match('/^MemFree:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['t_free'] = $ar_buf[1];
        } else if (preg_match('/^Cached:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['cached'] = $ar_buf[1];
        } else if (preg_match('/^Buffers:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['ram']['buffers'] = $ar_buf[1];
        } else if (preg_match('/^SwapTotal:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['swap']['total'] = $ar_buf[1];
        } else if (preg_match('/^SwapFree:\s+(.*)\s*kB/i', $buf, $ar_buf)) {
          $results['swap']['free'] = $ar_buf[1];
        } 
      } 
      fclose($fd);

      $results['ram']['t_used'] = $results['ram']['total'] - $results['ram']['t_free'];
      $results['ram']['percent'] = round(($results['ram']['t_used'] * 100) / $results['ram']['total']);
      $results['swap']['used'] = $results['swap']['total'] - $results['swap']['free'];
      $results['swap']['percent'] = round(($results['swap']['used'] * 100) / $results['swap']['total']);
      
      // values for splitting memory usage
      if (isset($results['ram']['cached']) && isset($results['ram']['buffers'])) {
        $results['ram']['app'] = $results['ram']['t_used'] - $results['ram']['cached'] - $results['ram']['buffers'];
	$results['ram']['app_percent'] = round(($results['ram']['app'] * 100) / $results['ram']['total']);
	$results['ram']['buffers_percent'] = round(($results['ram']['buffers'] * 100) / $results['ram']['total']);
	$results['ram']['cached_percent'] = round(($results['ram']['cached'] * 100) / $results['ram']['total']);
      }

      $swaps = file ('/proc/swaps');
      for ($i = 1; $i < (sizeof($swaps)); $i++) {
        $ar_buf = preg_split('/\s+/', $swaps[$i], 6);
        $results['devswap'][$i - 1] = array();
        $results['devswap'][$i - 1]['dev'] = $ar_buf[0];
        $results['devswap'][$i - 1]['total'] = $ar_buf[2];
        $results['devswap'][$i - 1]['used'] = $ar_buf[3];
        $results['devswap'][$i - 1]['free'] = ($results['devswap'][$i - 1]['total'] - $results['devswap'][$i - 1]['used']);
        $results['devswap'][$i - 1]['percent'] = round(($ar_buf[3] * 100) / $ar_buf[2]);
      } 
    } else {
      $results['ram'] = array();
      $results['swap'] = array();
      $results['devswap'] = array();
    }
    return $results;
  } 

  function filesystems () {
    global $show_bind;
    $fstype = array();
    $fsoptions = array();

    $df = execute_program('df', '-kP');
    $mounts = split("\n", $df);

    $buffer = execute_program("mount");
    $buffer = explode("\n", $buffer);

    $j = 0;
    foreach($buffer as $line) {
      preg_match("/(.*) on (.*) type (.*) \((.*)\)/", $line, $result);
      if (count($result) == 5) {
        $dev = $result[1]; $mpoint = $result[2]; $type = $result[3]; $options = $result[4];
        $fstype[$mpoint] = $type; $fsdev[$dev] = $type; $fsoptions[$mpoint] = $options;

        foreach ($mounts as $line2) {
          if (preg_match("#^" . $result[1] . "#", $line2)) {
            $line2 = preg_replace("#^" . $result[1] . "#", "", $line2);
            $ar_buf = preg_split("/(\s+)/", $line2, 6);
            $ar_buf[0] = $result[1];

            if (hide_mount($ar_buf[5]) || $ar_buf[0] == "") {
              continue;
            }

            if ($show_bind || !stristr($fsoptions[$ar_buf[5]], "bind")) {
              $results[$j] = array();
              $results[$j]['disk'] = $ar_buf[0];
              $results[$j]['size'] = $ar_buf[1];
              $results[$j]['used'] = $ar_buf[2];
              $results[$j]['free'] = $ar_buf[3];
              $results[$j]['percent'] = round(($results[$j]['used'] * 100) / $results[$j]['size']) . '%';
              $results[$j]['mount'] = $ar_buf[5];
              ($fstype[$ar_buf[5]]) ? $results[$j]['fstype'] = $fstype[$ar_buf[5]] : $results[$j]['fstype'] = $fsdev[$ar_buf[0]];
              $results[$j]['options'] = $fsoptions[$ar_buf[5]];
              $j++;
            }
          }
	}
      }
    }
    return $results;
  } 

  function distro () {
   return $this->distro;
  }

  function distroicon () {   
   return $this->icon;
  }

} 

?>
