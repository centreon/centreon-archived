<?php
/*
 * Copyright 2005-2009 MERETHIS
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
 
# connect DB oreon	

	require_once ("../../../../class/Session.class.php");
	require_once ("../../../../class/Oreon.class.php");
	require_once ("../../../../class/centreonDB.class.php");

	Session::start();
	$oreon =& $_SESSION["oreon"];

	/* Connect to Centreon DB */
	
	include("../../../../centreon.conf.php");
	is_file ("../../../../lang/".$oreon->user->get_lang().".php") ? include_once ("../../../../lang/".$oreon->user->get_lang().".php") : include_once ("../../../../lang/en.php");	
	
	require_once "../../../common/common-Func.php";
	
	$pearDB = new CentreonDB();
	

# get info from database

if (isset($_GET["service_id"]) && $_GET["service_id"] != NULL){
	# get number of max last_notification service
	$max_notif = "SELECT max(esc.last_notification) nb_max_service ,min(esc.last_notification) nb_min_lastservice, min(esc.first_notification) nb_firstmin_service ,max(esc.first_notification) nb_firstmax_service ".
	"FROM escalation_service_relation ehr, escalation esc ".
	"WHERE ehr.service_service_id = ".$_GET["service_id"]." ".
	"AND ehr.escalation_esc_id = esc.esc_id ".
	"ORDER BY esc.first_notification";
	$res_max =& $pearDB->query($max_notif);
	$nb_max =& $res_max->fetchRow();
	$nb_max["nb_firstmin_service"] != NULL ? $min_notif = $nb_max["nb_firstmin_service"] : $min_notif = 1;
	$nb_max["nb_firstmax_service"] != NULL ? $max_min_notif = $nb_max["nb_firstmax_service"] : $max_min_notif = 1;
	$min_max_notif = $nb_max["nb_min_lastservice"];
	$nb_max["nb_max_service"] != NULL ? $max_notif = $nb_max["nb_max_service"] : $max_notif = 1;
	$max_notif == 0 ? $max_notif = (($max_min_notif < 42) ? 42 : $max_min_notif + 42) : $max_notif = $max_notif;
	$res_max->free();

	# retrieve all escalation correspond to service
	$cmd = "SELECT esc.esc_id, esc.esc_name, esc.first_notification, esc.last_notification, esc.notification_interval, esc.esc_comment ".
	"FROM escalation_service_relation ehr, escalation esc ".
	"WHERE ehr.service_service_id = ".$_GET["service_id"]." ".
	"AND ehr.escalation_esc_id = esc.esc_id ".
	"ORDER BY esc.first_notification desc ";
	$res_esc_svc =& $pearDB->query($cmd);
	$nb_esc = $res_esc_svc->numRows();
	$nb_esc_default = $nb_esc;
	$nb_esc != NULL ? $nb_esc = $nb_esc : $nb_esc = 1;

	# calc all row for service escalation (service escalation + contactgroup escalation)
	$cmd = "SELECT cg.cg_name, esc.esc_id, esc.esc_name, esc.first_notification, esc.last_notification, esc.notification_interval, esc.esc_comment ".
	"FROM escalation_service_relation ehr, escalation esc, contactgroup cg, escalation_contactgroup_relation ecr ".
	"WHERE ehr.service_service_id = ".$_GET["service_id"]." ".
	"AND ehr.escalation_esc_id = esc.esc_id ".
	"AND ecr.escalation_esc_id = esc.esc_id ".
	"AND ecr.contactgroup_cg_id = cg.cg_id ".
	"ORDER BY esc.first_notification desc ";
	$nb_svc =& $pearDB->query($cmd);
	$nb_esc_tot = $nb_svc->numRows();
	$nb_esc_tot != NULL ? $nb_esc_tot = $nb_esc_tot : $nb_esc_tot = 1;

	# retrieve all contactgroup correspond to service
	$cg_host = "SELECT cg.cg_name ".
	"FROM contactgroup cg, contactgroup_service_relation csr ".
	"WHERE csr.service_service_id = ".$_GET["service_id"]." ".
	"AND csr.contactgroup_cg_id = cg.cg_id";
	$res_cg_service =& $pearDB->query($cg_host);
	$max_contact_service = $res_cg_service->numRows();

	# retrieve max length contactgroup of service
	$cg_svc_length = "SELECT max(length(cg.cg_name)) max_length ".
	"FROM contactgroup cg, contactgroup_service_relation csr ".
	"WHERE csr.service_service_id = ".$_GET["service_id"]." ".
	"AND csr.contactgroup_cg_id = cg.cg_id";
	$res_svc_max =& $pearDB->query($cg_svc_length);
	$cg_contactgroup_svc_max =& $res_svc_max->fetchRow();
	$max_contact_length = $cg_contactgroup_svc_max["max_length"];

}else if (isset($_GET["host_id"]) && $_GET["host_id"] != NULL){
	# get number of max last_notification host
	$max_notif = "SELECT max(esc.last_notification) nb_max_host, min(esc.last_notification) nb_min_lasthost,min(esc.first_notification) nb_firstmin_host ,max(esc.first_notification) nb_firstmax_host ".
	"FROM escalation_host_relation ehr, escalation esc ".
	"WHERE ehr.host_host_id = ".$_GET["host_id"]." ".
	"AND ehr.escalation_esc_id = esc.esc_id ".
	"ORDER BY esc.first_notification";
	$res_max =& $pearDB->query($max_notif);
	$nb_max =& $res_max->fetchRow();
	$nb_max["nb_firstmax_host"] != NULL ? $max_min_notif = $nb_max["nb_firstmax_host"] : $max_min_notif = 1;
	$nb_max["nb_firstmin_host"] != NULL ? $min_notif = $nb_max["nb_firstmin_host"] : $min_notif = 1;
	$min_max_notif = $nb_max["nb_min_lasthost"];
	$nb_max["nb_max_host"] != NULL ? $max_notif = $nb_max["nb_max_host"] : $max_notif = 1;
	$max_notif == 0 ? $max_notif = (($max_min_notif < 42) ? 42 : $max_min_notif + 42) : $max_notif = $max_notif;
	$res_max->free();

	# retrieve all escalation correspond to host
	$cmd = "SELECT esc.esc_id, esc.esc_name, esc.first_notification, esc.last_notification, esc.notification_interval, esc.esc_comment ".
	"FROM escalation_host_relation ehr, escalation esc ".
	"WHERE ehr.host_host_id = ".$_GET["host_id"]." ".
	"AND ehr.escalation_esc_id = esc.esc_id ".
	"ORDER BY esc.first_notification desc ";
	$res_esc_host =& $pearDB->query($cmd);
	$nb_esc = $res_esc_host->numRows();
	$nb_esc_default = $nb_esc;
	$nb_esc != NULL ? $nb_esc = $nb_esc : $nb_esc = 1;
	
	# calc all row for host escalation (host escalation + contactgroup escalation)
	$cmd = "SELECT cg.cg_name, esc.esc_id, esc.esc_name, esc.first_notification, esc.last_notification, esc.notification_interval, esc.esc_comment ".
	"FROM escalation_host_relation ehr, escalation esc, contactgroup cg, escalation_contactgroup_relation ecr ".
	"WHERE ehr.host_host_id = ".$_GET["host_id"]." ".
	"AND ehr.escalation_esc_id = esc.esc_id ".
	"AND ecr.escalation_esc_id = esc.esc_id ".
	"AND ecr.contactgroup_cg_id = cg.cg_id ".
	"ORDER BY esc.first_notification desc ";
	$nb_host =& $pearDB->query($cmd);
	$nb_esc_tot = $nb_host->numRows();
	$nb_esc_tot != NULL ? $nb_esc_tot = $nb_esc_tot : $nb_esc_tot = 1;

	# retrieve all contactgroup correspond to host
	$cg_host = "SELECT cg.cg_name ".
	"FROM contactgroup cg, contactgroup_host_relation chr ".
	"WHERE chr.host_host_id = ".$_GET["host_id"]." ".
	"AND chr.contactgroup_cg_id = cg.cg_id";
	$res_cg_host =& $pearDB->query($cg_host);
	$max_contact_service = $res_cg_host->numRows();
	
	# retrieve the max length contactgroup
	$cg_max_length = "SELECT max(length(cg.cg_name)) max_length ".
	"FROM contactgroup cg, contactgroup_host_relation chr ".
	"WHERE chr.host_host_id = ".$_GET["host_id"]." ".
	"AND chr.contactgroup_cg_id = cg.cg_id";
	$res_length_contact =& $pearDB->query($cg_max_length);
	$cg_contactgroup_host_max =& $res_length_contact->fetchRow();
	$max_contact_length = $cg_contactgroup_host_max["max_length"];
}
else if (isset($_GET["hostgroup_id"]) && $_GET["hostgroup_id"] != NULL){
	# get number of max last_notification hostgroup
	$max_notif = "SELECT max(esc.last_notification) nb_max_hostgroup ,min(esc.last_notification) nb_min_lasthostgroup, min(esc.first_notification) nb_firstmin_hostgroup ,max(esc.first_notification) nb_firstmax_hostgroup ".
	"FROM escalation_hostgroup_relation ehr, escalation esc ".
	"WHERE ehr.hostgroup_hg_id = ".$_GET["hostgroup_id"]." ".
	"AND ehr.escalation_esc_id = esc.esc_id ".
	"ORDER BY esc.first_notification";
	$res_max =& $pearDB->query($max_notif);
	$nb_max =& $res_max->fetchRow();
	$nb_max["nb_firstmin_hostgroup"] != NULL ? $min_notif = $nb_max["nb_firstmin_hostgroup"] : $min_notif = 1;
	$nb_max["nb_firstmax_hostgroup"] != NULL ? $max_min_notif = $nb_max["nb_firstmax_hostgroup"] : $max_min_notif = 1;
	$min_max_notif = $nb_max["nb_min_lasthostgroup"];
	$nb_max["nb_max_hostgroup"] != NULL ? $max_notif = $nb_max["nb_max_hostgroup"] : $max_notif = 1;
	$max_notif == 0 ? $max_notif = (($max_min_notif < 42) ? 42 : $max_min_notif + 42) : $max_notif = $max_notif;
	$res_max->free();

	# retrieve all escalation correspond to hostgroup
	$cmd = "SELECT esc.esc_id, esc.esc_name, esc.first_notification, esc.last_notification, esc.notification_interval, esc.esc_comment ".
	"FROM escalation_hostgroup_relation ehr, escalation esc ".
	"WHERE ehr.hostgroup_hg_id = ".$_GET["hostgroup_id"]." ".
	"AND ehr.escalation_esc_id = esc.esc_id ".
	"ORDER BY esc.first_notification desc ";
	$res_esc_hostgroup =& $pearDB->query($cmd);
	$nb_esc = $res_esc_hostgroup->numRows();
	$nb_esc_default = $nb_esc;
	$nb_esc != NULL ? $nb_esc = $nb_esc : $nb_esc = 1;

	# calc all row for hostgroup escalation (hostgroup escalation + contactgroup escalation)
	$cmd = "SELECT cg.cg_name, esc.esc_id, esc.esc_name, esc.first_notification, esc.last_notification, esc.notification_interval, esc.esc_comment ".
	"FROM escalation_hostgroup_relation ehr, escalation esc, contactgroup cg, escalation_contactgroup_relation ecr ".
	"WHERE ehr.hostgroup_hg_id = ".$_GET["hostgroup_id"]." ".
	"AND ehr.escalation_esc_id = esc.esc_id ".
	"AND ecr.escalation_esc_id = esc.esc_id ".
	"AND ecr.contactgroup_cg_id = cg.cg_id ".
	"ORDER BY esc.first_notification desc ";
	$nb_hostgroup =& $pearDB->query($cmd);
	$nb_esc_tot = $nb_hostgroup->numRows();
	$nb_esc_tot != NULL ? $nb_esc_tot = $nb_esc_tot : $nb_esc_tot = 1;

	# retrieve all contactgroup correspond to hostgroup
	$cg_host = "SELECT cg.cg_name ".
	"FROM contactgroup cg, contactgroup_hostgroup_relation chr ".
	"WHERE chr.hostgroup_hg_id = ".$_GET["hostgroup_id"]." ".
	"AND chr.contactgroup_cg_id = cg.cg_id";
	$res_cg_hostgroup =& $pearDB->query($cg_host);
	$max_contact_service = $res_cg_hostgroup->numRows();

	# retrieve max length contactgroup of hostgroup
	$cg_svc_length = "SELECT max(length(cg.cg_name)) max_length ".
	"FROM contactgroup cg, contactgroup_hostgroup_relation chr ".
	"WHERE chr.hostgroup_hg_id = ".$_GET["hostgroup_id"]." ".
	"AND chr.contactgroup_cg_id = cg.cg_id";
	$res_svc_max =& $pearDB->query($cg_svc_length);
	$cg_contactgroup_svc_max =& $res_svc_max->fetchRow();
	$max_contact_length = $cg_contactgroup_svc_max["max_length"];
}
# init IMAGE
$largeur = ($max_notif > 50) ? 1024 : 800;
//$hauteur = ($nb_esc > 5) ? 768 : 400;
$hauteur = ($nb_esc_tot * 35) + (($max_contact_service == 0 ? $nb_esc : $max_contact_service) * 35) + 70;
$marge_left = ($max_contact_length) ? $max_contact_length * 13 : 10 * 13;
$marge_legende = 20;
$marge_bottom = 50 + $marge_legende;
$image = imagecreate($largeur,$hauteur);


