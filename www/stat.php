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

if (!isset($centreon))
	exit;

// our version number
$VERSION="2.1";

// reassign HTTP variables (incase register_globals is off)
if (!empty($HTTP_GET_VARS)) while(list($name, $value) = each($HTTP_GET_VARS)) $$name = $value;
if (!empty($HTTP_POST_VARS)) while(list($name, $value) = each($HTTP_POST_VARS)) $$name = $value;

// Check to see if where running inside of phpGroupWare
if (isset($sessionid) && $sessionid && $kp3 && $domain) {
    define('PHPGROUPWARE', 1);
    $phpgw_info['flags'] = array(
        'currentapp' => 'phpsysinfo-dev'
    );
    include('../header.inc.php');
} else {
    define('PHPGROUPWARE', 0);
}

define('APP_ROOT', dirname(__FILE__));

// check to see if we have a random template first
if (isset($template) && $template == 'random') {
    $dir = opendir('sysinfo/templates/');
    while (($file = readdir($dir))!=false) {
        if ($file != 'CVS' && $file != '.' && $file != '..') {
            $buf[] = $file;
        }
    }
    $template = $buf[array_rand($buf, 1)];
    $random = True;
}

    $template = 'classic';

define('TEMPLATE_SET', $template);

// get our current language
// default to english, but this is negotiable.
if (!(isset($lng) && file_exists('./sysinfo/includes/lang/' . $centreon->user->get_lang() . '.php'))) {
    $lng = 'en';
    // see if the browser knows the right languange.
    if(isset($HTTP_ACCEPT_LANGUAGE)) {
        $plng = preg_split(',', $HTTP_ACCEPT_LANGUAGE);
        if(count($plng) > 0) {
            while(list($k,$v) = each($plng)) {
                $k = preg_split(';', $v, 1);
                $k = preg_split('-', $k[0]);
                if(file_exists('./sysinfo/includes/lang/' . $k[0] . '.php')) {
                    $lng = $k[0];
                    break;
                }
            }
        }
    }
}

require('./sysinfo/includes/lang/' . $lng . '.php');   // get our language include

// Figure out which OS where running on, and detect support
if (file_exists(dirname(__FILE__) . '/sysinfo/includes/os/class.' . PHP_OS . '.inc.php')) {
    require('./sysinfo/includes/os/class.' . PHP_OS . '.inc.php');
    $sysinfo = new sysinfo;
} else {
    echo '<center><b>Error: ' . PHP_OS . ' is not currently supported</b></center>';
    exit;
}

require('./sysinfo/includes/common_functions.php'); // Set of common functions used through out the app
require('./sysinfo/includes/xml/vitals.php');
require('./sysinfo/includes/xml/network.php');
require('./sysinfo/includes/xml/hardware.php');
require('./sysinfo/includes/xml/memory.php');
require('./sysinfo/includes/xml/filesystems.php');

$xml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n";
$xml .= "<!DOCTYPE phpsysinfo SYSTEM \"phpsysinfo.dtd\">\n\n";
$xml .= created_by();
$xml .= "<phpsysinfo>\n";
$xml .= "  <Generation version=\"$VERSION\" timestamp=\"" . time() . "\"/>\n";
$xml .= xml_vitals();
$xml .= xml_network();
$xml .= xml_hardware();
$xml .= xml_memory();
$xml .= xml_filesystems();
$xml .= "</phpsysinfo>";


if ($template == 'xml') {
    // just printout the XML and exit
    print $xml;
} else {
    // If they have GD complied into PHP, find out the height of the image to make this cleaner
    if (function_exists('getimagesize') && $template != 'xml') {
        $image_prop = getimagesize(APP_ROOT . '/sysinfo/templates/' . TEMPLATE_SET . '/images/bar_middle.gif');
        define('BAR_HEIGHT', $image_prop[1]);
        unset($image_prop);
    } else {
        // Until they complie GD into PHP, this could look ugly
        define('BAR_HEIGHT', 16);
    }

    // Store the current template name in a cookie, set expire date to one month later
    // Store 'random' if we want a random template
    if ($random) {
        setcookie("template", 'random', (time() + 2592000));
    } else {
        setcookie("template", $template, (time() + 2592000));
    }

    if (PHPGROUPWARE != 1) {
        require('./sysinfo/includes/class.Template.inc.php');  // template library
    }

    // fire up the template engine
    $tpl = new Template(dirname(__FILE__) . '/sysinfo/templates/' . TEMPLATE_SET);
    $tpl->set_file(array(
        'form' => 'form.tpl'
    ));

    // print out a box of information
    function makebox ($title, $content) {
        $t = new Template(dirname(__FILE__) . '/sysinfo/templates/' . TEMPLATE_SET);

        $t->set_file(array(
            'box'  => 'box.tpl'
        ));

        $t->set_var('title', $title);
        $t->set_var('content', $content);

        return $t->parse('out', 'box');
    }

    // Fire off the XPath class
    require('./sysinfo/includes/XPath.class.php');
    $XPath = new XPath();
    $XPath->importFromString($xml);

    // let the page begin.
    require('./sysinfo/includes/system_header.php');

    $tpl->set_var('title', $text['title'] . ': ' . $XPath->getData('/phpsysinfo/Vitals/Hostname') . ' (' . $XPath->getData('/phpsysinfo/Vitals/IPAddr') . ')');

    $tpl->set_var('vitals', makebox($text['vitals'], html_vitals(), '100%'));
    $tpl->set_var('network', makebox($text['netusage'], html_network(), '100%'));
    $tpl->set_var('hardware', makebox($text['hardware'], html_hardware(), '100%'));
    $tpl->set_var('memory', makebox($text['memusage'], html_memory(), '100%'));
    $tpl->set_var('filesystems', makebox($text['fs'], html_filesystems(), '100%'));

    // parse our the template
    $tpl->pparse('out', 'form');

    // finally our print our footer
    if (PHPGROUPWARE == 1) {
        $phpgw->common->phpgw_footer();
    } else {
        require('./sysinfo/includes/system_footer.php');
    }
}

?>
