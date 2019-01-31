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

# connect DB oreon

require_once("../../../../class/centreonSession.class.php");
require_once("../../../../class/centreon.class.php");
require_once("../../../../class/centreonDB.class.php");

CentreonSession::start();
$oreon = $_SESSION["oreon"];

/* Connect to Centreon DB */

include("../../../../centreon.conf.php");
is_file("../../../../lang/" . $oreon->user->get_lang() . ".php")
    ? include_once("../../../../lang/" . $oreon->user->get_lang() . ".php")
    : include_once("../../../../lang/en.php");

require_once "../../../common/common-Func.php";

$pearDB = new CentreonDB();

# get info from database

if (isset($_GET["service_id"]) && $_GET["service_id"] != null) {
    # get number of max last_notification service
    $maxNotifQuery = "SELECT max(esc.last_notification) nb_max_service, "
        . "min(esc.last_notification) nb_min_lastservice, "
        . "min(esc.first_notification) nb_firstmin_service, "
        . "max(esc.first_notification) nb_firstmax_service "
        . "FROM escalation_service_relation ehr, escalation esc "
        . "WHERE ehr.service_service_id = :serviceId "
        . "AND ehr.escalation_esc_id = esc.esc_id "
        . "ORDER BY esc.first_notification";

    $stmt = $pearDB->prepare($maxNotifQuery);
    $stmt->bindValue(':serviceId', $_GET["service_id"], \PDO::PARAM_INT);
    $nbMax = $stmt->fetch();

    $nbMax["nb_firstmin_service"] != null
        ? $minNotif = $nbMax["nb_firstmin_service"]
        : $minNotif = 1;
    $nbMax["nb_firstmax_service"] != null
        ? $maxMinNotif = $nbMax["nb_firstmax_service"]
        : $maxMinNotif = 1;
    $minMaxNotif = $nbMax["nb_min_lastservice"];
    $nbMax["nb_max_service"] != null
        ? $maxNotif = $nbMax["nb_max_service"]
        : $maxNotif = 1;
    if ($maxNotif == 0) {
        $maxNotif = (($maxMinNotif < 42) ? 42 : $maxMinNotif + 42);
    }
    $stmt->closeCursor();

    # retrieve all escalation correspond to service
    $cmd = "SELECT esc.esc_id, esc.esc_name, esc.first_notification, " .
        "esc.last_notification, esc.notification_interval, esc.esc_comment " .
        "FROM escalation_service_relation ehr, escalation esc " .
        "WHERE ehr.service_service_id = :serviceId " .
        "AND ehr.escalation_esc_id = esc.esc_id " .
        "ORDER BY esc.first_notification desc ";
    $stmtEscSvc = $pearDB->prepare($cmd);
    $stmtEscSvc->bindValue(':serviceId', $_GET["service_id"], \PDO::PARAM_INT);
    $nbEscDefault = $nbEsc = $stmtEscSvc->rowCount();

    if (is_null($nbEsc)) {
        $nbEsc = 1;
    }

    # calc all row for service escalation (service escalation + contactgroup escalation)
    $cmd = "SELECT cg.cg_name, esc.esc_id, esc.esc_name, " .
        "esc.first_notification, esc.last_notification, " .
        "esc.notification_interval, esc.esc_comment " .
        "FROM escalation_service_relation ehr, escalation esc, " .
        "contactgroup cg, escalation_contactgroup_relation ecr " .
        "WHERE ehr.service_service_id = :serviceId " .
        "AND ehr.escalation_esc_id = esc.esc_id " .
        "AND ecr.escalation_esc_id = esc.esc_id " .
        "AND ecr.contactgroup_cg_id = cg.cg_id " .
        "ORDER BY esc.first_notification desc ";
    $stmt = $pearDB->prepare($cmd);
    $stmt->bindValue(':serviceId', $_GET["service_id"], \PDO::PARAM_INT);
    $nbEscTot = $stmt->rowCount();
    if (is_null($nbEscTot)) {
        $nbEscTot = 1;
    }

    # retrieve all contactgroup correspond to service
    $cg_host = "SELECT cg.cg_name " .
        "FROM contactgroup cg, contactgroup_service_relation csr " .
        "WHERE csr.service_service_id = :serviceId " .
        "AND csr.contactgroup_cg_id = cg.cg_id";
    $stmtCgService = $pearDB->prepare($cg_host);
    $stmtCgService->bindValue(':serviceId', $_GET["service_id"], \PDO::PARAM_INT);
    $maxContactService = $stmtCgService->rowCount();

    # retrieve max length contactgroup of service
    $cgSvcLength = "SELECT max(length(cg.cg_name)) max_length " .
        "FROM contactgroup cg, contactgroup_service_relation csr " .
        "WHERE csr.service_service_id = :serviceId " .
        "AND csr.contactgroup_cg_id = cg.cg_id";
    $stmt = $pearDB->prepare($cgSvcLength);
    $stmt->bindValue(':serviceId', $_GET["service_id"], \PDO::PARAM_INT);
    $cgContactGroupSvcMax = $stmt->fetch();
    $maxContactLength = $cgContactGroupSvcMax["max_length"];
} elseif (isset($_GET["host_id"]) && $_GET["host_id"] != null) {
    # get number of max last_notification host
    $maxNotifQuery = "SELECT max(esc.last_notification) nb_max_host, " .
        "min(esc.last_notification) nb_min_lasthost, " .
        "min(esc.first_notification) nb_firstmin_host ," .
        "max(esc.first_notification) nb_firstmax_host " .
        "FROM escalation_host_relation ehr, escalation esc " .
        "WHERE ehr.host_host_id = :hostId " .
        "AND ehr.escalation_esc_id = esc.esc_id " .
        "ORDER BY esc.first_notification";
    $stmt = $pearDB->prepare($maxNotifQuery);
    $stmt->bindValue(':hostId', $_GET["host_id"], \PDO::PARAM_INT);
    $nbMax = $stmt->fetch();

    $nbMax["nb_firstmax_host"] != null
        ? $maxMinNotif = $nbMax["nb_firstmax_host"]
        : $maxMinNotif = 1;
    $nbMax["nb_firstmin_host"] != null
        ? $minNotif = $nbMax["nb_firstmin_host"]
        : $minNotif = 1;
    $minMaxNotif = $nbMax["nb_min_lasthost"];
    $nbMax["nb_max_host"] != null
        ? $maxNotif = $nbMax["nb_max_host"]
        : $maxNotif = 1;
    if ($maxNotif == 0) {
        $maxNotif = (($maxMinNotif < 42) ? 42 : $maxMinNotif + 42);
    }
    $stmt->closeCursor();

    # retrieve all escalation correspond to host
    $cmd = "SELECT esc.esc_id, esc.esc_name, esc.first_notification, " .
        "esc.last_notification, esc.notification_interval, esc.esc_comment " .
        "FROM escalation_host_relation ehr, escalation esc " .
        "WHERE ehr.host_host_id = :hostId " .
        "AND ehr.escalation_esc_id = esc.esc_id " .
        "ORDER BY esc.first_notification desc ";
    $stmtEscHost = $pearDB->prepare($cmd);
    $stmtEscHost->bindValue(':hostId', $_GET["host_id"], \PDO::PARAM_INT);
    $nbEsc = $stmtEscHost->rowCount();
    $nbEscDefault = $nbEsc;
    if (is_null($nbEsc)) {
        $nbEsc = 1;
    }

    # calc all row for host escalation (host escalation + contactgroup escalation)
    $cmd = "SELECT cg.cg_name, esc.esc_id, esc.esc_name, " .
        "esc.first_notification, esc.last_notification, " .
        "esc.notification_interval, esc.esc_comment " .
        "FROM escalation_host_relation ehr, escalation esc, " .
        "contactgroup cg, escalation_contactgroup_relation ecr " .
        "WHERE ehr.host_host_id = :hostId " .
        "AND ehr.escalation_esc_id = esc.esc_id " .
        "AND ecr.escalation_esc_id = esc.esc_id " .
        "AND ecr.contactgroup_cg_id = cg.cg_id " .
        "ORDER BY esc.first_notification desc ";
    $stmtCgHost = $pearDB->prepare($cmd);
    $stmtCgHost->bindValue(':hostId', $_GET["host_id"], \PDO::PARAM_INT);
    $nbEscTot = $stmtCgHost->rowCount();
    if (is_null($nbEscTot)) {
        $nbEscTot = 1;
    }

    # retrieve all contactgroup correspond to host
    $cgHost = "SELECT cg.cg_name " .
        "FROM contactgroup cg, contactgroup_host_relation chr " .
        "WHERE chr.host_host_id = :hostId " .
        "AND chr.contactgroup_cg_id = cg.cg_id";
    $stmt = $pearDB->prepare($cgHost);
    $stmt->bindValue(':hostId', $_GET["host_id"], \PDO::PARAM_INT);
    $maxContactService = $stmt->rowCount();

    # retrieve the max length contactgroup
    $cgMaxLength = "SELECT max(length(cg.cg_name)) max_length " .
        "FROM contactgroup cg, contactgroup_host_relation chr " .
        "WHERE chr.host_host_id = :hostId " .
        "AND chr.contactgroup_cg_id = cg.cg_id";
    $stmt = $pearDB->prepare($cgMaxLength);
    $stmt->bindValue(':hostId', $_GET["host_id"], \PDO::PARAM_INT);
    $cgContactGroupHostMax = $stmt->fetch();
    $maxContactLength = $cgContactGroupHostMax["max_length"];
} elseif (isset($_GET["hostgroup_id"]) && $_GET["hostgroup_id"] != null) {
    # get number of max last_notification hostgroup
    $maxNotifQuery = "SELECT max(esc.last_notification) nb_max_hostgroup, " .
        "min(esc.last_notification) nb_min_lasthostgroup, " .
        "min(esc.first_notification) nb_firstmin_hostgroup, " .
        "max(esc.first_notification) nb_firstmax_hostgroup " .
        "FROM escalation_hostgroup_relation ehr, escalation esc " .
        "WHERE ehr.hostgroup_hg_id = :hgId " .
        "AND ehr.escalation_esc_id = esc.esc_id " .
        "ORDER BY esc.first_notification";
    $stmt = $pearDB->prepare($maxNotifQuery);
    $stmt->bindValue(':hgId', $_GET["hostgroup_id"], \PDO::PARAM_INT);
    $nbMax = $stmt->fetch();
    $nbMax["nb_firstmin_hostgroup"] != null
        ? $minNotif = $nbMax["nb_firstmin_hostgroup"]
        : $minNotif = 1;
    $nbMax["nb_firstmax_hostgroup"] != null
        ? $maxMinNotif = $nbMax["nb_firstmax_hostgroup"]
        : $maxMinNotif = 1;
    $minMaxNotif = $nbMax["nb_min_lasthostgroup"];
    $nbMax["nb_max_hostgroup"] != null
        ? $maxNotif = $nbMax["nb_max_hostgroup"]
        : $maxNotif = 1;
    if ($maxNotif == 0) {
        $maxNotif = (($maxMinNotif < 42) ? 42 : $maxMinNotif + 42);
    }
    $stmt->closeCursor();

    # retrieve all escalation correspond to hostgroup
    $cmd = "SELECT esc.esc_id, esc.esc_name, esc.first_notification, " .
        "esc.last_notification, esc.notification_interval, esc.esc_comment " .
        "FROM escalation_hostgroup_relation ehr, escalation esc " .
        "WHERE ehr.hostgroup_hg_id = :hgId " .
        "AND ehr.escalation_esc_id = esc.esc_id " .
        "ORDER BY esc.first_notification desc ";

    $stmtEscHostGroup = $pearDB->prepare($cmd);
    $stmtEscHostGroup->bindValue(':hgId', $_GET["hostgroup_id"], \PDO::PARAM_INT);
    $nbEscDefault = $nbEsc = $stmtEscHostGroup->rowCount();
    if (is_null($nbEsc)) {
        $nbEsc = 1;
    }

    # calc all row for hostgroup escalation (hostgroup escalation + contactgroup escalation)
    $cmd = "SELECT cg.cg_name, esc.esc_id, esc.esc_name, " .
        "esc.first_notification, esc.last_notification, " .
        "esc.notification_interval, esc.esc_comment " .
        "FROM escalation_hostgroup_relation ehr, escalation esc, " .
        "contactgroup cg, escalation_contactgroup_relation ecr " .
        "WHERE ehr.hostgroup_hg_id = :hgId " .
        "AND ehr.escalation_esc_id = esc.esc_id " .
        "AND ecr.escalation_esc_id = esc.esc_id " .
        "AND ecr.contactgroup_cg_id = cg.cg_id " .
        "ORDER BY esc.first_notification desc ";
    $stmt = $pearDB->prepare($cmd);
    $stmt->bindValue(':hgId', $_GET["hostgroup_id"], \PDO::PARAM_INT);
    $nbEscTot = $stmt->rowCount();
    if (is_null($nbEscTot)) {
        $nbEscTot = 1;
    }

    # retrieve all contactgroup correspond to hostgroup
    $cgHost = "SELECT cg.cg_name " .
        "FROM contactgroup cg, contactgroup_hostgroup_relation chr " .
        "WHERE chr.hostgroup_hg_id = :hgId " .
        "AND chr.contactgroup_cg_id = cg.cg_id";
    $stmt = $pearDB->prepare($cgHost);
    $stmt->bindValue(':hgId', $_GET["hostgroup_id"], \PDO::PARAM_INT);
    $maxContactService = $stmt->rowCount();

    # retrieve max length contactgroup of hostgroup
    $cgSvcLength = "SELECT max(length(cg.cg_name)) max_length " .
        "FROM contactgroup cg, contactgroup_hostgroup_relation chr " .
        "WHERE chr.hostgroup_hg_id = :hgId " .
        "AND chr.contactgroup_cg_id = cg.cg_id";
    $stmt = $pearDB->prepare($cgSvcLength);
    $stmt->bindValue(':hgId', $_GET["hostgroup_id"], \PDO::PARAM_INT);
    $cgContactGroupSvcMax = $stmt->fetchRow();
    $maxContactLength = $cgContactGroupSvcMax["max_length"];
}
# init IMAGE
$largeur = ($maxNotif > 50) ? 1024 : 800;
//$hauteur = ($nbEsc > 5) ? 768 : 400;

