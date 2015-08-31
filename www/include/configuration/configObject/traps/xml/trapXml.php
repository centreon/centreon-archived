<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

    include_once("@CENTREON_ETC@/centreon.conf.php");

	require_once $centreon_path . "/www/class/centreonDB.class.php";
	require_once $centreon_path . "/www/class/centreonXML.class.php";
        
        /*
	 * Get session
	 */
	require_once ($centreon_path . "www/class/centreonSession.class.php");
	require_once ($centreon_path . "www/class/centreon.class.php");
	if(!isset($_SESSION['centreon'])) {
		CentreonSession::start();
	}

	if (isset($_SESSION['centreon'])) {
            $oreon = $_SESSION['centreon'];
	} else {
            exit;
	}

	/*
	 * Get language
	 */
	$locale = $oreon->user->get_lang();
	putenv("LANG=$locale");
	setlocale(LC_ALL, $locale);
	bindtextdomain("messages",  $centreon_path . "www/locale/");;
	bind_textdomain_codeset("messages", "UTF-8");
	textdomain("messages");
        

    /*
	 * start init db
	 */
	$db = new CentreonDB();

	$xml = new CentreonXML();
	$xml->startElement('root');

    $xml->startElement('main');
    $xml->writeElement('advancedLabel', _('Advanced parameters'));
    $xml->writeElement('addLabel', _('Add a new matching rule'));
    $xml->writeElement('regexpLabel', _('Regexp') . ' : ');
    $xml->writeElement('regexpVar', _('String') . ' : ');
    $xml->writeElement('statusLabel', _('Status') . ' : ');
    $xml->writeElement('orderLabel', _('Order') . ' : ');
    $xml->writeElement('addImg', trim('./img/icones/16x16/navigate_plus.gif'));
    $xml->writeElement('rmImg', trim('./img/icones/16x16/delete.gif'));
    $xml->writeElement('okLabel', _('OK'));
    $xml->writeElement('warningLabel', _('Warning'));
    $xml->writeElement('criticalLabel', _('Critical'));
    $xml->writeElement('unknownLabel', _('Unknown'));
    $xml->writeElement('confirmDeletion', _('Do you really wish to remove this entry?'));
    $xml->endElement();

    if (isset($_GET['trapId']) && $_GET['trapId']) {
        $trapId = htmlentities($_GET['trapId'], ENT_QUOTES, "UTF-8");
        $res = $db->query("SELECT * FROM traps_matching_properties WHERE trap_id = '".$trapId."' ORDER BY tmo_order ASC");
        $style = 'list_two';
        while ($row = $res->fetchRow()) {
            $style == 'list_one' ? $style = 'list_two' : $style = 'list_one';
            $xml->startElement('trap');
            $xml->writeElement('regexp', $row['tmo_regexp']);
            $xml->writeElement('var', $row['tmo_string']);
            $xml->writeElement('status', $row['tmo_status']);
            $xml->writeElement('order', $row['tmo_order']);
            $xml->writeElement('style', $style);
            $xml->endElement();
        }
    }

	$xml->endElement();
	header('Content-Type: text/xml');
	$xml->output();
?>