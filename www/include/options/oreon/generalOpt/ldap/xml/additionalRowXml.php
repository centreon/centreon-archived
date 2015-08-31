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

ini_set('display_errors', 'Off');

if (!isset($_GET['id'])) {
    exit;
}

$nextRowId = htmlentities($_GET['id'], ENT_QUOTES, "UTF-8") + 1;
$nbOfInitialRows = htmlentities($_GET['nbOfInitialRows'], ENT_QUOTES, "UTF-8");
$currentId = htmlentities($_GET['id'], ENT_QUOTES, "UTF-8");

include_once("@CENTREON_ETC@/centreon.conf.php");
require_once $centreon_path . "/www/class/centreon.class.php";
require_once $centreon_path . "/www/class/centreonDB.class.php";
require_once $centreon_path . "/www/class/centreonXML.class.php";
require_once $centreon_path . "/www/class/centreonLang.class.php";
require_once $centreon_path . "/www/include/common/common-Func.php";

/*
 * Validate the session
 */
session_start();
$centreon = $_SESSION['centreon'];

$db = new CentreonDB();
$pearDB = $db;

$centreonlang = new CentreonLang($centreon_path, $centreon);
$centreonlang->bindLang();

if (isset($_GET["sid"])) {
    $sid = $_GET["sid"];
    $res = $db->query("SELECT * FROM session WHERE session_id = '" . CentreonDB::escape($sid) . "'");
    if (!$session = $res->fetchRow()) {
        get_error('bad session id');
    }
} else {
    get_error('need session id !');
}

$currentId++;
$xml = new CentreonXML();
$xml->startElement('root');

$xml->startElement('main');
$xml->writeElement('addImg', trim('./img/icones/16x16/navigate_plus.gif'));
$xml->writeElement('rmImg', trim('./img/icones/16x16/delete.gif'));
$xml->writeElement('currentId', $currentId);
$xml->writeElement('orderValue', $nbOfInitialRows);
$xml->writeElement('nextRowId', 'additionalRow_' . $nextRowId);

/* Input fields */
$xml->startElement('inputs');


/**
 * Host name
 */
$xml->startElement('input');
$xml->writeElement('name', "ldapHosts[$currentId][hostname]");
$xml->writeElement('value', '');
$xml->writeElement('label', _('Host Name'));
$xml->writeElement('type', 'text');
$xml->writeElement('class', 'list_one');
$xml->writeElement('size', 20);
$xml->endElement();

/**
 * Port
 */
$xml->startElement('input');
$xml->writeElement('name', "ldapHosts[$currentId][port]");
$xml->writeElement('value', '389');
$xml->writeElement('label', _('Port'));
$xml->writeElement('type', 'text');
$xml->writeElement('class', 'list_two');
$xml->writeElement('size', 4);
$xml->endElement();

/**
 * use ssl
 */
$xml->startElement('input');
$xml->writeElement('name', "ldapHosts[$currentId][use_ssl]");
$xml->writeElement('label', _('SSL'));
$xml->writeElement('type', 'checkbox');
$xml->writeElement('class', 'list_one');
$xml->endElement();

/**
 * use tls
 */
$xml->startElement('input');
$xml->writeElement('name', "ldapHosts[$currentId][use_tls]");
$xml->writeElement('label', _('TLS'));
$xml->writeElement('type', 'checkbox');
$xml->writeElement('class', 'list_two');
$xml->endElement();

/**
 * Order
 */
$xml->startElement('input');
$xml->writeElement('name', "ldapHosts[$currentId][order]");
$xml->writeElement('value', $nbOfInitialRows);
$xml->writeElement('label', _('Order'));
$xml->writeElement('type', 'text');
$xml->writeElement('class', 'list_one');
$xml->writeElement('size', 4);
$xml->endElement();

$xml->endElement();

/* The labels */
$xml->startElement('labels');
$xml->writeElement('addHost', _("Add a LDAP server"));
$xml->writeElement('confirmDeletion', _('Do you really wish to remove this entry?'));
$xml->endElement(); /* End label */
$xml->endElement(); /* End main */


$xml->endElement();
header('Content-Type: text/xml');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');
$xml->output();
?>