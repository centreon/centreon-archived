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
 */

namespace CentreonBam\Listeners\Core;

use Centreon\Events\LoadFormDatas as LoadFormDatasEvent;
use CentreonBam\Models\AclresourceBusinessActivitiesParams;

class LoadFormDatas
{
    /**
     * @param Core\Events\LoadFormDatas $event
     */
    public static function execute(LoadFormDatasEvent $event)
    {
        $route = $event->getRoute();
        $objectId = $event->getObjectId();
        $parameters = $event->getParameters();
        if ($route === '/centreon-administration/aclresource/update') {
            try {
                $allBasParameter = AclresourceBusinessActivitiesParams::getParameters($objectId, 'all_business_activities');
                $event->addParameters(array(
                    'centreon-bam__aclresource_all_bas' => $allBasParameter['all_business_activities']
                ));
            } catch (\Exception $e) {

            }
        }
    }
}
