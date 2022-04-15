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

$path = "./include/options/session/";
require_once "./include/common/common-Func.php";
require_once "./class/centreonMsg.class.php";

$action = filter_var(
    $_GET["o"] ?? null,
    FILTER_SANITIZE_STRING
);

$selectedUserId = filter_var(
    $_GET['user'] ?? null,
    FILTER_VALIDATE_INT
);

$currentPage = filter_var(
    $_GET['p'] ?? $_POST['p'] ?? 0,
    FILTER_VALIDATE_INT
);

if ($selectedUserId) {
    $msg = new CentreonMsg();
    $msg->setTextStyle("bold");
    $msg->setTimeOut("3");

    switch ($action) {
        // logout action
        case KICK_USER:
            $stmt = $pearDB->prepare("DELETE FROM session WHERE user_id = :userId");
            $stmt->bindValue(':userId', $selectedUserId, \PDO::PARAM_INT);
            $stmt->execute();
            $msg->setText(_("User kicked"));
            break;
    }
}

/*
 * Smarty template Init
 */
$tpl = new Smarty();
$tpl = initSmartyTpl($path, $tpl);

$session_data = array();
$res = $pearDB->query(
    "SELECT session.*, contact_name, contact_admin, contact_auth_type, `contact_ldap_last_sync`
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

    // getting the current users' position in the IHM
    $session_data[$cpt]["current_page"] = $r["current_page"] . $rCP["topology_url_opt"];
    if ($rCP['topology_name'] != '') {
        $session_data[$cpt]["topology_name"] = _($rCP["topology_name"]);
    } else {
        $session_data[$cpt]["topology_name"] = $rCP["topology_name"];
    }

    if ($centreon->user->admin) {
        // adding the link to be able to kick the user
        $session_data[$cpt]["actions"] =
            "<a href='./main.php?p=" . $p . "&o=k&user=" . $r['user_id'] . "'>" .
                "<img src='./img/icons/delete.png' border='0' alt='" . _("Kick User") .
                "' title='" . _("Kick User") . "'>" .
            "</a>";

        // checking if the user account is linked to an LDAP
        if ($r['contact_auth_type'] === "ldap") {
            // adding the last synchronization time
            if ((int)$r["contact_ldap_last_sync"] > 0) {
                $session_data[$cpt]["last_sync"] = (int)$r["contact_ldap_last_sync"];
            } elseif ($r["contact_ldap_last_sync"] === '0' || $r["contact_ldap_last_sync"] === null) {
                $session_data[$cpt]["last_sync"] = "-";
            }
            $session_data[$cpt]["synchronize"] =
                "<a href='#'>" .
                    "<span onclick='submitSync(" . $currentPage . ", \"" . $r['user_id'] . "\")' 
                    title='" . _("Synchronize LDAP") . "'>" .
                        returnSvg("www/img/icons/refresh.svg", "var(--icons-fill-color)", 18, 18) .
                    "</span>" .
                "</a>";
        } else {
            // hiding the synchronization option and details
            $session_data[$cpt]["last_sync"] = "";
            $session_data[$cpt]["synchronize"] = "";
        }
        // adding the column titles
        $tpl->assign("wi_last_sync", _("Last LDAP sync"));
        $tpl->assign("wi_syncLdap", _("Refresh LDAP"));
        $tpl->assign("wi_logoutUser", _("Logout user"));
    }
}

if (isset($msg)) {
    $tpl->assign("msg", $msg);
}

$tpl->assign("session_data", $session_data);
$tpl->assign("isAdmin", $centreon->user->admin);
$tpl->assign("wi_user", _("Users"));
$tpl->assign("wi_where", _("Position"));
$tpl->assign("wi_last_req", _("Last request"));
$tpl->assign("distant_location", _("IP Address"));
$tpl->assign("adminIcon", returnSvg("www/img/icons/admin.svg", "var(--icons-fill-color)", 17, 17));
$tpl->assign("userIcon", returnSvg("www/img/icons/user.svg", "var(--icons-fill-color)", 17, 17));
$tpl->display("connected_user.ihtml");
?>

<script>
    //formatting the tags containing a class isTimestamp
    formatDateMoment();

    // ask for confirmation when requesting to resynchronize contact data from the LDAP
    function submitSync(p, contactId) {
        // msg = localized message to be displayed in the confirmation popup
        let msg = "<?= _('All this contact sessions will be closed. Are you sure you want to request a ' .
            'synchronization at the next login of this Contact ?'); ?>";
        // then executing the request and refreshing the page
        if (confirm(msg)) {
            $.ajax({
                url: './api/internal.php?object=centreon_ldap_synchro&action=requestLdapSynchro',
                type: 'POST',
                async: false,
                data: {contactId: contactId},
                success: function(data) {
                    if (data === true) {
                        window.location.href = "?p=" + p;
                    }
                }
            });
        }
    }
</script>