$hauteurData = ($maxContactService == 0) ? $nbEsc : $maxContactService;
$hauteur = ($nbEscTot * 35) + (($hauteurData) * 35) + 70;

$margeLeft = ($maxContactLength) ? $maxContactLength * 13 : 10 * 13;
$margeLegend = 20;
$margeBottom = 50 + $margeLegend;
$image = imagecreate($largeur, $hauteur);


# init color

$blanc = imagecolorallocate($image, 255, 255, 255);
$rouge = imagecolorallocate($image, 255, 0, 0);
$bleuclair = imagecolorallocate($image, 156, 227, 254);
$noir = imagecolorallocate($image, 0, 0, 0);
$orange_dark = imagecolorallocate($image, 112, 211, 255);
$orange = imagecolorallocate($image, 104, 188, 255);

# Cadre
ImageSetThickness($image, 3);
ImageRectangle($image, 0, 0, $largeur - 1, $hauteur - 1, $noir);
ImageSetThickness($image, 1);

# AXE Ordonnée
ImageLine($image, $margeLeft, 20, $margeLeft, $hauteur - $margeBottom + 20, $noir);
# AXE abscisse
ImageLine(
    $image,
    $margeLeft - 20,
    $hauteur - $margeBottom,
    $largeur,
    $hauteur - $margeBottom,
    $noir
);

