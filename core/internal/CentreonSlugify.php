<?php
/*
 * Copyright 2005-2015 CENTREON
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
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
