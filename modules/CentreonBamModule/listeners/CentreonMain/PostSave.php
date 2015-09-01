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

namespace CentreonBam\Listeners\CentreonMain;

use CentreonMain\Events\PostSave as PostSaveEvent;
use CentreonBam\Repository\BusinessActivityRepository;
use CentreonBam\Repository\BusinessActivityTagRepository;

class PostSave
{
    /**
     * @param CentreonMain\Events\PostSave $event
     */
    public static function execute(PostSaveEvent $event)
    {
        $parameters = $event->getParameters();
        $extraParameters = $event->getExtraParameters();
        if (isset($extraParameters['centreon-bam'])) {
            if ($event->getObjectName() === 'aclresource') {
                if (isset($extraParameters['centreon-bam']['aclresource_business_activities'])) {
                    $baIds = array_filter(array_map('trim',explode(',',$extraParameters['centreon-bam']['aclresource_business_activities'])));
                    BusinessActivityRepository::updateBusinessActivityAcl($event->getAction(), $event->getObjectId(), $baIds);
                }
                if (isset($extraParameters['centreon-bam']['aclresource_business_activity_tags'])) {
                    $baTagIds = array_filter(array_map('trim',explode(',',$extraParameters['centreon-bam']['aclresource_business_activity_tags'])));
                    BusinessActivityTagRepository::updateBusinessActivityTagAcl($event->getAction(), $event->getObjectId(), $baTagIds);
                }
                if (isset($extraParameters['centreon-bam']['aclresource_all_bas'])) {
                    $allBas = $extraParameters['centreon-bam']['aclresource_all_bas'];
                    BusinessActivityRepository::updateAllBusinessActivitiesAcl($event->getAction(), $event->getObjectId(), $allBas);
                } else {
                    BusinessActivityRepository::updateAllBusinessActivitiesAcl($event->getAction(), $event->getObjectId(), '0');
                }
            }
        }
    }
}
