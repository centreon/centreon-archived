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

namespace Centreon\Api\Internal;

use Centreon\Internal\Command\AbstractCommand;
use Centreon\Internal\Module\Informations;
use Centreon\Internal\Exception;
use Centreon\Internal\Di;

/**
 * Description of BasicCrud
 *
 * @author lionel
 */
class BasicCrud extends AbstractCommand
{
    /**
     *
     * @var array 
     */
    public $options = array();
    
    /**
     *
     * @var array 
     */
    protected $paramsToExclude = array();


    /**
     *
     * @var string 
     */
    protected $objectManifest = '';
    
    /**
     *
     * @var array 
     */
    protected $liteAttributesSet = array();
    
    /**
     *
     * @var array 
     */
    protected $externalAttributeSet = array();
    
    /**
     *
     * @var array 
     */
    public $attributesMap = array();
    
    /**
     *
     * @var string 
     */
    protected $objectName = '';
    
    /**
     *
     * @var string 
     */
    protected $objectBaseUrl = '';
    
    /**
     *
     * @var string 
     */
    protected $objectClass = '';
    
    /**
     *
     * @var type 
     */
    protected $repository;
    
    /**
     *
     * @var string 
     */
    public static $moduleShortName = '';
    
    /**
     *
     * @var array 
     */
    public $relationMap = array();
    
    /**
     *
     * @var array 
     */
    public $simpleRelationMap = array();
    
    /**
     * 
     */
    const OBJ_NOT_EXIST = 'Object not in database.';
    
    /**
     * 
     */
    public static $aRenameModules = array(
        'trap' => "traps"
    );
    
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
        $repository::setAttributesMap($this->attributesMap);
        if (!empty($this->secondaryObjectClass)) {
            $repository::setSecondaryObjectClass($this->secondaryObjectClass);
        }

