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
use Centreon\Internal\Exception;

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
    protected $externalAttributeSet = array();
    
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
     * @return type
     */
    public function listAction($fields = null, $count = -1, $offset = 0)
    {
        try {
            // Getting the repository name
            $repository = $this->repository;

            // Parsing attributes List
            $givenFields = (!is_null($fields)) ? $fields : $this->liteAttributesSet;
            $fieldsToQuery = array_diff(
                explode(',', $givenFields),
                array_column($this->externalAttributeSet, 'type')
            );
            
            // Getting the list from
            $objectList = $repository::getList($fieldsToQuery, $count, $offset);
            $this->normalizeParams($objectList);
            
            $this->getExternalObject($objectList);
            
        } catch (\Exception $ex) {
            
        }
        
        return $objectList;
    }
    
    /**
     * 
     * @param type $objectList
     */
    private function getExternalObject(&$objectList)
    {
        foreach ($objectList as &$myObject) {
            
            $myExternalParams = array();
            
            foreach ($this->externalAttributeSet as $externalAttribute) {
                if ($externalAttribute['link'] == 'relation') {
                    $relClass = $this->relationMap[$externalAttribute['objectClass']];
                    $exP = $relClass::getMergedParameters(
                        $externalAttribute['fields'],
                        array(),
                        -1,
                        0,
                        null,
                        "ASC",
                        array($this->attributesMap['id'] => $myObject[$this->attributesMap['id']]),
                        "AND"
                    );
                    
                    if (count($exP) > 0) {
                        if ($externalAttribute['group']) {
                            $myExternalParams = array_merge($myExternalParams, $exP);
                        } else {
                            $myExternalParams = array_merge($myExternalParams, $exP[0]);
                        }
                    }
                    
                }
            }
            
            $myObject = array_merge($myObject, $myExternalParams);
        }
    }
    
    /**
     * 
     * @param type $objectSlug
     * @param type $fields
     * @param type $linkedObject
     * @return type
     */
    public function showAction($objectSlug, $fields = null, $linkedObject = '')
    {
        $repository = $this->repository;

        //
        $fields = (!is_null($fields)) ? $fields : '*';
        
        $object = $repository::load($objectSlug, $fields);
        $this->normalizeSingleSet($object);
        
        return $object;
        
        /*$objPrimaryKey = $obj::getPrimaryKey();
        $linkedObjects = (!empty($linkedObject)) ? explode(',', $linkedObject) : array();
        $object['links'] = $this->getLinkedObjects($params['id'], $linkedObjects);*/
    }
    
    /**
     * 
     * @param type $params
     * @return type
     */
    private function parseObjectParams($params)
    {
        $finalParamList = array();

        // First we seperate the params
        $rawParamList = explode(';', $params);

        // 
        foreach ($rawParamList as $param) {
            $openingDelimiterPos = strpos($param, '[');
            $closingDelimiterPos = strrpos($param, ']');
            if (($openingDelimiterPos !== false) || ($closingDelimiterPos !== false)) {
                $paramName = substr($param, 0, $openingDelimiterPos);
                $paramValue = substr($param, $openingDelimiterPos + 1, ($closingDelimiterPos - $openingDelimiterPos) - 1);
                $finalParamList[$paramName] = $paramValue;
            }
        }

        return $finalParamList;
    }
    
    /**
     * 
     * @param string $params
     */
    public function createAction($params)
    {
        try {
            $repository = $this->repository;
            $paramList = $this->parseObjectParams($params);
            $idOfCreatedElement = $repository::create(
                        $paramList,
                        'api',
                        $this->objectBaseUrl . '/update'
                    );
            \Centreon\Internal\Utils\CommandLine\InputOutput::display("Object successfully created", true, 'green');
        } catch (Exception $ex) {
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($ex->getMessage(), true, 'red');
        }
    }
    
    /**
     * 
     * @param string $object
     * @param string $params
     */
    public function updateAction($object, $params)
    {
        try {
            $repository = $this->repository;
            $paramList = $this->parseObjectParams($params);
            $paramList['object_id'] = $object;
            $repository::update(
                        $paramList,
                        'api',
                        $this->objectBaseUrl . '/update'
                    );
            \Centreon\Internal\Utils\CommandLine\InputOutput::display("Object successfully created", true, 'green');
        } catch (Exception $ex) {
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($ex->getMessage(), true, 'red');
        }
    }
    
    /**
     * 
     * @param type $id
     */
    public function deleteAction($id)
    {
        try {
            $repository = $this->repository;
            $repository::delete(array($id));
            \Centreon\Internal\Utils\CommandLine\InputOutput::display("Object successfully deleted", true, 'green');
        } catch (Exception $ex) {
            \Centreon\Internal\Utils\CommandLine\InputOutput::display($ex->getMessage(), true, 'red');
        }
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
