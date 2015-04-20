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
namespace Centreon\Api\Internal;

use Centreon\Internal\Command\AbstractCommand;
use Centreon\Internal\Module\Informations;

/**
 * Description of BasicCrud
 *
 * @author lionel
 */
class BasicCrud extends AbstractCommand
{
    /**
     *
     * @var type 
     */
    protected $objectManifest = '';
    
    /**
     *
     * @var type 
     */
    protected $liteAttributesSet = array();
    
    /**
     *
     * @var type 
     */
    protected $attributesMap = array();
    
    /**
     *
     * @var type 
     */
    protected $objectName = '';
    
    /**
     *
     * @var type 
     */
    protected $objectBaseUrl = '';
    
    /**
     *
     * @var type 
     */
    protected $objectClass = '';
    
    /**
     *
     * @var type 
     */
    protected $repository;
    
    /**
     *
     * @var type 
     */
    public static $moduleShortName = '';
    
    /**
     *
     * @var type 
     */
    public $relationMap = array();
    
    /**
     *
     * @var type 
     */
    public $simpleRelationMap = array();
    
    /**
     * 
     */
    const OBJ_NOT_EXIST = 'Object not in database.';
    
    /**
     * 
     */
    public function __construct()
    {
        parent::__construct();
        
        // Retrieving the real module Shortname
        $rc = new \ReflectionClass(get_class($this));
        $moduleName = Informations::getModuleFromPath($rc->getFileName());
        static::$moduleShortName = Informations::getModuleSlugName($moduleName);
        
        // Getting config values form the manifest file
        $this->parseManifest();
        
        // Configuring the object repository
        if (is_null($this->repository)) {
            throw new Exception('Repository unspecified');
        }
        $repository = $this->repository;
        $repository::setRelationMap($this->relationMap);
        $repository::setObjectName($this->objectName);
        $repository::setObjectClass($this->objectClass);
        if (!empty($this->secondaryObjectClass)) {
            $repository::setSecondaryObjectClass($this->secondaryObjectClass);
        }

        // Settin object base url
        $this->objectBaseUrl = '/' . static::$moduleShortName . '/' . $this->objectName;
    }
    
    /**
     * 
     * @param string $manifestFile
     */
    private function parseManifest()
    {
        $manifestDir = realpath(Informations::getModulePath(static::$moduleShortName) . '/');
        $manifestFile = $manifestDir . '/api/internal/' . $this->objectName . 'Manifest.json';
        $this->objectManifest = json_decode(file_get_contents($manifestFile), true);
        foreach ($this->objectManifest as $mKey => $mValue) {
            if (property_exists($this, $mKey)) {
                $this->$mKey = $mValue;
            }
        }
    }
    
    /**
     * 
     * @param type $dataset
     * @param type $strict
     */
    protected function normalizeParams(&$dataset, $strict = true)
    {
        foreach ($dataset as &$value) {
            $this->normalizeSingleSet($value, $strict);
        }
    }
    
    /**
     * 
     * @param type $dataset
     * @param type $strict
     */
    protected function normalizeSingleSet(&$dataset, $strict = true)
    {
        $newDataset = array();
        foreach($dataset as $dKey => $dValue) {
            $normalizeKey = array_search($dKey, $this->attributesMap);
            if ($normalizeKey !== false) {
                $newDataset[$normalizeKey] = $dValue;
            } else {
                $newDataset[$dKey] = $dValue;
            }
        }
        
        if ($strict) {
            $diffKey = array_diff(array_keys($newDataset), array_keys($this->attributesMap));
            foreach ($diffKey as $dKey) {
                unset($newDataset[$dKey]);
            }
        }
        
        $dataset = $newDataset;
    }

    /**
     * 
     * @param type $fields
     * @param type $count
     * @param type $offset
     */
    public function listAction($fields = null, $count = -1, $offset = 0)
    {
        try {
            // Getting the repository name
            $repository = $this->repository;

            // Getting the list from 
            $fields = (!is_null($fields)) ? $fields : $this->liteAttributesSet;
            $objectList = $repository::getList($fields, $count, $offset);
            $this->normalizeParams($objectList);
        } catch (\Exception $ex) {
            
        }
        
        return $objectList;
    }
    
    /**
     * 
     * @param type $objectSlug
     * @param type $fields
     */
    public function showAction($objectSlug, $fields = null, $linkedObject = '')
    {
        $repository = $this->repository;

        //
        $fields = (!id_null($fields)) ? $fields : '*';
        
        $object = $repository::load($objectSlug, $fields);
        $this->normalizeSingleSet($object);
        
        return $object;
        
        /*$objPrimaryKey = $obj::getPrimaryKey();
        $linkedObjects = (!empty($linkedObject)) ? explode(',', $linkedObject) : array();
        $object['links'] = $this->getLinkedObjects($params['id'], $linkedObjects);*/
    }
    
    /**
     * 
     */
    public function createAction()
    {
        echo "Not implemented yet";
    }
    
    /**
     * 
     */
    public function updateAction()
    {
        echo "Not implemented yet";
    }
    
    /**
     * 
     */
    public function deleteAction()
    {
        echo "Not implemented yet";
    }
    
    /**
     * Action for duplicate
     *
     */
    public function duplicateAction()
    {
        echo "Not implemented yet";
    }
}
