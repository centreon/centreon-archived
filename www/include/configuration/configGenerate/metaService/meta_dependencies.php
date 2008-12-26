<?php
/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

	if (!isset($oreon))
 		exit();
	
	$str = NULL;
	$i = 1;
	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_dependencies.cfg", $oreon->user->get_name());

	$rq = "SELECT * FROM dependency dep WHERE (SELECT DISTINCT COUNT(*) FROM dependency_metaserviceParent_relation dmspr WHERE dmspr.dependency_dep_id = dep.dep_id) > 0 AND (SELECT DISTINCT COUNT(*) FROM dependency_metaserviceChild_relation dmscr WHERE dmscr.dependency_dep_id = dep.dep_id) > 0";
	$DBRESULT =& $pearDB->query($rq);
	if (PEAR::isError($DBRESULT))
		print "DB Error : SELECT * FROM dependency dep WHERE (SELECT DISTINCT COUNT(*)... : ".$DBRESULT->getMessage()."<br />";
	$dependency = array();
	$i = 1;
	$str = NULL;
	while($dependency =& $DBRESULT->fetchRow())	{
		$BP = false;
		$DBRESULT2 =& $pearDB->query("SELECT meta_service_meta_id FROM dependency_metaserviceParent_relation WHERE dependency_dep_id = '".$dependency["dep_id"]."'");
		if (PEAR::isError($DBRESULT2))
			print "DB Error : SELECT meta_service_meta_id FROM dependency_metaserviceParent_relation.. : ".$DBRESULT2->getMessage()."<br />";
		$metaPar = NULL;
		while ($metaPar =& $DBRESULT2->fetchRow()) {
			if (isset($gbArr[7][$metaPar["meta_service_meta_id"]])) {
				$DBRESULT3 =& $pearDB->query("SELECT meta_service_meta_id FROM dependency_metaserviceChild_relation WHERE dependency_dep_id = '".$dependency["dep_id"]."'");
				if (PEAR::isError($DBRESULT3))
					print "DB Error : SELECT meta_service_meta_id FROM dependency_metaserviceChild_relation.. : ".$DBRESULT3->getMessage()."<br />";
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