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
// HTML/XML Comment
function created_by ()
{
  global $VERSION;
  return "<!--\n\tCreated By: phpSysInfo - $VERSION\n\thttp://phpsysinfo.sourceforge.net/\n-->\n\n";
} 
// So that stupid warnings do not appear when we stats files that do not exist.
error_reporting(5);
// print out the bar graph

// $value as full percentages
// $maximim as current maximum 
// $b as scale factor
// $type as filesystem type
function create_bargraph ($value, $maximum, $b, $type = "")
{
  global $webpath;
  
  $textdir = direction();

  $imgpath = $webpath . 'templates/' . TEMPLATE_SET . '/images/';
  $maximum == 0 ? $barwidth = 0 : $barwidth = round((100  / $maximum) * $value) * $b;
  $red = 90 * $b;
  $yellow = 75 * $b;

  if (!file_exists(APP_ROOT . "/templates/" . TEMPLATE_SET . "/images/nobar_left.gif")) {
    if ($barwidth == 0) {
      return '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'bar_' . $textdir['left'] . '.gif" alt="">' 
           . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'bar_middle.gif" width="1" alt="">' 
	   . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'bar_' . $textdir['right'] . '.gif" alt="">';
    } elseif (file_exists(APP_ROOT . "/templates/" . TEMPLATE_SET . "/images/yellowbar_left.gif") && $barwidth > $yellow && $barwidth < $red) {
      return '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'yellowbar_' . $textdir['left'] . '.gif" alt="">' 
           . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'yellowbar_middle.gif" width="' . $barwidth . '" alt="">' 
	   . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'yellowbar_' . $textdir['right'] . '.gif" alt="">';
    } elseif (($barwidth < $red) || ($type == "iso9660") || ($type == "CDFS")) {
      return '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'bar_' . $textdir['left'] . '.gif" alt="">' 
           . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'bar_middle.gif" width="' . $barwidth . '" alt="">' 
	   . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'bar_' . $textdir['right'] . '.gif" alt="">';
    } else {
      return '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'redbar_' . $textdir['left'] . '.gif" alt="">' 
           . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'redbar_middle.gif" width="' . $barwidth . '" alt="">' 
	   . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'redbar_' . $textdir['right'] . '.gif" alt="">';
    }
  } else {
    if ($barwidth == 0) {
      return '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'nobar_' . $textdir['left'] . '.gif" alt="">' 
           . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'nobar_middle.gif" width="' . (100 * $b) . '" alt="">' 
	   . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'nobar_' . $textdir['right'] . '.gif" alt="">';
    } elseif (file_exists(APP_ROOT . "/templates/" . TEMPLATE_SET . "/images/yellowbar_left.gif") && $barwidth > $yellow && $barwidth < $red) {
      return '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'yellowbar_' . $textdir['left'] . '.gif" alt="">' 
           . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'yellowbar_middle.gif" width="' . $barwidth . '" alt="">' 
	   . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'nobar_middle.gif" width="' . ((100 * $b) - $barwidth) . '" alt="">' 
	   . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'nobar_' . $textdir['right'] . '.gif" alt="">';
    } elseif (($barwidth < $red) || $type == "iso9660" || ($type == "CDFS")) {
      return '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'bar_' . $textdir['left'] . '.gif" alt="">' 
           . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'bar_middle.gif" width="' . $barwidth . '" alt="">' 
	   . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'nobar_middle.gif" width="' . ((100 * $b) - $barwidth) . '" alt="">' 
	   . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'nobar_' . $textdir['right'] . '.gif" alt="">';
    } elseif ($barwidth == (100 * $b)) {
      return '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'redbar_' . $textdir['left'] . '.gif" alt="">' 
           . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'redbar_middle.gif" width="' . (100 * $b) . '" alt="">' 
	   . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'redbar_' . $textdir['right'] . '.gif" alt="">';
    } else {
      return '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'redbar_' . $textdir['left'] . '.gif" alt="">' 
           . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'redbar_middle.gif" width="' . $barwidth . '" alt="">' 
	   . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'nobar_middle.gif" width="' . ((100 * $b) - $barwidth) . '" alt="">' 
	   . '<img height="' . BAR_HEIGHT . '" src="' . $imgpath . 'nobar_' . $textdir['right'] . '.gif" alt="">';
    }
  }
} 

