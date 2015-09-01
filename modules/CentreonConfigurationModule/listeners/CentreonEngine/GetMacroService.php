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

namespace CentreonConfiguration\Listeners\CentreonEngine;

use Centreon\Internal\Di;
use CentreonEngine\Events\GetMacroService as MacroServiceEvent;
use CentreonConfiguration\Repository\ServiceRepository;
use CentreonConfiguration\Repository\ServicetemplateRepository;
use CentreonConfiguration\Repository\CustomMacroRepository;

class GetMacroService
{
    const MACRO_DOMAIN = 'CENTREON_DOMAIN';

    /**
     * @param CentreonEngine\Events\GetMacroService $event
     */
    public static function execute(MacroServiceEvent $event)
    {
        /* Macros domain for service*/
        $services = array_keys(ServiceRepository::getServicesByPollerId($event->getPollerId()));
        foreach ($services as $serviceId) {
            $arr = ServiceRepository::getDomain($serviceId);
            foreach ($arr as $domainName) {
                $event->setMacro($serviceId, self::MACRO_DOMAIN, $domainName);
            }
        }
        /* Macros domain for service template */
        $servicesTmpl = ServicetemplateRepository::getList('service_id');
        foreach ($servicesTmpl as $serviceTmpl) {
            $arr = ServicetemplateRepository::getDomain($serviceTmpl['service_id']);
            foreach ($arr as $domainName) {
                $event->setMacro($serviceTmpl['service_id'], self::MACRO_DOMAIN, $domainName);
            }
        }
    }
}
