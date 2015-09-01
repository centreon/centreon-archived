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

namespace CentreonConfiguration\Listeners\CentreonMain;

use CentreonMain\Events\PostSave as PostSaveEvent;
use CentreonConfiguration\Repository\HostRepository;
use CentreonConfiguration\Repository\HostTagRepository;
use CentreonConfiguration\Repository\ServiceRepository;
use CentreonConfiguration\Repository\ServiceTagRepository;

class PostSave
{
    /**
     * @param CentreonMain\Events\PostSave $event
     */
    public static function execute(PostSaveEvent $event)
    {
        $parameters = $event->getParameters();
        $extraParameters = $event->getExtraParameters();
        if (isset($extraParameters['centreon-configuration'])) {
            if ($event->getObjectName() === 'aclresource') {
                if (isset($extraParameters['centreon-configuration']['aclresource_hosts'])) {
                    $hostIds = array_filter(array_map('trim',explode(',',$extraParameters['centreon-configuration']['aclresource_hosts'])));
                    HostRepository::updateHostAcl($event->getAction(), $event->getObjectId(), $hostIds);
                }
                if (isset($extraParameters['centreon-configuration']['aclresource_host_tags'])) {
                    $hostTagIds = array_filter(array_map('trim',explode(',',$extraParameters['centreon-configuration']['aclresource_host_tags'])));
                    HostTagRepository::updateHostTagAcl($event->getAction(), $event->getObjectId(), $hostTagIds);
                }
                if (isset($extraParameters['centreon-configuration']['aclresource_services'])) {
                    $serviceIds = array_filter(array_map('trim',explode(',',$extraParameters['centreon-configuration']['aclresource_services'])));
                    ServiceRepository::updateServiceAcl($event->getAction(), $event->getObjectId(), $serviceIds);
                }
                if (isset($extraParameters['centreon-configuration']['aclresource_service_tags'])) {
                    $serviceTagIds = array_filter(array_map('trim',explode(',',$extraParameters['centreon-configuration']['aclresource_service_tags'])));
                    ServiceTagRepository::updateServiceTagAcl($event->getAction(), $event->getObjectId(), $serviceTagIds);
                }
                if (isset($extraParameters['centreon-configuration']['aclresource_all_hosts'])) {
                    $allHosts = $extraParameters['centreon-configuration']['aclresource_all_hosts'];
                    HostRepository::updateAllHostsAcl($event->getAction(), $event->getObjectId(), $allHosts);
                } else {
                    HostRepository::updateAllHostsAcl($event->getAction(), $event->getObjectId(), '0');
                }
            }
        }
    }
}