function direction() {
  global $text_dir;

  if(!isset($text_dir) || $text_dir == "ltr") {
    $result['direction'] = "ltr";
    $result['left'] = "left";
    $result['right'] = "right";
  } else {
    $result['direction'] = "rtl";
    $result['left'] = "right";
    $result['right'] = "left";
  }
  
  return $result;
}

// Find a system program.  Do path checking
function find_program ($program)
{
  $path = array('/bin', '/sbin', '/usr/bin', '/usr/sbin', '/usr/local/bin', '/usr/local/sbin');

  if (function_exists("is_executable")) {
    while ($this_path = current($path)) {
      if (is_executable("$this_path/$program")) {
        return "$this_path/$program";
      } 
      next($path);
    } 
  } else {
    return strpos($program, '.exe');
  } ;

  return;
} 
// Execute a system program. return a trim()'d result.
// does very crude pipe checking.  you need ' | ' for it to work
// ie $program = execute_program('netstat', '-anp | grep LIST');
// NOT $program = execute_program('netstat', '-anp|grep LIST');
function execute_program ($program, $args = '')
{
  $buffer = '';
  $program = find_program($program);

  if (!$program) {
    return;
  } 
  // see if we've gotten a |, if we have we need to do patch checking on the cmd
  if ($args) {
    $args_list = split(' ', $args);
    for ($i = 0; $i < count($args_list); $i++) {
      if ($args_list[$i] == '|') {
        $cmd = $args_list[$i + 1];
        $new_cmd = find_program($cmd);
        $args = ereg_replace("\| $cmd", "| $new_cmd", $args);
      } 
    } 
  } 
  // we've finally got a good cmd line.. execute it
  if ($fp = popen("$program $args", 'r')) {
    while (!feof($fp)) {
      $buffer .= fgets($fp, 4096);
    } 
    return trim($buffer);
  } 
} 
// A helper function, when passed a number representing KB,
// and optionally the number of decimal places required,
// it returns a formated number string, with unit identifier.
function format_bytesize ($kbytes, $dec_places = 2)
{
  global $text;
  $spacer = '&nbsp;';
  if ($kbytes > 1048576) {
    $result = sprintf('%.' . $dec_places . 'f', $kbytes / 1048576);
    $result .= $spacer . $text['gb'];
  } elseif ($kbytes > 1024) {
    $result = sprintf('%.' . $dec_places . 'f', $kbytes / 1024);
    $result .= $spacer . $text['mb'];
  } else {
    $result = sprintf('%.' . $dec_places . 'f', $kbytes);
    $result .= $spacer . $text['kb'];
  } 
  return $result;
} 

function get_gif_image_height($image)
{ 
  // gives the height of the given GIF image, by reading it's LSD (Logical Screen Discriptor)
  // by Edwin Meester aka MillenniumV3
  // Header: 3bytes 	Discription
  // 3bytes 	Version
  // LSD:		2bytes 	Logical Screen Width
  // 2bytes 	Logical Screen Height
  // 1bit 		Global Color Table Flag
  // 3bits   Color Resolution
  // 1bit		Sort Flag
  // 3bits		Size of Global Color Table
  // 1byte		Background Color Index
  // 1byte		Pixel Aspect Ratio
  // Open Image
  $fp = fopen($image, 'rb'); 
  // read Header + LSD
  $header_and_lsd = fread($fp, 13);
  fclose($fp); 
  // calc Height from Logical Screen Height bytes
  $result = ord($header_and_lsd{8}) + ord($header_and_lsd{9}) * 255;
  return $result;
} 

// Check if a string exist in the global $hide_mounts.
// Return true if this is the case.
function hide_mount($mount) {
	global $hide_mounts;
	if (isset($hide_mounts) && is_array($hide_mounts) && in_array($mount, $hide_mounts)) {
		return true;
	}
	else {
		return false;
	}
}

?>
