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
// phpsysinfo release version number
$VERSION = "2.5.1";

define('APP_ROOT', dirname(__FILE__));
define('IN_PHPSYSINFO', true);

ini_set('magic_quotes_runtime', 'off');
ini_set('register_globals', 'off');

if (!file_exists(APP_ROOT . '/config.php')) {
  echo '<center><b>Error: config.php does not exist.</b></center>';
  exit;
} 

require_once(APP_ROOT . '/config.php'); // get the config file

if (!extension_loaded('xml')) {
  echo '<center><b>Error: phpsysinfo requires xml module.</b></center>';
  exit;
} 

if (!extension_loaded('pcre')) {
  echo '<center><b>Error: phpsysinfo requires pcre module.</b></center>';
  exit;
} 

// Check to see if where running inside of phpGroupWare
if (file_exists("../header.inc.php") && isset($_REQUEST['sessionid']) && $_REQUEST['sessionid'] && $_REQUEST['kp3'] && $_REQUEST['domain']) {
  define('PHPGROUPWARE', 1);
  $phpgw_info['flags'] = array('currentapp' => 'phpsysinfo-dev');
  include('../header.inc.php');
} else {
  define('PHPGROUPWARE', 0);
}

// DEFINE TEMPLATE_SET
if (isset($_POST['template'])) {
  $template = $_POST['template'];
} elseif (isset($_GET['template'])) {
  $template = $_GET['template'];
} elseif (isset($_COOKIE['template'])) {
  $template = $_COOKIE['template'];
} else {
  $template = $default_template; 
}

// check to see if we have a random
if ($template == 'random') {
  $dir = opendir(APP_ROOT . "/templates/");
  while (($file = readdir($dir)) != false) {
    if ($file != 'CVS' && $file != '.' && $file != '..') {
      $buf[] = $file;
    } 
  } 
  $template = $buf[array_rand($buf, 1)];
} 

if ($template != 'xml' && $template != 'random') {
  // figure out if the template exists
  $template = basename($template);
  if (!file_exists(APP_ROOT . "/templates/" . $template)) {
    // use default if not exists.
    $template = $default_template;
  }
}

// Store the current template name in a cookie, set expire date to 30 days later
// if template is xml then skip
if ($template != 'xml') {
  setcookie("template", $template, (time() + 60 * 60 * 24 * 30));
  $_COOKIE['template'] = $template; //update COOKIE Var
}


define('TEMPLATE_SET', $template);
// get our current language
// default to english, but this is negotiable.
if (isset($_POST['lng'])) {
  $lng = $_POST['lng'];
} elseif (isset($_GET['lng'])) {
  $lng = $_GET['lng'];
  if (!file_exists(APP_ROOT . '/includes/lang/' . $lng . '.php')) {
    $lng = 'browser';
  } 
} elseif (isset($_COOKIE['lng'])) {
  $lng = $_COOKIE['lng'];
} else {
  $lng = $default_lng;
} 
// Store the current language selection in a cookie, set expire date to 30 days later
setcookie("lng", $lng, (time() + 60 * 60 * 24 * 30));
$_COOKIE['lng'] = $lng; //update COOKIE Var

if ($lng == 'browser') {
  // see if the browser knows the right languange.
  if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $plng = split(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    if (count($plng) > 0) {
      while (list($k, $v) = each($plng)) {
        $k = split(';', $v, 1);
        $k = split('-', $k[0]);
        if (file_exists(APP_ROOT . '/includes/lang/' . $k[0] . '.php')) {
          $lng = $k[0];
          break;
        }
      }
    }
  }
}

$charset = 'utf-8';
$lng = basename($lng);
if (file_exists(APP_ROOT . '/includes/lang/' . $lng . '.php')) {
    require_once(APP_ROOT . '/includes/lang/' . $lng . '.php'); // get our language include
} else {
    echo "Sorry, we don't support this language.";
    exit;
}

// Figure out which OS where running on, and detect support
if (file_exists(APP_ROOT . '/includes/os/class.' . PHP_OS . '.inc.php')) {
  require_once(APP_ROOT . '/includes/os/class.' . PHP_OS . '.inc.php');
  $sysinfo = new sysinfo;
} else {
  echo '<center><b>Error: ' . PHP_OS . ' is not currently supported</b></center>';
  exit;
}

if (!empty($sensor_program)) {
  $sensor_program = basename($sensor_program);
  if (file_exists(APP_ROOT . '/includes/mb/class.' . $sensor_program . '.inc.php')) {
    require_once(APP_ROOT . '/includes/mb/class.' . $sensor_program . '.inc.php');
    $mbinfo = new mbinfo;
  } else {
    echo '<center><b>Error: ' . htmlspecialchars($sensor_program, ENT_QUOTES, "UTF-8") . ' is not currently supported</b></center>';
    exit;
  } 
} 

