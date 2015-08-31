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

    function deleteDowntimeFromDb($centreon, $select = array()) {
        if (isset($select)) {
            if ($centreon->broker->getBroker() == "ndo") {
                $pearDBndo = new CentreonDB("ndo");
            } else {
                $pearDBndo = new CentreonDB("centstorage");
            }
		    $ndo_base_prefix = getNDOPrefix();
            $dIds =  array();
            foreach ($select as $key => $val) {
                $tmp = explode(";",$key);
                if (isset($tmp[1])) {
                    $dIds[] = $tmp[1];
                }
            }
            if (count($dIds)) {
                if ($centreon->broker->getBroker() == "ndo") {
                    $request = "DELETE FROM ".$ndo_base_prefix."downtimehistory WHERE internal_downtime_id IN (".implode(', ',$dIds).")";
                } else {
                    $request = "DELETE FROM downtimes WHERE internal_id IN (".implode(', ',$dIds).")";
                }
                $pearDBndo->query($request);
            }
		}
    }

?>