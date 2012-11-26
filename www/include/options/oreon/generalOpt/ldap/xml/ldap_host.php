<?php

/*
 * Copyright 2005-2011 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
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

include_once("@CENTREON_ETC@/centreon.conf.php");
//include_once("/etc/centreon/centreon.conf.php");

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

$arId = 0;
if (isset($_GET['arId'])) {
    $arId = $_GET['arId'];
}

/*
 * start init db
 */
$xml = new CentreonXML();

$xml->startElement('root');
$xml->startElement('main');
$xml->writeElement('addImg', trim('./img/icones/16x16/navigate_plus.gif'));
$xml->writeElement('rmImg', trim('./img/icones/16x16/delete.gif'));
/**
 * Get next row id
 */
$query = "SELECT COUNT(ldap_host_id) as cnt FROM auth_ressource_host WHERE auth_ressource_id = ".$db->escape($arId);
$res = $db->query($query);
$maxHostId = 0;
if ($res->numRows()) {
    $row = $res->fetchRow();
    $maxHostId = $row['cnt'];
}
$xml->writeElement('nextRowId', $maxHostId+1);

/* The labels */
$xml->startElement('labels');
$xml->writeElement('addHost', _("Add a LDAP server"));
$xml->writeElement('hostname', _("Hostname"));
$xml->writeElement('port', _("Port"));
$xml->writeElement('ssl', _("SSL"));
$xml->writeElement('tls', _("TLS"));
$xml->writeElement('order', _("Order"));
$xml->writeElement('confirmDeletion', _('Do you really wish to remove this entry?'));
$xml->endElement(); /* End label */
$xml->endElement(); /* End main */

$query = "SELECT host_address, host_port, use_ssl, use_tls, host_order, ldap_host_id
          FROM auth_ressource_host
          WHERE auth_ressource_id = ".$db->escape($arId)."
          ORDER BY host_order";
$res = $db->query($query);

/* The hosts ldap */
while ($row = $res->fetchRow()) {
    $id = $row['ldap_host_id'];
    $xml->startElement('ldap_host');
    $xml->writeElement('order', $row['host_order']);
    $xml->writeElement('id', $id);
    $xml->startElement('inputs');
    
    /**
     * Host name
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$id][hostname]");
    $xml->writeElement('value', (isset($row['host_address']) && $row['host_address']) ? $row['host_address'] : "");
    $xml->writeElement('label', _('Host address'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_two');
    $xml->writeElement('size', 20);
    $xml->endElement();

    /**
     * Port
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$id][port]");
    $xml->writeElement('value', (isset($row['host_port']) && $row['host_port']) ? $row['host_port'] : "");
    $xml->writeElement('label', _('Port'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_one');
    $xml->writeElement('size', 4);
    $xml->endElement();

    /**
     * use ssl
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$id][use_ssl]");
    $xml->writeElement('checked', (isset($row['use_ssl']) && $row['use_ssl']) ? 1 : 0);
    $xml->writeElement('label', _('SSL'));
    $xml->writeElement('type', 'checkbox');
    $xml->writeElement('class', 'list_two');
    $xml->endElement();

    /**
     * use tls
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$id][use_tls]");
    $xml->writeElement('checked', (isset($row['use_tls']) && $row['use_tls']) ? 1 : 0);
    $xml->writeElement('label', _('TLS'));
    $xml->writeElement('type', 'checkbox');
    $xml->writeElement('class', 'list_one');
    $xml->endElement();

    /**
     * Order
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$id][order]");
    $xml->writeElement('value', $row['host_order']);
    $xml->writeElement('label', _('Order'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_two');
    $xml->writeElement('size', 4);
    $xml->endElement();

    $xml->endElement();

    $xml->endElement(); /* End ldap_host */
}
$xml->endElement(); /* End root */

header('Content-Type: text/xml');
header('Pragma: no-cache');
header('Expires: 0');
header('Cache-Control: no-cache, must-revalidate');
$xml->output();
?>