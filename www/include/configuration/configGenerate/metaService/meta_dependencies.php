<?php
/*
 * Copyright 2005-2010 MERETHIS
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

	if (!isset($oreon))
 		exit();
	
	$str = NULL;
	$i = 1;
	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_dependencies.cfg", $oreon->user->get_name());

	$rq = "SELECT * FROM dependency dep WHERE (SELECT DISTINCT COUNT(*) FROM dependency_metaserviceParent_relation dmspr WHERE dmspr.dependency_dep_id = dep.dep_id) > 0 AND (SELECT DISTINCT COUNT(*) FROM dependency_metaserviceChild_relation dmscr WHERE dmscr.dependency_dep_id = dep.dep_id) > 0";
	$DBRESULT =& $pearDB->query($rq);
	$dependency = array();
	$i = 1;
	$str = NULL;
	while($dependency =& $DBRESULT->fetchRow())	{
		$BP = false;
		$DBRESULT2 =& $pearDB->query("SELECT meta_service_meta_id FROM dependency_metaserviceParent_relation WHERE dependency_dep_id = '".$dependency["dep_id"]."'");
		$metaPar = NULL;
		while ($metaPar =& $DBRESULT2->fetchRow()) {
			if (isset($gbArr[7][$metaPar["meta_service_meta_id"]])) {
				$DBRESULT3 =& $pearDB->query("SELECT meta_service_meta_id FROM dependency_metaserviceChild_relation WHERE dependency_dep_id = '".$dependency["dep_id"]."'");
				$metaCh = NULL;
				while ($metaCh =& $DBRESULT3->fetchRow()) {
					if (isset($gbArr[7][$metaCh["meta_service_meta_id"]])) {
						$ret["comment"]["comment"] ? ($str .= "# '".$dependency["dep_name"]."' host dependency definition ".$i."\n") : NULL;
						if ($ret["comment"]["comment"] && $dependency["dep_comment"])	{
							$comment = array();
							$comment = explode("\n", $dependency["dep_comment"]);
							foreach ($comment as $cmt)
								$str .= "# ".$cmt."\n";
						}
						$str .= "define servicedependency{\n";
						$str .= print_line("dependent_host_name", "_Module_Meta");
						$str .= print_line("dependent_service_description", "meta_".$metaCh["meta_service_meta_id"]);
						$str .= print_line("host_name", "_Module_Meta");
						$str .= print_line("service_description", "meta_".$metaPar["meta_service_meta_id"]);
						if (isset($dependency["inherits_parent"]["inherits_parent"]) && $dependency["inherits_parent"]["inherits_parent"] != NULL) 
							$str .= print_line("inherits_parent", $dependency["inherits_parent"]["inherits_parent"]);
						if (isset($dependency["execution_failure_criteria"]) && $dependency["execution_failure_criteria"] != NULL) 
							$str .= print_line("execution_failure_criteria", $dependency["execution_failure_criteria"]);
						if (isset($dependency["notification_failure_criteria"]) && $dependency["notification_failure_criteria"] != NULL) 
							$str .= print_line("notification_failure_criteria", $dependency["notification_failure_criteria"]);
						$str .= "}\n\n";
						$i++;
					}
				}
				$DBRESULT3->free();
			}
		}
		$DBRESULT2->free();
	}
	unset($dependency);
	$DBRESULT->free();
	write_in_file($handle, $str, $nagiosCFGPath.$tab['id']."/meta_dependencies.cfg");
	fclose($handle);
	unset($str);
?>