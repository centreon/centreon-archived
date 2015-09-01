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
 * Description of Slugify
 *
 * @author tmechouet
 */

namespace Centreon\Internal;
use Cocur\Slugify\Slugify;

class CentreonSlugify
{
    static $index = 1;


    protected $oObject;
    /**
     *
     * @var string 
     */
    protected $sRegex =  '/([^a-z0-9]|-)+/';
    /**
     *
     * @var object 
     */
    protected $oModel;

    /**
     *
     * @var object 
     */
    protected $oRepository;

    /**
     * 
     * @param string
     */
    protected $sSlugField;
    
    /**
     *
     * @var string 
     */
    public static $sGlue = "-";

    public function __construct($oModel, $oRepository)
    {
        if (!is_null($oModel::getSlugField())) {
            $this->oObject = new Slugify($this->sRegex);

            $this->oRepository = $oRepository;
            $this->oModel = $oModel;
            $this->sSlugField = $oModel::getSlugField();
        }
    }
    
    /**
     * @param string $sValue Description
     * @param int $iIobjectId
     * @return string
     */
    public function slug($sValue, $iIobjectId = '')
    {
        $oRepo  = $this->oRepository;
        $oModel = $this->oModel;
        
        $sSlug  = $this->oObject->slugify($sValue);
        
        $aValues  = explode(static::$sGlue, $sSlug);
        $iLast    = end($aValues);
        
        if (is_numeric($iLast)) {
            $iPos     = strrpos($sSlug, static::$sGlue);
            $sSlugToSearch = substr($sSlug, 0, $iPos).static::$sGlue;
        } else {
            $sSlugToSearch = $sSlug;
        }

        $aObject = $oRepo::getList("*", -1, 0, null, 'asc', array($this->sSlugField => $sSlugToSearch."%"), 'AND');
        
        if (self::slugLength($sSlugToSearch, $aObject, $oModel::getSlugField())) {
            $sSlugNew = self::concat($oModel, $sSlug, $iIobjectId, $aObject);
        } else {
            $sSlugNew = $sSlug;
        }
       
        return $sSlugNew;
    }
    
    /**
     * @param object $oModel Description
     * @param type $sValue
     * @param int $iIobjectId
     * @param array $aObject Description
     * @return string
     */
    public static function concat($oModel, $sValue, $iIobjectId, $aObject)
    {
        $sSlugNew = "";
        $iNb      = count($aObject);
        $aValues  = explode(static::$sGlue, $sValue);
        $iLast    = end($aValues);
        $iPos     = strrpos($sValue, static::$sGlue);
        $sPrimary = $oModel::getPrimaryKey();
        $sSlugField = $oModel::getSlugField();
        
        if ($iNb == 0) {
            $sSlugNew = $sValue;
        } else {
            $iHighSlug = static::highPrefixSlug($aObject, $sSlugField);

            if (static::testSlug($sValue, $aObject, $sSlugField, $iIobjectId, $sPrimary)) {
                $sSlugNew = $sValue;
            } elseif (is_numeric($iLast)) {  
                $sSlugNew = substr($sValue, 0, $iPos).static::$sGlue.($iHighSlug + 1);
            } else {
                $sSlugNew = $sValue.static::$sGlue.$iNb;
            }
        }
       
        return $sSlugNew;
    }
    /**
     * 
     * @param array $aObject
     * @param string $sSlugField
     * @return int
     */
    
    public static function highPrefixSlug($aObject, $sSlugField)
    {
        $iReturn = 0;
        foreach ($aObject as $cle => $valeur) {
            if (isset($valeur[$sSlugField])) {
                $aValues = explode(static::$sGlue, $valeur[$sSlugField]);
                $iEnd    = end($aValues);
                if (is_numeric($iEnd) && $iEnd > $iReturn) {
                    $iReturn = $iEnd;
                }
            }
        }

        return $iReturn;
    }
    
    /**
     * Cette méthode test s'il s'agit d'un update d'un objet, le slug ne sera pas modifié
     * 
     * @param string $sNewSlug   new slug
     * @param array  $aObject    array containts the slug in database
     * @param string $sSlugField name of field slug in model
     * @param int    $iIdObject  id of object which is updated
     * @param string $sPrimary   name of field ID in model
     * @return boolean
     */
    public static function testSlug($sNewSlug, $aObject, $sSlugField, $iIdObject, $sPrimary)
    {
        foreach ($aObject as $cle => $valeur) { 
            if (isset($valeur[$sSlugField]) 
                    && $sNewSlug == $valeur[$sSlugField] 
                    && !empty($iIdObject)
                    && $iIdObject == $valeur[$sPrimary]) {
                        return true;
            }
        }

        return false;
    }
    
    /**
     * 
     * @param type $sNewSlug
     * @param type $aObject
     * @param type $sSlugField
     * @return boolean
     */
    public static function slugLength($sNewSlug, $aObject, $sSlugField)
    {
        foreach ($aObject as $cle => $valeur) { 
            if (isset($valeur[$sSlugField]) 
                    && strlen($sNewSlug) == strlen($valeur[$sSlugField])) {
                        return true;
            }
        }

        return false;
    }

}
