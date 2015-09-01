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

namespace CentreonBam\Repository;

use Centreon\Internal\Di;
use Centreon\Repository\FormRepository;
use CentreonBam\Models\BusinessActivity;

/**
 * @author Sylvestre Ho <sho@centreon.com>
 * @package CentreonBam
 * @subpackage Repository
 */
class BusinessViewRepository extends FormRepository
{

    /**
     *
     * @param string $name
     * @return string
     */
    public static function getBuList()
    {
        // Initializing connection
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        #$router = $di->get('router');

        $buList = BusinessActivity::getList("ba_id,name", -1, 0, null, "ASC", array('ba_type_id' => 1));

		return $buList;
	}
}
