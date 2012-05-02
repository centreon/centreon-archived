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

    if (!isset($_GET['id'])) {
        exit;
    }

    $nextRowId = htmlentities($_GET['id'], ENT_QUOTES, "UTF-8") + 1;
    $nbOfInitialRows = htmlentities($_GET['nbOfInitialRows'], ENT_QUOTES, "UTF-8");
    $currentId = htmlentities($_GET['id'], ENT_QUOTES, "UTF-8");

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

    if (isset($_GET["sid"]) && !check_injection($_GET["sid"])){
        $sid = $_GET["sid"];
        $res = $db->query("SELECT * FROM session WHERE session_id = '".CentreonDB::escape($sid)."'");
        if (!$session = $res->fetchRow()) {
            get_error('bad session id');
        }
    } else {
        get_error('need session id !');
    }


	$xml = new CentreonXML();
	$xml->startElement('root');

	$xml->startElement('main');
	$xml->writeElement('addImg', trim('./img/icones/16x16/navigate_plus.gif'));
    $xml->writeElement('rmImg', trim('./img/icones/16x16/delete.gif'));
    $xml->writeElement('currentId', $currentId);
    $xml->writeElement('orderValue', $nbOfInitialRows + 1);
    $xml->writeElement('nextRowId', 'additionalRow_'.$nextRowId);

    /* Input fields */
    $xml->startElement('inputs');

    /**
     * Order
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][order]");
    $xml->writeElement('value', $nbOfInitialRows + 1);
    $xml->writeElement('label', _('Order'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_one');
    $xml->writeElement('size', 4);
    $xml->endElement();

    /**
     * Template
     */
    $xml->startElement('input');
    $xml->writeElement('label', _('Apply template'));
    $xml->writeElement('type', 'select');
    $xml->writeElement('class', 'list_two');
    $xml->writeElement('onChange', 'applyTemplate(this.value, '.$currentId.');');
    $xml->startElement('options');
    $xml->startElement('option');
    $xml->writeElement('value', '');
    $xml->writeElement('selected', 1);
    $xml->endElement();
    $xml->startElement('option');
    $xml->writeElement('value', 'Posix');
    $xml->endElement();
    $xml->startElement('option');
    $xml->writeElement('value', 'Active Directory');
    $xml->endElement();
    $xml->endElement();
    $xml->endElement();

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
     * bind user
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][bind_user]");
    $xml->writeElement('label', _('Bind user'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_one');
    $xml->writeElement('size', 20);
    $xml->endElement();

    /**
     * bind password
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_bindpass]");
    $xml->writeElement('label', _('Bind password'));
    $xml->writeElement('type', 'password');
    $xml->writeElement('class', 'list_two');
    $xml->writeElement('size', 20);
    $xml->endElement();

    /**
     * ldap protocol version
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_version_protocol]");
    $xml->writeElement('label', _('Protocol version'));
    $xml->writeElement('type', 'select');
    $xml->writeElement('class', 'list_one');
    $xml->startElement('options');
    $xml->startElement('option');
    $xml->writeElement('value', 2);
    $xml->endElement();
    $xml->startElement('option');
    $xml->writeElement('value', 3);
    $xml->writeElement('selected', 1);
    $xml->endElement();
    $xml->endElement();
    $xml->endElement();

    /**
     * search user base dn
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_user_basedn]");
    $xml->writeElement('label', _('Search user base DN'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_two');
    $xml->writeElement('size', 20);
    $xml->endElement();

    /**
     * search group base DN
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_group_basedn]");
    $xml->writeElement('label', _('Search group base DN'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_one');
    $xml->writeElement('size', 20);
    $xml->endElement();

    /**
     * user filter
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_user_filter]");
    $xml->writeElement('label', _('User filter'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_two');
    $xml->writeElement('size', 20);
    $xml->endElement();

    /**
     * login attribute
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_user_uid_attr]");
    $xml->writeElement('label', _('Login attribute'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_one');
    $xml->writeElement('size', 20);
    $xml->endElement();

    /**
     * user group attribute
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_user_group]");
    $xml->writeElement('label', _('User group attribute'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_two');
    $xml->writeElement('size', 20);
    $xml->endElement();

    /**
     * User displayname attribute
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_user_name]");
    $xml->writeElement('label', _('User displayname attribute'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_one');
    $xml->writeElement('size', 20);
    $xml->endElement();

    /**
     * User firstname attribute
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_user_firstname]");
    $xml->writeElement('label', _('User firstname attribute'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_two');
    $xml->writeElement('size', 20);
    $xml->endElement();


    /**
     * User lastname attribute
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_user_lastname]");
    $xml->writeElement('label', _('User lastname attribute'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_one');
    $xml->writeElement('size', 20);
    $xml->endElement();

    /**
     * User email attribute
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_user_email]");
    $xml->writeElement('label', _('User email attribute'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_two');
    $xml->writeElement('size', 20);
    $xml->endElement();

    /**
     * User pager attribute
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_user_pager]");
    $xml->writeElement('label', _('User pager attribute'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_one');
    $xml->writeElement('size', 20);
    $xml->endElement();

    /**
     * Group filter
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_group_filter]");
    $xml->writeElement('label', _('Group filter'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_two');
    $xml->writeElement('size', 20);
    $xml->endElement();


    /**
     * Group attribute
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_group_gid_attr]");
    $xml->writeElement('label', _('Group attribute'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_one');
    $xml->writeElement('size', 20);
    $xml->endElement();

    /**
     * Group member attribute
     */
    $xml->startElement('input');
    $xml->writeElement('name', "ldapHosts[$currentId][ldap_group_member]");
    $xml->writeElement('label', _('Group member attribute'));
    $xml->writeElement('type', 'text');
    $xml->writeElement('class', 'list_two');
    $xml->writeElement('size', 20);
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