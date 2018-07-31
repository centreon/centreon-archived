<?php

/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

ini_set('display_errors', 'Off');

if (!isset($_GET['id'])) {
    exit;
}

$nextRowId = htmlentities($_GET['id'], ENT_QUOTES, "UTF-8") + 1;
$nbOfInitialRows = htmlentities($_GET['nbOfInitialRows'], ENT_QUOTES, "UTF-8");
$currentId = htmlentities($_GET['id'], ENT_QUOTES, "UTF-8");

require_once realpath(dirname(__FILE__) . "/../../../../../../../config/centreon.config.php");
require_once _CENTREON_PATH_ . "/www/class/centreon.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonDB.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonXML.class.php";
require_once _CENTREON_PATH_ . "/www/class/centreonLang.class.php";
require_once _CENTREON_PATH_ . "/www/include/common/common-Func.php";

/*
 * Validate the session
 */
session_start();
session_write_close();

$centreon = $_SESSION['centreon'];

$db = new CentreonDB();
$pearDB = $db;

$centreonlang = new CentreonLang(_CENTREON_PATH_, $centreon);
$centreonlang->bindLang();
$sid = session_id();
if (isset($sid)) {
    //$sid = $_GET["sid"];
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