        // Settin object base url
        $this->objectBaseUrl = '/' . static::$moduleShortName . '/' . $this->objectName;
    }
    
    /**
     * 
     */
    public function refreshAttributesMap()
    {
        $repository = $this->repository;
        $repository::setAttributesMap($this->attributesMap);
    }
    
    /**
     * 
     * @param type $attr
     * @return string
     */
    private function getChoices($attr)
    {
        $choices = "";
        if (!empty($attr['choices'])) {
            $choices = " Choices => ".implode(' , ', array_keys($attr['choices']));
        }
        return $choices;
    }

    /**
     * 
     * @param string $route
     */
    public function getAttributesMapFromForm($route)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = 'select ff.* from cfg_forms f
                    inner join cfg_forms_sections fs on fs.form_id = f.form_id
                    inner join cfg_forms_blocks fb on fb.section_id = fs.section_id
                    inner join cfg_forms_blocks_fields_relations fbfr on fbfr.block_id = fb.block_id
                    inner join cfg_forms_fields ff on ff.field_id = fbfr.field_id
                    where f.route = :route and ff.normalized_name != "" and ff.normalized_name is not null';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':route', $route, \PDO::PARAM_STR);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as $row) {
            if (empty($this->attributesMap[$row['normalized_name']])) {
                $this->attributesMap[$row['normalized_name']] = $row['name'];
            }
        }
        
    }
    
    /**
     * 
     * @param string $route
     * @param type $required
     */
    public function getFieldsFromForm($route, $required)
    {
        $db = Di::getDefault()->get('db_centreon');
        $sql = 'select ff.* from cfg_forms f
                    inner join cfg_forms_sections fs on fs.form_id = f.form_id
                    inner join cfg_forms_blocks fb on fb.section_id = fs.section_id
                    inner join cfg_forms_blocks_fields_relations fbfr on fbfr.block_id = fb.block_id
                    inner join cfg_forms_fields ff on ff.field_id = fbfr.field_id
                    where f.route = :route and ff.normalized_name != "" and ff.normalized_name is not null';
        $stmt = $db->prepare($sql);
        $stmt->bindParam(':route', $route, \PDO::PARAM_STR);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $attributes = json_decode($row['attributes'], true);
            $multiple = false;
            $mandatory = false;
            if ($required) {
                if ($row['mandatory'] !== "0") {
                    $mandatory = true;
                }
            }
            
            if (isset($attributes['multiple'])) {
                $multiple = $attributes['multiple'];
            }
            
            if (empty($this->attributesMap[$row['normalized_name']])) {
                $this->attributesMap[$row['normalized_name']] = $row['name'];
            }
            
            $this->options[$row['normalized_name']] = array(
                'paramType' => 'params',
                'help' => $row['help'].$this->getChoices($attributes),
                'type' => 'string',
                'multiple' => $multiple,
                'required' => $mandatory,
                'attributes' => $attributes
            );
            
            if ($required && isset($row['default_value']) && $row['default_value'] != "") {
                $this->options[$row['normalized_name']]['defaultValue'] = $row['default_value'];
            }
        }
    }

    /**
     * 
     */
    private function parseManifest()
    {
        $manifestDir = realpath(Informations::getModulePath(static::$moduleShortName) . '/');
        $manifestFile = $this->objectName . 'Manifest.json';
        $manifestPath = $manifestDir . '/api/internal/' . $manifestFile;
        $objectManifest = json_decode(file_get_contents($manifestPath), true);

        $moduleList = Informations::getModuleList();
        foreach ($moduleList as $module) {
            if ($module !== static::$moduleShortName) {
                $modulePath = Informations::getModulePath($module);
                if (file_exists($modulePath . '/api/internal/' . $manifestFile)) {
                    $objectManifest = self::mergeManifest(
                        $objectManifest,
                        json_decode(file_get_contents($modulePath . '/api/internal/' . $manifestFile), true)
                    );
                }
            }
        }
        $this->objectManifest = $objectManifest;

        foreach ($this->objectManifest as $mKey => $mValue) {
            if (property_exists($this, $mKey)) {
                $this->$mKey = $mValue;
            }
        }
    }

    /**
     * 
     * @param array $objectManifest
     * @param array $additionalManifest
     * @return array
     */
    private function mergeManifest($objectManifest, $additionalManifest)
    {
        if (isset($additionalManifest['liteAttributesSet'])) {
            $objectManifest['liteAttributesSet'] =
                $objectManifest['liteAttributesSet'] . ',' . $additionalManifest['liteAttributesSet'];
        }
        if (isset($additionalManifest['externalAttributeSet'])) {
            $objectManifest['externalAttributeSet'] =
                array_merge($objectManifest['externalAttributeSet'], $additionalManifest['externalAttributeSet']);
        }
        if (isset($additionalManifest['relationMap'])) {
            $objectManifest['relationMap'] =
                array_merge($objectManifest['relationMap'], $additionalManifest['relationMap']);
        }
        if (isset($additionalManifest['attributesMap'])) {
            $objectManifest['attributesMap'] =
                array_merge($objectManifest['attributesMap'], $additionalManifest['attributesMap']);
        }

        return $objectManifest;
    }
    
    /**
     * 
     * @param array $dataset
     * @param boolean $strict
     */
    protected function normalizeParams(&$dataset, $strict = true)
    {
        foreach ($dataset as &$value) {
            $this->normalizeSingleSet($value, $strict);
        }
    }
    
    /**
     * 
     * @param array $dataset
     * @param boolean $strict
     */
    protected function normalizeSingleSet(&$dataset, $strict = true)
    {
        $newDataset = array();
        foreach ($dataset as $dKey => $dValue) {
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
        $this->getExternalObject($objectList);

        $this->normalizeParams($objectList, false);
 
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
                        array(),
                        explode(',', $externalAttribute['fields']),
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
        $repository::transco($objectSlug);

        $sName = static::renameObject($this->objectName);

        $aId = $repository::getListBySlugName($objectSlug[$sName]);
        if (count($aId) > 0) {
            $objectSlug = $aId[0]['id'];
        } else {
            throw new \Exception(static::OBJ_NOT_EXIST);
        }

        $fields = (!is_null($fields)) ? $fields : '*';
                
        $object = $repository::load($objectSlug, $fields);
        
        $this->normalizeSingleSet($object, false);
        
        return $object;
    }
    
    /**
     * 
     * @param type $params
     * @return type
     */
    protected function parseObjectParams($params)
    {
        $finalParamList = array();
        $aFieldAttribute = array();
        foreach ($this->externalAttributeSet as $externalAttribute) {
            $aFieldAttribute[] = $externalAttribute['type'];
        }
        foreach ($params as $key => $param) {
            if (in_array($key, $aFieldAttribute)) {
                foreach ($this->externalAttributeSet as $externalAttribute) {
                    if ($externalAttribute['link'] == 'simple' && $key === $externalAttribute['type']) {
                        $aFields = explode(",", $externalAttribute['fields']);
                        $iId =  $externalAttribute['objectClass']::getIdByParameter(
                            $aFields[1],
                            $params[$externalAttribute['type']]
                        );

                        if (count($iId) > 0) {
                            $finalParamList[$key] = $iId[0];
                        } else {
                            $sMessage = static::OBJ_NOT_EXIST;
                            if (!empty($externalAttribute['message'])) {
                                $sMessage = $externalAttribute['message'];
                            }
                            throw new \Exception($sMessage);
                        }
                    } elseif ($externalAttribute['link'] == 'multiple' && $key === $externalAttribute['type']) {

                        $aFields = explode(",", $externalAttribute['fields']);
                        $aDatas = explode(',', $params[$externalAttribute['type']]);
 
                        $tempParamList = array();
                        foreach ($aDatas as $sData) {
                            $sData = trim($sData);
                           
                            $iId =  $externalAttribute['objectClass']::getIdByParameter($aFields[1], $sData);
                            if (count($iId) > 0) {
                                $tempParamList[] = $iId[0];
                            } else {
                                $sMessage = static::OBJ_NOT_EXIST;
                                if (!empty($externalAttribute['message'])) {
                                    $sMessage = $externalAttribute['message'];
                                }
                                throw new \Exception($sMessage);
                            }
                        }
                        $finalParamList[$key] = implode(',', $tempParamList);
                    }
                }
            } else {
                $finalParamList[$key] = $param;
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
        $repository = $this->repository;
        $repository::transco($params);
        
        $paramList = $this->parseObjectParams($params);
        $paramList['object'] = $this->objectName;
        
        $idOfCreatedElement = $repository::create(
            $paramList,
            'api',
            $this->objectBaseUrl . '/update'
        );
        $slug = $repository::getSlugNameById($idOfCreatedElement);
        \Centreon\Internal\Utils\CommandLine\InputOutput::display($slug, true, 'green');
        \Centreon\Internal\Utils\CommandLine\InputOutput::display("Object successfully created", true, 'green');
    }
    
    /**
     * 
     * @param string $object
     * @param string $params
     */
    public function updateAction($object, $params)
    {
        $repository = $this->repository;
        $repository::transco($params);
        $repository::transco($object);
        if (!empty($params)) {
            $paramList = $this->parseObjectParams($params);
        }
        $paramList['object'] = $this->objectName;
        
        $sName = static::renameObject($this->objectName);
        
        $aId = $repository::getListBySlugName($object[$sName]);
        if (count($aId) > 0) {
            $paramList['object_id'] = $aId[0]['id'];
        } else {
            throw new \Exception(static::OBJ_NOT_EXIST);
        }
        $repository::update(
            $paramList,
            'api',
            $this->objectBaseUrl . '/update',
            true,
            false
        );
        \Centreon\Internal\Utils\CommandLine\InputOutput::display("Object successfully updated", true, 'green');
        
    }
    
    /**
     * 
     * @param string $object
     */
    public function deleteAction($object)
    {        
        $repository = $this->repository;
        $repository::transco($object);
        $id = '';
        $sName = static::renameObject($this->objectName);

        $aId = $repository::getListBySlugName($object[$sName]);
        if (count($aId) > 0) {
            $id = $aId[0]['id'];
        } else {
            throw new \Exception(static::OBJ_NOT_EXIST);
        }
        $repository::delete(array($id));
        \Centreon\Internal\Utils\CommandLine\InputOutput::display("Object successfully deleted", true, 'green');
    }
    
    /**
     * Action for duplicate
     *
     */
    public function duplicateAction()
    {
        echo "Not implemented yet";
    }
    
    /**
     * 
     * @param string $sName
     * @return string
     */
    public static function renameObject ($sName)
    {
        if (array_key_exists($sName, static::$aRenameModules)) {
            return static::$aRenameModules[$sName];
        } else {
            return $sName;
        }
    }
}