# LEGENDE

ImageLine(
    $image,
    $largeur / 2 - 50,
    $hauteur - $margeLegend + 5,
    $largeur / 2,
    $hauteur - $margeLegend + 5,
    $rouge
);
imagestring(
    $image,
    2,
    $largeur / 2,
    $hauteur - $margeLegend,
    ' : Basic notification',
    $noir
);
trace_bat(
    $largeur / 2 + 160,
    $hauteur - $margeLegend,
    $largeur / 2 + 200,
    $hauteur - $margeLegend + 10,
    ""
);
imagestring(
    $image,
    2,
    $largeur / 2 + 205,
    $hauteur - $margeLegend,
    ' : Escalations',
    $noir
);

function trace_bat($x1, $y1, $x2, $y2, $escName)
{
    global $image, $orange, $noir, $orange_dark;
    ImageFilledRectangle($image, $x1, $y1, $x2, $y2, $orange);
    ImageFilledRectangle($image, $x1, $y1 + 3, $x2, $y2 - 4, $orange_dark);
    ImageRectangle($image, $x1, $y1, $x2, $y2, $noir);
    imagestring($image, 3, $x1, $y1, $escName, $noir);
}

# pas graduation y
$pasGraduationY = ($hauteur - ($margeBottom)) / $nbEsc;
# pas graduation x
$pasGraduationX = ($largeur - ($margeLeft)) / $maxNotif;