require_once(APP_ROOT . '/includes/common_functions.php'); // Set of common functions used through out the app
require_once(APP_ROOT . '/includes/xml/vitals.php');
require_once(APP_ROOT . '/includes/xml/network.php');
require_once(APP_ROOT . '/includes/xml/hardware.php');
require_once(APP_ROOT . '/includes/xml/memory.php');
require_once(APP_ROOT . '/includes/xml/filesystems.php');
require_once(APP_ROOT . '/includes/xml/mbinfo.php');
require_once(APP_ROOT . '/includes/xml/hddtemp.php');



$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
$xml .= "<!DOCTYPE phpsysinfo SYSTEM \"phpsysinfo.dtd\">\n\n";
$xml .= created_by();
$xml .= "<phpsysinfo>\n";
$xml .= "  <Generation version=\"$VERSION\" timestamp=\"" . time() . "\"/>\n";
$xml .= xml_vitals();
$xml .= xml_network();
$xml .= xml_hardware($hddtemp_devices);
$xml .= xml_memory();
$xml .= xml_filesystems();
if (!empty($sensor_program)) {
  $xml .= xml_mbtemp();
  $xml .= xml_mbfans();
  $xml .= xml_mbvoltage();
};
if (isset($hddtemp_avail)) {
  require_once(APP_ROOT . "/includes/mb/class.hddtemp.inc.php");
  $hddtemp = new hddtemp($hddtemp_devices);
  $xml .= xml_hddtemp($hddtemp);
}
$xml .= "</phpsysinfo>";

if (TEMPLATE_SET == 'xml' and !$oreon) {
  // just printout the XML and exit
  Header("Content-Type: text/xml\n\n");
  print $xml;
} else {
  $image_height = get_gif_image_height(APP_ROOT . '/templates/' . TEMPLATE_SET . '/images/bar_middle.gif');
  define('BAR_HEIGHT', $image_height);

  if (PHPGROUPWARE != 1) {
    require_once(APP_ROOT . '/includes/class.Template.inc.php'); // template library
  } 
  // fire up the template engine
  $tpl = new Template(APP_ROOT . '/templates/' . TEMPLATE_SET);
  $tpl->set_file(array('form' => 'form.tpl')); 
  // print out a box of information
  function makebox ($title, $content)
  {
    if (empty($content)) {
      return "";
    } else {
      global $webpath;
      $textdir = direction();
      $t = new Template(APP_ROOT . '/templates/' . TEMPLATE_SET);
      $t->set_file(array('box' => 'box.tpl'));
      $t->set_var('title', $title);
      $t->set_var('content', $content);
      $t->set_var('webpath', $webpath);
      $t->set_var('text_dir', $textdir['direction']);
      return $t->parse('out', 'box');
    } 
  } 
  // Fire off the XPath class
  require_once(APP_ROOT . '/includes/XPath.class.php');
  $XPath = new XPath();
  $XPath->importFromString($xml); 
  // let the page begin.
  require_once(APP_ROOT . '/includes/system_header.php');

  $tpl->set_var('title', $text['title'] . ': ' . $XPath->getData('/phpsysinfo/Vitals/Hostname') . ' (' . $XPath->getData('/phpsysinfo/Vitals/IPAddr') . ')');
  $tpl->set_var('vitals', makebox($text['vitals'], html_vitals()));
  $tpl->set_var('network', makebox($text['netusage'], html_network()));
  $tpl->set_var('hardware', makebox($text['hardware'], html_hardware()));
  $tpl->set_var('memory', makebox($text['memusage'], html_memory()));
  $tpl->set_var('filesystems', makebox($text['fs'], html_filesystems()));
  // Timo van Roermund: change the condition for showing the temperature, voltage and fans section
  $html_temp = "";
  if (!empty($sensor_program)) {
    if ($XPath->match("/phpsysinfo/MBinfo/Temperature/Item")) {
      $html_temp = html_mbtemp();
    }
    if ($XPath->match("/phpsysinfo/MBinfo/Fans/Item")) {
      $tpl->set_var('mbfans', makebox($text['fans'], html_mbfans()));
    } else {
      $tpl->set_var('mbfans', '');
    };
    if ($XPath->match("/phpsysinfo/MBinfo/Voltage/Item")) {
      $tpl->set_var('mbvoltage', makebox($text['voltage'], html_mbvoltage()));
    } else {
      $tpl->set_var('mbvoltage', '');
    };
  }
  if (isset($hddtemp_avail) && $hddtemp_avail) {
    if ($XPath->match("/phpsysinfo/HDDTemp/Item")) {
      $html_temp .= html_hddtemp();
    };
  }
  if (strlen($html_temp) > 0) {
    $tpl->set_var('mbtemp', makebox($text['temperature'], "\n<table width=\"100%\">\n" . $html_temp . "</table>\n"));
  }
  
  // parse our the template
  $tpl->pfp('out', 'form'); 
 
  // finally our print our footer
  if (PHPGROUPWARE == 1) {
    $phpgw->common->phpgw_footer();
  } else {
    require_once(APP_ROOT . '/includes/system_footer.php');
  } 
} 

?>