# init color

$blanc = imagecolorallocate($image, 255, 255, 255);
$rouge = imagecolorallocate($image, 255, 0, 0);
$bleuclair = imagecolorallocate($image, 156, 227, 254);
$noir = imagecolorallocate($image, 0, 0, 0);
$orange_dark = imagecolorallocate($image, 112, 211, 255);
$orange = imagecolorallocate($image, 104, 188, 255);

# Cadre
ImageSetThickness ($image, 3); 
ImageRectangle ($image, 0, 0, $largeur - 1, $hauteur - 1, $noir);
ImageSetThickness ($image, 1); 

# AXE Ordonnée
ImageLine ($image, $marge_left, 20, $marge_left, $hauteur - $marge_bottom + 20, $noir);
# AXE abscisse
ImageLine ($image, $marge_left - 20, $hauteur - $marge_bottom, $largeur, $hauteur - $marge_bottom, $noir);

# LEGENDE

ImageLine ($image, $largeur / 2 - 50, $hauteur - $marge_legende + 5, $largeur / 2, $hauteur - $marge_legende + 5, $rouge);
imagestring($image, 2, $largeur / 2, $hauteur - $marge_legende, ' : Basic notification', $noir);
trace_bat($largeur / 2 + 160, $hauteur - $marge_legende, $largeur / 2 + 200, $hauteur - $marge_legende + 10, "");
imagestring($image, 2, $largeur / 2 + 205, $hauteur - $marge_legende, ' : Escalations', $noir);

