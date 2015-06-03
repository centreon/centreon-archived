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
        $this->oObject = new Slugify($this->sRegex);

        $this->oRepository = $oRepository;
        $this->oModel = $oModel;
        $this->sSlugField = $oModel::getSlugField();
    }
    
    /**
     * @param string $sValue Description
     * @return string
     */
    public function slug($sValue)
    {
        $sSlug = $this->oObject->slugify($sValue);
        $oRepo= $this->oRepository;

        $aObject = $oRepo::getList("*", -1, 0, null, 'asc', array($this->sSlugField => $sSlug));
        
        $sSlugNew = self::concat($sSlug, count($aObject));

        return $sSlugNew;
    }
    /**
     * 
     * @param type $sValue
     * @return string
     */
    public static function concat($sValue, $iNb)
    {
        $sSlugNew = "";
        $aValues = explode(static::$sGlue, $sValue);
        $iLast = end($aValues);
        if ($iNb == 0) {
            $sSlugNew = $sValue;
        } else {
            if (is_int($iLast)) {
                $iPos = strrpos($sValue, static::$sGlue);
                $sSlugNew = substr($sValue, 0, $iPos).static::$sGlue.($iNb + 1);
            } else {
                static::$index++;
                $sSlugNew = $sValue.static::$sGlue.static::$index;
            }
        }
        return $sSlugNew;
    }
}
