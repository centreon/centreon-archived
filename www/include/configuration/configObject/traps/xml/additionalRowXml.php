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

    if (!isset($_GET['id'])) {
        exit;
    }

    $nextRowId = htmlentities($_GET['id'], ENT_QUOTES, "UTF-8") + 1;
    $nbOfInitialRows = htmlentities($_GET['nbOfInitialRows'], ENT_QUOTES, "UTF-8");
    $currentId = htmlentities($_GET['id'], ENT_QUOTES, "UTF-8");

    include_once("@CENTREON_ETC@/centreon.conf.php");
	require_once $centreon_path . "/www/class/centreonXML.class.php";

        /*
	 * Get session
	 */
	require_once ($centreon_path . "www/class/centreonSession.class.php");
	require_once ($centreon_path . "www/class/centreon.class.php");
	if (!isset($_SESSION['centreon'])) {
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
    $xml->writeElement('okLabel', _('OK'));
    $xml->writeElement('warningLabel', _('Warning'));
    $xml->writeElement('criticalLabel', _('Critical'));
    $xml->writeElement('unknownLabel', _('Unknown'));
    $xml->writeElement('nextRowId', 'additionalRow_'.$nextRowId);
    $xml->writeElement('confirmDeletion', _('Do you really wish to remove this entry?'));
    $xml->writeElement('currentId', $currentId);
    $xml->writeElement('orderValue', $nbOfInitialRows + $currentId);

	$xml->endElement();
	header('Content-Type: text/xml');
	$xml->output();
?>