function trace_bat($x1, $y1, $x2, $y2, $esc_name)
{
	global $image, $orange, $noir, $orange_dark;
	ImageFilledRectangle ($image, $x1, $y1, $x2, $y2, $orange);
	ImageFilledRectangle ($image, $x1, $y1 + 3, $x2, $y2 - 4, $orange_dark);
	ImageRectangle ($image, $x1, $y1, $x2, $y2, $noir);
	imagestring($image, 3, $x1, $y1, $esc_name, $noir);
}

	# pas graduation y
	$pas_graduation_y = ($hauteur - ($marge_bottom)) / $nb_esc;
	# pas graduation x
	$pas_graduation_x = ($largeur - ($marge_left)) / $max_notif;

//imagestring($image, 3, 700, 50, $min_max_notif, $noir);
# GRAPH SERVICES ESCALATION()
if (isset($_GET["service_id"]) && $_GET["service_id"] != NULL){
	#show contactgroup link with the service
	$pas_tmp_svc = ($max_contact_service * 20 > $pas_graduation_y ? $pas_graduation_y / ($max_contact_service > 0 ? $max_contact_service : 1) : 20);
	for ($cnt = 0, $i = 0; $contactgroup_service =& $res_cg_service->fetchRow(); $cnt++){
			($cnt == 0) ? $i += 15 : $i += $pas_tmp_svc;
			imagestring($image, 3, 10, $hauteur - $marge_bottom - $i, $contactgroup_service["cg_name"], $rouge);
			ImageFilledRectangle ($image, $marge_left, $hauteur - $marge_bottom - $i, $marge_left + ($pas_graduation_x), $hauteur - $marge_bottom - $i, $rouge);
			if ($min_max_notif != 0 || $nb_esc_default == 0){
				ImageFilledRectangle ($image, $marge_left + (($max_notif - $min_notif + 1) * $pas_graduation_x), $hauteur - $marge_bottom - $i, $largeur, $hauteur - $marge_bottom - $i, $rouge);
				ImageFilledPolygon ($image, array($largeur - 10, $hauteur - $marge_bottom - 5 - $i,$largeur - 10, $hauteur - $marge_bottom + 5 - $i,$largeur, $hauteur - $marge_bottom - $i), 3, $rouge);
			}
		}
	$res_cg_service->free();
	ImageLine ($image, $largeur - 10, $hauteur - $marge_bottom - 5, $largeur- 10, $hauteur - $marge_bottom + 5, $noir);
	imagestring($image, 2, $largeur - 10, $hauteur - $marge_bottom + 10, 'x', $noir);
	for ($i = 0, $tmp_x = 0, $flag = 0; $esc_svc_data =& $res_esc_svc->fetchRow();)
	{
		# retrieve contactgroup associated with the escalation service
		$cmd_contactgroup = "SELECT cg.cg_name ".
		"FROM contactgroup cg, escalation_contactgroup_relation ecr ".
		"WHERE ecr.escalation_esc_id = ".$esc_svc_data["esc_id"]." ".
		"AND ecr.contactgroup_cg_id = cg.cg_id";
		$res_cg =& $pearDB->query($cmd_contactgroup);
		$max_contact = $res_cg->numRows();
		$pas_tmp = ($max_contact * 20 > $pas_graduation_y ? $pas_graduation_y / ($max_contact > 0 ? $max_contact : 1) : 20);
		for ($cnt = 0; $contactgroup =& $res_cg->fetchRow(); $cnt++){#show contactgroup link with the escalation of the service
			($cnt == 0) ? $i += $pas_graduation_y / 2 : $i += $pas_tmp;
			ImageLine ($image, $marge_left, $hauteur - $marge_bottom - $i, $marge_left + 5, $hauteur - $marge_bottom - $i, $noir);
			imagestring($image, 3, 10, $hauteur - $marge_bottom - $i, $contactgroup["cg_name"], $noir);
			if ($esc_svc_data["last_notification"] == 0){
				trace_bat($marge_left+(($esc_svc_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - $i, $largeur - 10, $hauteur - $marge_bottom - $i + 10, $esc_svc_data["esc_name"]);
			}
			else{
				trace_bat($marge_left+(($esc_svc_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - $i, $marge_left + (($esc_svc_data["last_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - $i + 10,$esc_svc_data["esc_name"]);
			}
		}
		$res_cg->free();
		#graduation Axe X
		ImageLine ($image, $marge_left+(($esc_svc_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - 5, $marge_left+(($esc_svc_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom + 5, $noir);
		imagestring($image, 2, $marge_left+(($esc_svc_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom + 10, $esc_svc_data["first_notification"], $noir);
		if ($esc_svc_data["last_notification"] != 0){
			ImageLine ($image, $marge_left + (($esc_svc_data["last_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - 5, $marge_left + (($esc_svc_data["last_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom + 5, $noir);
			imagestring($image, 2, $marge_left + (($esc_svc_data["last_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom + 10, $esc_svc_data["last_notification"], $noir);
		}
	}
	$res_esc_svc->free();
}# GRAPH HOSTS ESCALATION
else if (isset($_GET["host_id"]) && $_GET["host_id"] != NULL){
	#show contactgroup link with the host
	$pas_tmp_host = ($max_contact_service * 20 > $pas_graduation_y ? $pas_graduation_y / ($max_contact_service > 0 ? $max_contact_service : 1) : 20);
	for ($cnt = 0, $i = 0; $contactgroup_host =& $res_cg_host->fetchRow(); $cnt++){
			($cnt == 0) ? $i += 15 : $i += $pas_tmp_host;
			imagestring($image, 3, 10, $hauteur - $marge_bottom - $i, $contactgroup_host["cg_name"], $rouge);
			ImageFilledRectangle ($image, $marge_left, $hauteur - $marge_bottom - $i, $marge_left + ($pas_graduation_x), $hauteur - $marge_bottom - $i, $rouge);
			if ($min_max_notif != 0 || $nb_esc_default == 0 ){
			ImageFilledRectangle ($image, $marge_left + (($max_notif - $min_notif + 1) * $pas_graduation_x), $hauteur - $marge_bottom - $i, $largeur, $hauteur - $marge_bottom - $i, $rouge);
			ImageFilledPolygon ($image, array($largeur - 10, $hauteur - $marge_bottom - 5 - $i,$largeur - 10, $hauteur - $marge_bottom + 5 - $i,$largeur, $hauteur - $marge_bottom - $i), 3, $rouge);
			}
		}
	$res_cg_host->free();
	ImageLine ($image, $largeur - 10, $hauteur - $marge_bottom - 5, $largeur- 10, $hauteur - $marge_bottom + 5, $noir);
	imagestring($image, 2, $largeur - 10, $hauteur - $marge_bottom + 10, 'x', $noir);
	for ($i = 0; $esc_host_data =& $res_esc_host->fetchRow();)
	{
		# retrieve contactgroup associated with the escalation host
		$cmd_contactgroup = "SELECT cg.cg_name ".
		"FROM contactgroup cg, escalation_contactgroup_relation ecr ".
		"WHERE ecr.escalation_esc_id = ".$esc_host_data["esc_id"]." ".
		"AND ecr.contactgroup_cg_id = cg.cg_id";
		$res_cg =& $pearDB->query($cmd_contactgroup);
		$max_contact = $res_cg->numRows();
		$pas_tmp = ($max_contact * 20 > $pas_graduation_y ? $pas_graduation_y / ($max_contact > 0 ? $max_contact : 1) : 20);
		for ($cnt = 0; $contactgroup =& $res_cg->fetchRow(); $cnt++){#show contactgroup link with the escalation of the host
			($cnt == 0) ? $i += $pas_graduation_y / 2 : $i += $pas_tmp;
			ImageLine ($image, $marge_left, $hauteur - $marge_bottom - $i, $marge_left + 5, $hauteur - $marge_bottom - $i, $noir);
			imagestring($image, 3, 10, $hauteur - $marge_bottom - $i, $contactgroup["cg_name"], $noir);
			if ($esc_host_data["last_notification"] == 0){
				trace_bat($marge_left+(($esc_host_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - $i, $largeur - 10, $hauteur - $marge_bottom - $i + 10,$esc_host_data["esc_name"]);
			}
			else{
				trace_bat($marge_left+(($esc_host_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - $i, $marge_left + (($esc_host_data["last_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - $i + 10,$esc_host_data["esc_name"]);
			}
		}
		$res_cg->free();
		#graduation Axe X
		ImageLine ($image, $marge_left+(($esc_host_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - 5, $marge_left+(($esc_host_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom + 5, $noir);
		imagestring($image, 2, $marge_left+(($esc_host_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom + 10, $esc_host_data["first_notification"], $noir);
		if ($esc_host_data["last_notification"] != 0){
			ImageLine ($image, $marge_left + (($esc_host_data["last_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - 5, $marge_left + (($esc_host_data["last_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom + 5, $noir);
			imagestring($image, 2, $marge_left + (($esc_host_data["last_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom + 10, $esc_host_data["last_notification"], $noir);
		}
	}
	$res_esc_host->free();
}
else if (isset($_GET["hostgroup_id"]) && $_GET["hostgroup_id"] != NULL){
	ImageLine ($image, $largeur - 10, $hauteur - $marge_bottom - 5, $largeur- 10, $hauteur - $marge_bottom + 5, $noir);
	imagestring($image, 2, $largeur - 10, $hauteur - $marge_bottom + 10, 'x', $noir);
	for ($i = 0, $tmp_x = 0; $esc_hostgroup_data =& $res_esc_hostgroup->fetchRow();)
	{
		# retrieve contactgroup associated with the escalation hostgroup
		$cmd_contactgroup = "SELECT cg.cg_name ".
		"FROM contactgroup cg, escalation_contactgroup_relation ecr ".
		"WHERE ecr.escalation_esc_id = ".$esc_hostgroup_data["esc_id"]." ".
		"AND ecr.contactgroup_cg_id = cg.cg_id";
		$res_cg =& $pearDB->query($cmd_contactgroup);
		$max_contact = $res_cg->numRows();
		$pas_tmp = ($max_contact * 20 > $pas_graduation_y ? $pas_graduation_y / ($max_contact > 0 ? $max_contact : 1) : 20);
		for ($cnt = 0; $contactgroup =& $res_cg->fetchRow(); $cnt++){#show contactgroup link with the escalation of the hostgroup
			($cnt == 0) ? $i += $pas_graduation_y / 2 : $i += $pas_tmp;
			ImageLine ($image, $marge_left, $hauteur - $marge_bottom - $i, $marge_left + 5, $hauteur - $marge_bottom - $i, $noir);
			imagestring($image, 3, 10, $hauteur - $marge_bottom - $i, $contactgroup["cg_name"], $noir);
			if ($esc_hostgroup_data["last_notification"] == 0){
				trace_bat($marge_left+(($esc_hostgroup_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - $i, $largeur - 10, $hauteur - $marge_bottom - $i + 10, $esc_hostgroup_data["esc_name"]);
			}
			else{
				trace_bat($marge_left+(($esc_hostgroup_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - $i, $marge_left + (($esc_hostgroup_data["last_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - $i + 10,$esc_hostgroup_data["esc_name"]);
			}
		}
		$res_cg->free();
		#graduation Axe X
		ImageLine ($image, $marge_left+(($esc_hostgroup_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - 5, $marge_left+(($esc_hostgroup_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom + 5, $noir);
		imagestring($image, 2, $marge_left+(($esc_hostgroup_data["first_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom + 10, $esc_hostgroup_data["first_notification"], $noir);
		if ($esc_hostgroup_data["last_notification"] != 0){
			ImageLine ($image, $marge_left + (($esc_hostgroup_data["last_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom - 5, $marge_left + (($esc_hostgroup_data["last_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom + 5, $noir);
			imagestring($image, 2, $marge_left + (($esc_hostgroup_data["last_notification"] - $min_notif + 1)*$pas_graduation_x), $hauteur - $marge_bottom + 10, $esc_hostgroup_data["last_notification"], $noir);
		}
	}
	$res_esc_hostgroup->free();
}
//imagecolortransparent($image, $blanc);
imagepng($image);
imagedestroy($image);
?>