//imagestring($image, 3, 700, 50, $minMaxNotif, $noir);
# GRAPH SERVICES ESCALATION()
if (isset($_GET["service_id"]) && $_GET["service_id"] != null) {
    #show contactgroup link with the service
    $pasTmpSvc = (($maxContactService * 20) > $pasGraduationY)
        ? $pasGraduationY / ($maxContactService > 0 ? $maxContactService : 1)
        : 20;
    for ($cnt = 0, $i = 0; $contactGroupService = $stmtCgService->fetch(); $cnt++) {
        ($cnt == 0) ? $i += 15 : $i += $pasTmpSvc;
        imagestring(
            $image,
            3,
            10,
            $hauteur - $margeBottom - $i,
            $contactGroupService["cg_name"],
            $rouge
        );
        ImageFilledRectangle(
            $image,
            $margeLeft,
            $hauteur - $margeBottom - $i,
            $margeLeft + ($pasGraduationX),
            $hauteur - $margeBottom - $i,
            $rouge
        );
        if ($minMaxNotif != 0 || $nbEscDefault == 0) {
            ImageFilledRectangle(
                $image,
                $margeLeft + (($maxNotif - $minNotif + 1) * $pasGraduationX),
                $hauteur - $margeBottom - $i,
                $largeur,
                $hauteur - $margeBottom - $i,
                $rouge
            );
            ImageFilledPolygon(
                $image,
                array(
                    $largeur - 10,
                    $hauteur - $margeBottom - 5 - $i,
                    $largeur - 10,
                    $hauteur - $margeBottom + 5 - $i,
                    $largeur,
                    $hauteur - $margeBottom - $i
                ),
                3,
                $rouge
            );
        }
    }
    $stmtCgService->closeCursor();
    ImageLine(
        $image,
        $largeur - 10,
        $hauteur - $margeBottom - 5,
        $largeur - 10,
        $hauteur - $margeBottom + 5,
        $noir
    );
    imagestring($image, 2, $largeur - 10, $hauteur - $margeBottom + 10, 'x', $noir);
    for ($i = 0, $tmpX = 0, $flag = 0; $esc_svc_data = $stmtEscSvc->fetch();) {
        # retrieve contactgroup associated with the escalation service
        $cmdContactGroup = "SELECT cg.cg_name " .
            "FROM contactgroup cg, escalation_contactgroup_relation ecr " .
            "WHERE ecr.escalation_esc_id = :escalationId " .
            "AND ecr.contactgroup_cg_id = cg.cg_id";
        $stmtCg = $pearDB->prepare($cmd);
        $stmtCg->bindValue(':escalationId', $esc_svc_data["esc_id"], \PDO::PARAM_INT);
        $maxContact = $stmtCg->rowCount();
        $pasTmp = (($maxContact * 20) > $pasGraduationY)
            ? $pasGraduationY / ($maxContact > 0 ? $maxContact : 1)
            : 20;
        for ($cnt = 0; $contactgroup = $stmtCg->fetch(); $cnt++) {
            #show contactgroup link with the escalation of the service
            ($cnt == 0) ? $i += $pasGraduationY / 2 : $i += $pasTmp;
            ImageLine(
                $image,
                $margeLeft,
                $hauteur - $margeBottom - $i,
                $margeLeft + 5,
                $hauteur - $margeBottom - $i,
                $noir
            );
            imagestring($image, 3, 10, $hauteur - $margeBottom - $i, $contactgroup["cg_name"], $noir);
            if ($esc_svc_data["last_notification"] == 0) {
                trace_bat(
                    $margeLeft + (($esc_svc_data["first_notification"] - $minNotif + 1) * $pasGraduationX),
                    $hauteur - $margeBottom - $i,
                    $largeur - 10,
                    $hauteur - $margeBottom - $i + 10,
                    $esc_svc_data["esc_name"]
                );
            } else {
                trace_bat(
                    $margeLeft + (($esc_svc_data["first_notification"] - $minNotif + 1) * $pasGraduationX),
                    $hauteur - $margeBottom - $i,
                    $margeLeft + (($esc_svc_data["last_notification"] - $minNotif + 1) * $pasGraduationX),
                    $hauteur - $margeBottom - $i + 10,
                    $esc_svc_data["esc_name"]
                );
            }
        }
        $stmtCg->closeCursor();
        #graduation Axe X
        ImageLine(
            $image,
            $margeLeft + (($esc_svc_data["first_notification"] - $minNotif + 1) * $pasGraduationX),
            $hauteur - $margeBottom - 5,
            $margeLeft + (($esc_svc_data["first_notification"] - $minNotif + 1) * $pasGraduationX),
            $hauteur - $margeBottom + 5,
            $noir
        );
        imagestring(
            $image,
            2,
            $margeLeft + (($esc_svc_data["first_notification"] - $minNotif + 1) * $pasGraduationX),
            $hauteur - $margeBottom + 10,
            $esc_svc_data["first_notification"],
            $noir
        );
        if ($esc_svc_data["last_notification"] != 0) {
            ImageLine(
                $image,
                $margeLeft + (($esc_svc_data["last_notification"] - $minNotif + 1) * $pasGraduationX),
                $hauteur - $margeBottom - 5,
                $margeLeft + (($esc_svc_data["last_notification"] - $minNotif + 1) * $pasGraduationX),
                $hauteur - $margeBottom + 5,
                $noir
            );
            imagestring(
                $image,
                2,
                $margeLeft + (($esc_svc_data["last_notification"] - $minNotif + 1) * $pasGraduationX),
                $hauteur - $margeBottom + 10,
                $esc_svc_data["last_notification"],
                $noir
            );
        }
    }
    $stmtEscSvc->closeCursor();
# GRAPH HOSTS ESCALATION
} elseif (isset($_GET["host_id"]) && $_GET["host_id"] != null) {
    #show contactgroup link with the host
    $pasTmpHost = (($maxContactService * 20) > $pasGraduationY)
        ? $pasGraduationY / ($maxContactService > 0 ? $maxContactService : 1)
        : 20;
    for ($cnt = 0, $i = 0; $contactGroupHost = $stmtCgHost->fetch(); $cnt++) {
        ($cnt == 0) ? $i += 15 : $i += $pasTmpHost;
        imagestring($image, 3, 10, $hauteur - $margeBottom - $i, $contactGroupHost["cg_name"], $rouge);
        ImageFilledRectangle(
            $image,
            $margeLeft,
            $hauteur - $margeBottom - $i,
            $margeLeft + ($pasGraduationX),
            $hauteur - $margeBottom - $i,
            $rouge
        );
        if ($minMaxNotif != 0 || $nbEscDefault == 0) {
            ImageFilledRectangle(
                $image,
                $margeLeft + (($maxNotif - $minNotif + 1) * $pasGraduationX),
                $hauteur - $margeBottom - $i,
                $largeur,
                $hauteur - $margeBottom - $i,
                $rouge
            );
            ImageFilledPolygon(
                $image,
                array(
                    $largeur - 10,
                    $hauteur - $margeBottom - 5 - $i,
                    $largeur - 10,
                    $hauteur - $margeBottom + 5 - $i,
                    $largeur,
                    $hauteur - $margeBottom - $i
                ),
                3,
                $rouge
            );
        }
    }
    $stmtCgHost->closeCursor();
    ImageLine(
        $image,
        $largeur - 10,
        $hauteur - $margeBottom - 5,
        $largeur - 10,
        $hauteur - $margeBottom + 5,
        $noir
    );
    imagestring($image, 2, $largeur - 10, $hauteur - $margeBottom + 10, 'x', $noir);
    for ($i = 0; $escHostData = $stmtEscHost->fetch();) {
        # retrieve contactgroup associated with the escalation host
        $cmdContactgroup = "SELECT cg.cg_name " .
            "FROM contactgroup cg, escalation_contactgroup_relation ecr " .
            "WHERE ecr.escalation_esc_id = escalationId " .
            "AND ecr.contactgroup_cg_id = cg.cg_id";

        $stmtCg = $pearDB->prepare($cmdContactgroup);
        $stmtCg->bindValue(':escalationId', $escHostData["esc_id"], \PDO::PARAM_INT);
        $maxContact = $stmtCg->rowCount();
        $pasTmp = (($maxContact * 20) > $pasGraduationY)
            ? $pasGraduationY / ($maxContact > 0 ? $maxContact : 1)
            : 20;
        for ($cnt = 0; $contactgroup = $stmtCg->fetch(); $cnt++) {
            #show contactgroup link with the escalation of the host
            ($cnt == 0) ? $i += $pasGraduationY / 2 : $i += $pasTmp;
            ImageLine(
                $image,
                $margeLeft,
                $hauteur - $margeBottom - $i,
                $margeLeft + 5,
                $hauteur - $margeBottom - $i,
                $noir
            );
            imagestring($image, 3, 10, $hauteur - $margeBottom - $i, $contactgroup["cg_name"], $noir);
            if ($escHostData["last_notification"] == 0) {
                trace_bat(
                    $margeLeft + (($escHostData["first_notification"] - $minNotif + 1) * $pasGraduationX),
                    $hauteur - $margeBottom - $i,
                    $largeur - 10,
                    $hauteur - $margeBottom - $i + 10,
                    $escHostData["esc_name"]
                );
            } else {
                trace_bat(
                    $margeLeft + (($escHostData["first_notification"] - $minNotif + 1) * $pasGraduationX),
                    $hauteur - $margeBottom - $i,
                    $margeLeft + (($escHostData["last_notification"] - $minNotif + 1) * $pasGraduationX),
                    $hauteur - $margeBottom - $i + 10,
                    $escHostData["esc_name"]
                );
            }
        }
        $stmtCg->closeCursor();
        #graduation Axe X
        ImageLine(
            $image,
            $margeLeft + (($escHostData["first_notification"] - $minNotif + 1) * $pasGraduationX),
            $hauteur - $margeBottom - 5,
            $margeLeft + (($escHostData["first_notification"] - $minNotif + 1) * $pasGraduationX),
            $hauteur - $margeBottom + 5,
            $noir
        );
        imagestring(
            $image,
            2,
            $margeLeft + (($escHostData["first_notification"] - $minNotif + 1) * $pasGraduationX),
            $hauteur - $margeBottom + 10,
            $escHostData["first_notification"],
            $noir
        );
        if ($escHostData["last_notification"] != 0) {
            ImageLine(
                $image,
                $margeLeft + (($escHostData["last_notification"] - $minNotif + 1) * $pasGraduationX),
                $hauteur - $margeBottom - 5,
                $margeLeft + (($escHostData["last_notification"] - $minNotif + 1) * $pasGraduationX),
                $hauteur - $margeBottom + 5,
                $noir
            );
            imagestring(
                $image,
                2,
                $margeLeft + (($escHostData["last_notification"] - $minNotif + 1) * $pasGraduationX),
                $hauteur - $margeBottom + 10,
                $escHostData["last_notification"],
                $noir
            );
        }
    }
    $stmtEscHost->closeCursor();
} elseif (isset($_GET["hostgroup_id"]) && $_GET["hostgroup_id"] != null) {
    ImageLine(
        $image,
        $largeur - 10,
        $hauteur - $margeBottom - 5,
        $largeur - 10,
        $hauteur - $margeBottom + 5,
        $noir
    );
    imagestring($image, 2, $largeur - 10, $hauteur - $margeBottom + 10, 'x', $noir);
    for ($i = 0, $tmpX = 0; $escHostgroupData = $stmtEscHostGroup->fetch();) {
        # retrieve contactgroup associated with the escalation hostgroup
        $cmdContactgroup = "SELECT cg.cg_name " .
            "FROM contactgroup cg, escalation_contactgroup_relation ecr " .
            "WHERE ecr.escalation_esc_id = :escalationId " .
            "AND ecr.contactgroup_cg_id = cg.cg_id";

        $stmtCg = $pearDB->prepare($cmdContactgroup);
        $stmtCg->bindValue(':escalationId', $escHostData["esc_id"], \PDO::PARAM_INT);
        $maxContact = $stmtCg->rowCount();
        $pasTmp = (($maxContact * 20) > $pasGraduationY)
            ? $pasGraduationY / ($maxContact > 0 ? $maxContact : 1)
            : 20;
        for ($cnt = 0; $contactgroup = $stmtCg->fetchRow(); $cnt++) {
            #show contactgroup link with the escalation of the hostgroup
            ($cnt == 0) ? $i += $pasGraduationY / 2 : $i += $pasTmp;
            ImageLine(
                $image,
                $margeLeft,
                $hauteur - $margeBottom - $i,
                $margeLeft + 5,
                $hauteur - $margeBottom - $i,
                $noir
            );
            imagestring(
                $image,
                3,
                10,
                $hauteur - $margeBottom - $i,
                $contactgroup["cg_name"],
                $noir
            );
            if ($escHostgroupData["last_notification"] == 0) {
                trace_bat(
                    $margeLeft + (($escHostgroupData["first_notification"] - $minNotif + 1) * $pasGraduationX),
                    $hauteur - $margeBottom - $i,
                    $largeur - 10,
                    $hauteur - $margeBottom - $i + 10,
                    $escHostgroupData["esc_name"]
                );
            } else {
                trace_bat(
                    $margeLeft + (($escHostgroupData["first_notification"] - $minNotif + 1) * $pasGraduationX),
                    $hauteur - $margeBottom - $i,
                    $margeLeft + (($escHostgroupData["last_notification"] - $minNotif + 1) * $pasGraduationX),
                    $hauteur - $margeBottom - $i + 10,
                    $escHostgroupData["esc_name"]
                );
            }
        }
        $stmtCg->closeCursor();
        #graduation Axe X
        ImageLine(
            $image,
            $margeLeft + (($escHostgroupData["first_notification"] - $minNotif + 1) * $pasGraduationX),
            $hauteur - $margeBottom - 5,
            $margeLeft + (($escHostgroupData["first_notification"] - $minNotif + 1) * $pasGraduationX),
            $hauteur - $margeBottom + 5,
            $noir
        );
        imagestring(
            $image,
            2,
            $margeLeft + (($escHostgroupData["first_notification"] - $minNotif + 1) * $pasGraduationX),
            $hauteur - $margeBottom + 10,
            $escHostgroupData["first_notification"],
            $noir
        );
        if ($escHostgroupData["last_notification"] != 0) {
            ImageLine(
                $image,
                $margeLeft + (($escHostgroupData["last_notification"] - $minNotif + 1) * $pasGraduationX),
                $hauteur - $margeBottom - 5,
                $margeLeft + (($escHostgroupData["last_notification"] - $minNotif + 1) * $pasGraduationX),
                $hauteur - $margeBottom + 5,
                $noir
            );
            imagestring(
                $image,
                2,
                $margeLeft + (($escHostgroupData["last_notification"] - $minNotif + 1) * $pasGraduationX),
                $hauteur - $margeBottom + 10,
                $escHostgroupData["last_notification"],
                $noir
            );
        }
    }
    $stmtEscHostGroup->closeCursor();
}
//imagecolortransparent($image, $blanc);
imagepng($image);
imagedestroy($image);
