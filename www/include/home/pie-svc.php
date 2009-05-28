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
 
	require_once ("../../class/Session.class.php");
	require_once ("../../class/Oreon.class.php");
	
	Session::start();
	$oreon =& $_SESSION["oreon"];
	$locale = $oreon->user->get_lang();
	putenv("LANG=$locale");
	setlocale(LC_ALL, $locale);
	bindtextdomain("messages", $centreon_path . "/www/locale/");
	bind_textdomain_codeset("messages", "UTF-8");
	textdomain("messages");
	$mlang = $oreon->user->get_lang();
	
	include_once "@CENTREON_ETC@/centreon.conf.php";	
	include_once $centreon_path . "www/class/centreonDB.class.php";
		
	$pearDB = new CentreonDB();
	
	include_once $centreon_path . "www/include/common/common-Func.php";

	$ndo_base_prefix = getNDOPrefix();
			
	$pearDBndo = new CentreonDB("ndo");

	/*
	 *  calcul stat for resume
	 */
	$statistic = array(0 => "OK", 1 => "WARNING", 2 => "CRITICAL", 3 => "UNKNOWN", 4 => "PENDING");
	
    /*
	 * LCA
	 */
	$sid = $_GET['sid'];
	$res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '".$sid."'");
	$user =& $res1->fetchRow();
	$user_id = $user["user_id"];

	global $is_admin;
	
	$is_admin =  $oreon->user->admin;
	$grouplistStr = $oreon->user->access->getAccessGroupsString();
			
	/* 
	 * Get Service NDO status 
	 */
	if (!$is_admin)
		$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
				" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no, centreon_acl " .
				" WHERE no.object_id = nss.service_object_id".				
				" AND no.name1 NOT LIKE '_Module_%' ".
				" AND no.name1 = centreon_acl.host_name ".
				" AND no.name2 = centreon_acl.service_description " .
				" AND centreon_acl.group_id IN (".$grouplistStr.") ".
				" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";
	else
		$rq2 = 	" SELECT count(nss.current_state), nss.current_state" .
				" FROM ".$ndo_base_prefix."servicestatus nss, ".$ndo_base_prefix."objects no" .
				" WHERE no.object_id = nss.service_object_id".
				" AND no.name1 NOT LIKE '_Module_%' ".
				" AND no.is_active = 1 GROUP BY nss.current_state ORDER by nss.current_state";			
	$DBRESULT_NDO2 =& $pearDBndo->query($rq2);
	
	$svc_stat = array(0=>0, 1=>0, 2=>0, 3=>0, 4=>0);
	$counter = 0;
	while ($ndo =& $DBRESULT_NDO2->fetchRow()){
		$data[] = $ndo["count(nss.current_state)"];
		$legend[] = $statistic[$ndo["current_state"]];
		$color[] = $oreon->optGen["color_".strtolower($statistic[$ndo["current_state"]])];
		$counter += $ndo["count(nss.current_state)"];
	}
	$DBRESULT_NDO2->free();
	
	/*
	 *  create the dataset
	 */
	
	foreach ($data as $key => $value)
		$data[$key] = round($value / $counter * 100, 2);
	
	include_once($centreon_path.'/www/lib/ofc-library/open-flash-chart.php' );
	$g = new graph();
	$g->bg_colour = '#F3F6F6';
	//
	// PIE chart, 60% alpha
	//
	$g->pie(60,'#505050','#000000');
	//
	// pass in two arrays, one of data, the other data labels
	//
	
	$g->pie_values($data, $legend);
	//
	// Colours for each slice, in this case some of the colours
	// will be re-used (3 colurs for 5 slices means the last two
	// slices will have colours colour[0] and colour[1]):
	//
	$g->set_tool_tip( '#key# : #val# %' );
	
	$g->pie_slice_colours($color);

	$g->set_tool_tip( '#val#%' );
	$g->title( _(' Services '), '{font-size:18px; color: #424242}' );
	echo $g->render();
?>