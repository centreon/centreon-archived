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

namespace CentreonConfiguration\Api\Internal;
use CentreonConfiguration\Api\Internal\BasicMacroSupport;
use CentreonAdministration\Repository\TagsRepository;
use \Centreon\Internal\Utils\CommandLine\InputOutput;

/**
 * 
 */
class BasicTagSupport extends BasicMacroSupport
{
    /**
     * 
     * @param string $object
     * @param string $tag
     */
    public function addTagAction($object, $tag)
    {
        $iNbAdded = 0;
        $sLibTag = 'tag';
        $aError = array();
        
        $repository = $this->repository;
        $sName = $this->objectName;
        $repository::transco($object);
        $aId = $repository::getListBySlugName($object[$sName]);
        if (count($aId) > 0) {
            $object = $aId[0]['id'];
        } else {
            throw new \Exception(static::OBJ_NOT_EXIST, 1);
        }

        $aTags = explode(",", $tag);

        foreach ($aTags as $sTag) {
            $bOkyToAdd = true;
            $iIdTag = TagsRepository::getTagId($sTag, 1, 0);
            if (!empty($iIdTag)) {
                $bLink = TagsRepository::isLink($sName, $object, $iIdTag);
                if ($bLink) {
                    $aError[] = $sTag;
                    $bOkyToAdd = false;
                }
            }
            if ($bOkyToAdd) {
                TagsRepository::add($sTag, $this->objectName, $object, 1);
                $iNbAdded++;
            }
        }

        if (count($aTags) == 1) {
            $sLibTag = $aTags[0];
        } else if (count($aTags) > 1) {
            $sLibTag .= "s";
        }

        if ($iNbAdded > 0) {
            InputOutput::display(
                $sLibTag . " has been successfully added to the object",
                true,
                'green'
            );

            if ($iNbAdded > 0 && count($aError) > 0) {
                throw new \Exception("but some tags already exists : '". implode("', '", $aError)."'", 1);
            }
        } else {
            throw new \Exception($sLibTag . " already exists", 1);
        }

    }
    
    /**
     * 
     * @param string $object
     */
    public function listTagAction($object = null)
    {
        $repository = $this->repository;
        $repository::transco($object);
        $sName = $this->objectName;

        $aId = $repository::getListBySlugName($object[$sName]);
        if (count($aId) > 0) {
            $object = $aId[0]['id'];
        } else {
            throw new \Exception(static::OBJ_NOT_EXIST, 1);
        }
        $TagList = TagsRepository::getList($this->objectName, $object, 1);

        if (count($TagList) >0) {
            foreach ($TagList as $tag) {
                echo $tag['text'] . "\n";
            }
        } else{
            throw new \Exception('No results', 1);
        }
    }
    
    /**
     * 
     * @param string $object
     * @param string $tag
     */
    public function removeTagAction($object, $tag)
    {
        $iNbDeleted = 0;
        $sLibTag  = "tag";
        
        $repository = $this->repository;
        $sName = $this->objectName;
        $repository::transco($object);
        $aId = $repository::getListBySlugName($object[$sName]);
        if (count($aId) > 0) {
            $object = $aId[0]['id'];
        } else {
            throw new \Exception(static::OBJ_NOT_EXIST, 1);
        }

        $aTags = explode(",", $tag);
        foreach ($aTags as $sTag) {
            $iReturn = TagsRepository::isExist($sTag);
            $bLinked = TagsRepository::isLink($this->objectName, $object, $iReturn);

            if (!$bLinked) {
                throw new \Exception("This tag can't be found on the object", 1);
            }
            TagsRepository::delete($sTag, $this->objectName, $object);
            $iNbDeleted++;

        }
        if ($iNbDeleted >1) 
            $sLibTag .= "s";


        InputOutput::display(
            $sLibTag." has been successfully removed from the object",
            true,
            'green'
        );

    }
}
