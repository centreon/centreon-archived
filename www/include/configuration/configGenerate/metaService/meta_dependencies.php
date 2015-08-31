<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

	if (!isset($oreon))
 		exit();
	
	$str = NULL;
	$i = 1;
	$handle = create_file($nagiosCFGPath.$tab['id']."/meta_dependencies.cfg", $oreon->user->get_name());

	$rq = "SELECT * FROM dependency dep WHERE (SELECT DISTINCT COUNT(*) FROM dependency_metaserviceParent_relation dmspr WHERE dmspr.dependency_dep_id = dep.dep_id) > 0 AND (SELECT DISTINCT COUNT(*) FROM dependency_metaserviceChild_relation dmscr WHERE dmscr.dependency_dep_id = dep.dep_id) > 0";
	$DBRESULT = $pearDB->query($rq);
	$dependency = array();
	$i = 1;
	$str = NULL;
	while($dependency = $DBRESULT->fetchRow())	{
		$BP = false;
		$DBRESULT2 = $pearDB->query("SELECT meta_service_meta_id FROM dependency_metaserviceParent_relation WHERE dependency_dep_id = '".$dependency["dep_id"]."'");
		$metaPar = NULL;
		while ($metaPar = $DBRESULT2->fetchRow()) {
			if (isset($gbArr[7][$metaPar["meta_service_meta_id"]])) {
				$DBRESULT3 = $pearDB->query("SELECT meta_service_meta_id FROM dependency_metaserviceChild_relation WHERE dependency_dep_id = '".$dependency["dep_id"]."'");
				$metaCh = NULL;
				while ($metaCh = $DBRESULT3->fetchRow()) {
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
	write_in_file($handle, html_entity_decode($str, ENT_QUOTES, 'UTF-8'), $nagiosCFGPath.$tab['id']."/meta_dependencies.cfg");
	fclose($handle);
	unset($str);
?>
