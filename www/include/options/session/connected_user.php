<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 */

if (!isset($centreon)) {
    exit();
}

/**
 * Kick a logged user
 */
const KICK_USER = "k";

/**
 *  Manually plan the synchronization of the chosen user on next login
 */
const SYNC_USER = "s";

$path = "./include/options/session/";
require_once "./include/common/common-Func.php";
require_once "./class/centreonMsg.class.php";

$action = filter_var(
    $_GET["o"] ?? null,
    FILTER_SANITIZE_STRING
);

$selectedUserSid = filter_var(
    $_GET['session'] ?? null, // the sessionId of the chosen user
    FILTER_SANITIZE_STRING
);

$currentPage = filter_var(
    $_GET['p'] ?? $_POST['p'] ?? 0,
    FILTER_VALIDATE_INT
);

if ($selectedUserSid) {
    $msg = new CentreonMsg();
    $msg->setTextStyle("bold");
    $msg->setTimeOut("3");

    switch ($action) {
        case SYNC_USER:
            require_once './class/centreonLog.class.php';

            // finding chosen user's data
            $resUser = $pearDB->prepare(
                "SELECT `contact_id`, `contact_alias`, `ar_id` FROM contact 
            LEFT JOIN session ON session.user_id = contact.contact_id 
            WHERE session.session_id = :userSessionId"
            );
            try {
                $resUser->bindValue(':userSessionId', $selectedUserSid, \PDO::PARAM_STR);
                $resUser->execute();
                $currentData = $resUser->fetch();

                // checking if at least one LDAP configuration is still enable
                $ldapEnable = $pearDB->query(
                    "SELECT `value` FROM `options` WHERE `key` = 'ldap_auth_enable'"
                );
                $row = $ldapEnable->fetch();

                if ($row['value'] !== '1') {
                    // unable to plan the manual LDAP request of the contact
                    $msg->setText(_("No LDAP configuration enabled !"));
                    break;
                } else {
                    // requiring a manual synchronization at next login of the contact
                    $stmtRequiredSync = $pearDB->prepare(
                        'UPDATE contact
                    SET `ldap_required_sync` = "1"
                    WHERE contact_id = :contactId'
                    );
                    $stmtRequiredSync->bindValue(':contactId', $currentData['contact_id'], \PDO::PARAM_INT);
                    $stmtRequiredSync->execute();
                    $msg->setTextColor("green");
                    $msg->setText(_("Resync data requested."));
                    $msg->setText(_(" ")); // adding the space here, to avoid to forgot it in the translation files
                    /*
                     * as every SYNC_USER steps were successful, we need to logout the contact
                     * to synchronize the data at next login or when the centAcl CRON is executed
                     */
                }
            } catch (\PDOException $e) {
                $msg->setText(_("Error : unable to read or update the contact data in the DB"));
                break;
            }
        case KICK_USER:
            $stmt = $pearDB->prepare("DELETE FROM session WHERE session_id = :userSessionId");
            $stmt->bindValue(':userSessionId', $selectedUserSid, \PDO::PARAM_STR);
            $stmt->execute();
            $msg->setText(_("User kicked"));
    }
}

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$session_data = array();
$res = $pearDB->query(
    "SELECT session.*, contact_name, contact_admin, contact_auth_type, `ldap_last_sync`
    FROM session, contact
    WHERE contact_id = user_id ORDER BY contact_name, contact_admin"
);
for ($cpt = 0; $r = $res->fetch(); $cpt++) {
    $session_data[$cpt] = array();
    if ($cpt % 2) {
        $session_data[$cpt]["class"] = "list_one";
    } else {
        $session_data[$cpt]["class"] = "list_two";
    }
    $session_data[$cpt]["user_id"] = $r["user_id"];
    $session_data[$cpt]["user_alias"] = $r["contact_name"];
    $session_data[$cpt]["admin"] = $r["contact_admin"];
    $session_data[$cpt]["ip_address"] = $r["ip_address"];
    $session_data[$cpt]["last_reload"] = $r["last_reload"];
    $session_data[$cpt]["ldapContact"] = $r['contact_auth_type'];

    $resCP = $pearDB->prepare(
        "SELECT topology_name, topology_page, topology_url_opt FROM topology " .
        "WHERE topology_page = :topologyPage"
    );
    $resCP->bindValue(':topologyPage', $r["current_page"], \PDO::PARAM_INT);
    $resCP->execute();
    $rCP = $resCP->fetch();

    // getting the actual users position in the IHM
    $session_data[$cpt]["current_page"] = $r["current_page"] . $rCP["topology_url_opt"];
    if ($rCP['topology_name'] != '') {
        $session_data[$cpt]["topology_name"] = _($rCP["topology_name"]);
    } else {
        $session_data[$cpt]["topology_name"] = $rCP["topology_name"];
    }

    if ($centreon->user->admin) {
        // adding the link to be able to kick the user
        $session_data[$cpt]["actions"] = "<a href='./main.php?p=" . $p . "&o=k&session=" . $r['session_id'] .
            "'><img src='./img/icons/delete.png' border='0' alt='" . _("Kick User") .
            "' title='" . _("Kick User") . "'></a>";

        // checking if the user account is linked to a LDAP
        if ($r['contact_auth_type'] === "ldap") {
            // adding the last synchronization time
            if ((int)$r["ldap_last_sync"] > 0) {
                $session_data[$cpt]["last_sync"] = (int)$r["ldap_last_sync"];
            } elseif ($r["ldap_last_sync"] === '0' || $r["ldap_last_sync"] === NULL) {
                $session_data[$cpt]["last_sync"] = "-";
            }

            // adding the link to be able to synchronize user's data from the LDAP
            $session_data[$cpt]["synchronize"] = "<a href='./main.php?p=" . $p . "&o=s&session=" . $r['session_id'] .
                "'><img src='./img/icons/refresh.png' border='0' alt='" . _("Sync LDAP") .
                "' title='" . _("Sync LDAP") . "'></a>";
        } else {
            // hiding the synchronization option and details
            $session_data[$cpt]["last_sync"] = "";
            $session_data[$cpt]["synchronize"] = "";
        }
        // adding the column titles
        $tpl->assign("wi_last_sync", _("Last LDAP sync"));
        $tpl->assign("wi_syncLdap", _("Refresh LDAP"));
        $tpl->assign("wi_logoutUser", _("Logout user"));
    } else {
        // hiding the buttons
        $session_data[$cpt]["actions"] = "";
        $session_data[$cpt]["synchronize"] = "";
        // hiding the column titles
        $tpl->assign("wi_last_sync", _(""));
        $tpl->assign("wi_syncLdap", _(""));
        $tpl->assign("wi_logoutUser", _(""));
    }
}

if (isset($msg)) {
    $tpl->assign("msg", $msg);
}

$tpl->assign("session_data", $session_data);
$tpl->assign("wi_user", _("Users"));
$tpl->assign("wi_where", _("Position"));
$tpl->assign("wi_last_req", _("Last request"));

$tpl->assign("distant_location", _("IP Address"));
$tpl->display("connected_user.ihtml");
?>

<script>
    //formatting the tags containing a class isTimestamp
    formatDateMoment();
</script>