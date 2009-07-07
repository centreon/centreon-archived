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

	$oreonPath = '/usr/local/centreon/';

	require_once("DB.php");
	require_once("@CENTREON_ETC@/centreon.conf.php");
		
	/* Connect to oreon DB */
	$dsn = array('phptype'  => 'mysql',
			     'username' => $conf_centreon['user'],
			     'password' => $conf_centreon['password'],
			     'hostspec' => $conf_centreon['hostCentreon'],
			     'database' => $conf_centreon['db'],);
	$options = array('debug'=> 2, 'portability' => DB_PORTABILITY_ALL ^ DB_PORTABILITY_LOWERCASE,);	

	$pearDB =& DB::connect($dsn, $options);
	
	$pearDB->setFetchMode(DB_FETCHMODE_ASSOC);

	include_once('/usr/local/centreon/www/lib/ofc-library/open-flash-chart.php' );	
	include_once($oreonPath . "www/include/common/common-Func.php");

	$ndo_base_prefix = getNDOPrefix();
		
	include_once($oreonPath . "www/class/centreonDB.class.php");

	## calcul stat for resume
	$statistic = array(0 => "UP", 1 => "", 2 => "CRITICAL", 3 => "UNKNOWN", 4 => "PENDING");
	
	$hg = array();
	$strnameY = "";
	$percentmax = 0;
	$bar_blue = new bar_3d( 75, '#125CEC' );
	$bar_blue->key( 'host UP (%)', 10 );
	
	$bar_red = new bar_3d( 75, '#EC5C12' );
	$bar_red->key( 'host Down (%)', 10 );
	

        /*
         * LCA
         */

        $sid = session_id();

        $res1 =& $pearDB->query("SELECT user_id FROM session WHERE session_id = '$sid'");
        $user = $res1->fetchRow();
        $user_id = $user["user_id"];

        $res2 =& $pearDB->query("SELECT contact_admin FROM contact WHERE contact_id = '".$user_id."'");
        $admin = $res2->fetchrow();

        global $is_admin;

        $is_admin = 0;
        $is_admin = $admin["contact_admin"];

        if (!$is_admin){
                $_POST["sid"] = $_GET["sid"];
        }

	if ($is_admin){
	$DBRESULT =& $pearDB->query("SELECT * FROM hostgroup");
	
	while($hg = $DBRESULT->fetchRow()){
		$counterTotal = 0;
		$counterUP = 0;
		$counterDown = 0;
		$DBRESULT2 =& $pearDB->query("SELECT host_name FROM host, hostgroup_relation WHERE  hostgroup_relation.hostgroup_hg_id = '".$hg["hg_id"]."' AND hostgroup_relation.host_host_id = host.host_id");
		while($h = $DBRESULT2->fetchRow()){
			$DBRESULT3 =& $pearDBndo->query("SELECT current_state FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."hosts WHERE ".$ndo_base_prefix."hoststatus.host_object_id = ".$ndo_base_prefix."hosts.host_object_id AND ".$ndo_base_prefix."hosts.display_name = '".$h["host_name"]."'");
			while($stt = $DBRESULT3->fetchRow()){
				if ($stt["current_state"] == 1)
					$counterDown++;
				if ($stt["current_state"] == 0)
					$counterUP++;
				$counterTotal++;
			}
		}
		if ($counterTotal){
			$percentU = $counterUP / $counterTotal * 100;
			$percentD = $counterDown / $counterTotal * 100;
			$hostgroupU[$hg["hg_name"]] = $percentU;
			$bar_blue->data[] = $percentU;
			$bar_red->data[] = $percentD;
			if ($percentU > $percentmax)
				$percentmax = $percentU;
			if ($strnameY)
				$strnameY .= ", ";
			$strnameY .= $hg["hg_name"];
		}
	}
	}
	else
	{
        $tmp1 =& $pearDB->query("SELECT acl_group_id FROM acl_group_contacts_relations WHERE acl_group_contacts_relations.contact_contact_id = '$user_id'");
        $tmp2 = $tmp1->fetchRow();
        $acl_group_id = $tmp2["acl_group_id"];

	$tmp1 =& $pearDB->query("SELECT acl_res_id FROM acl_res_group_relations WHERE acl_res_group_relations.acl_group_id = '$acl_group_id'");
	$tmp2 = $tmp1->fetchRow();
	$acl_res_id = $tmp2["acl_res_id"];

	$tmp1 =& $pearDB->query("SELECT hg_hg_id FROM acl_resources_hg_relations WHERE acl_resources_hg_relations.acl_res_id = '$acl_res_id'");
	while($tmp2 = $tmp1->fetchRow()){
		$host_group_id = $tmp2["hg_hg_id"];

        	$tmp3 =& $pearDB->query("SELECT hg_name FROM host, hostgroup, acl_resources_hg_relations WHERE hostgroup.hg_id = '$host_group_id' AND acl_resources_hg_relations.acl_res_id = '$acl_res_id'");
        	$tmp4 = $tmp3->fetchRow();      
        	$hg_name = $tmp4["hg_name"];

                $counterTotal = 0;
                $counterUP = 0;
                $counterDown = 0;

		$DBRESULT2 =& $pearDB->query("SELECT host_name FROM host, hostgroup_relation, acl_resources_hg_relations WHERE  hostgroup_relation.hostgroup_hg_id = '$host_group_id' AND hostgroup_relation.host_host_id = host.host_id AND acl_resources_hg_relations.hg_hg_id = '$host_group_id'");

		while($h = $DBRESULT2->fetchRow()){
			$DBRESULT3 =& $pearDBndo->query("SELECT current_state FROM ".$ndo_base_prefix."hoststatus, ".$ndo_base_prefix."hosts, ".$ndo_base_prefix."objects no WHERE ".$ndo_base_prefix."hoststatus.host_object_id = ".$ndo_base_prefix."hosts.host_object_id AND ".$ndo_base_prefix."hosts.display_name = '".$h["host_name"]."'");
                        while($stt = $DBRESULT3->fetchRow()){
                                if ($stt["current_state"] == 1)
                                        $counterDown++;
                                if ($stt["current_state"] == 0)
                                        $counterUP++;
                                $counterTotal++;
                        }
                }

                if ($counterTotal){
                        $percentU = $counterUP / $counterTotal * 100;
                        $percentD = $counterDown / $counterTotal * 100;
                        $hostgroupU[$hg["hg_name"]] = $percentU;
                        $bar_blue->data[] = $percentU;
                        $bar_red->data[] = $percentD;
                        if ($percentU > $percentmax)
                                $percentmax = $percentU;
                        if ($strnameY)
                                $strnameY .= ", ";
                        $strnameY .= $hg_name; //$hg["hg_name"];
                }
	}	
	}

	// create the graph object:
	$g = new graph();
	$g->bg_colour = '#F3F6F6';
	$g->title( _('Status of Host Groups'), '{font-size:18px; color: #424242; margin: 5px; background-color: #F3F6F6; padding:5px; padding-left: 20px; padding-right: 20px;}' );
	
	$g->data_sets[] = $bar_blue;
	$g->data_sets[] = $bar_red;
	
	$g->set_x_axis_3d( 12 );
	$g->x_axis_colour( '#909090', '#ADB5C7' );
	$g->y_axis_colour( '#909090', '#ADB5C7' );
	
	$g->set_tool_tip( " "._('Availability of hosts from the group') . ' #x_label# : #val# %' );
	
	$g->set_x_labels(array($strnameY));
	$g->set_y_max( $percentmax );
	$g->y_label_steps( 5 );
	$g->set_y_legend( _('Availability'), 12, '#424242' );
	echo $g->render();
?>
