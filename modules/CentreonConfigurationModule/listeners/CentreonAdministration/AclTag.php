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

/**
 * Description of AclTag
 *
 * @author bsauveton
 */

namespace CentreonConfiguration\Listeners\CentreonAdministration;
use CentreonAdministration\Events\aclTagsEvent;
use CentreonAdministration\Repository\TagsRepository;

class AclTag
{
    
    public static function execute(aclTagsEvent $event)
    {
        $params = $event->getParams();
        
        if(!empty($params["host-tags"])){
            $tagId = array();
            $tags = explode(",",$params["host-tags"]);
            foreach($tags as $tag){
                $tagId = array_merge($tagId, TagsRepository::getTagsIdByResource('hosts',$tag));
            }
            $params["host-tags"] = implode(',', $tagId);
        }
        
        if(!empty($params["service-tags"])){
            $tagId = array();
            $tags = explode(",",$params["service-tags"]);
            foreach($tags as $tag){
                $tagId = array_merge($tagId, TagsRepository::getTagsIdByResource('services',$tag));
            }
            $params["service-tags"] = implode(',', $tagId);
            
        }
        
        $event->setParams($params);
        
    }
    //put your code here
}
