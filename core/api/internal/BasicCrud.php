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
use Centreon\Internal\Di;
/**
 * Description of BasicCrud
 *
 * @author lionel
 */
class BasicCrud extends AbstractCommand
{
    
    public $options = array();
    
    protected $paramsToExclude = array();


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
    
    
    /** Get the fields from xml forms for update and create action
     * 
     * @param string $action
     * @param string $module
     */
    public function getFieldsFromForms($action,$module){
        
        $route = "";
        $db = Di::getDefault()->get('db_centreon');
        
        
        
        switch ($action){
            case 'updateAction' : 
                $route = '/'.$module.'/'.$this->objectName.'/update';
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
                foreach($rows as $row){
                    if(!in_array($row['name'], $this->paramsToExclude)){
                        if(!isset($this->options['updateAction'][$row['normalized_name']])){
                            $this->options['updateAction'][$row['normalized_name']] = array(
                                'functionParams' => 'params',
                                'help' => $row['help'],
                                'type' => 'string',
                                'toTransform' => $row['name'],
                                'multiple' => '',
                                'required' => false
                            );
                        }
                    }
                }
                break;
            case 'createAction' : 
                $route = '/'.$module.'/'.$this->objectName.'/add';
                $sql = 'select ff.* from cfg_forms_wizards fw
                        inner join cfg_forms_steps fs on fs.wizard_id = fw.wizard_id
                        inner join cfg_forms_steps_fields_relations fsfr on fsfr.step_id = fs.step_id
                        inner join cfg_forms_fields ff on ff.field_id = fsfr.field_id
                        where fw.route = :route and ff.normalized_name != "" and ff.normalized_name is not null';
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':route', $route, \PDO::PARAM_STR);
                $stmt->execute();
                $rows = $stmt->fetchAll();
                foreach($rows as $row){
                    if(!in_array($row['name'], $this->paramsToExclude)){
                        if(!isset($this->options['createAction'][$row['normalized_name']])){
                            $this->options['createAction'][$row['normalized_name']] = array(
                                'functionParams' => 'params',
                                'help' => $row['help'],
                                'type' => 'string',
                                'toTransform' => $row['name'],
                                'multiple' => '',
                                'required' => $row['mandatory']
                            );
                        }
                    }
                }
                
                /*** add default values from global form ***/
                $route = '/'.$module.'/'.$this->objectName.'/update';
                $sql = 'select ff.* from cfg_forms f
                        inner join cfg_forms_sections fs on fs.form_id = f.form_id
                        inner join cfg_forms_blocks fb on fb.section_id = fs.section_id
                        inner join cfg_forms_blocks_fields_relations fbfr on fbfr.block_id = fb.block_id
                        inner join cfg_forms_fields ff on ff.field_id = fbfr.field_id
                        where f.route = :route and ff.normalized_name != "" and ff.normalized_name is not null and ff.default_value != "" and ff.default_value is not null';
                $stmt = $db->prepare($sql);
                $stmt->bindParam(':route', $route, \PDO::PARAM_STR);
                $stmt->execute();
                $rowsDefault = $stmt->fetchAll();
                
                foreach($rowsDefault as $rowDefault){
                    if(!isset($this->options['createAction'][$rowDefault['normalized_name']])){
                        $this->options['createAction'][$rowDefault['normalized_name']] = array(
                            'functionParams' => 'params',
                            'help' => '',
                            'type' => 'string',
                            'toTransform' => $rowDefault['name'],
                            'multiple' => '',
                            'required' => false,
                            'defaultValue' => $rowDefault['default_value']
                        );
                    }
                }
                
                break;
            default : 
                break;
        }
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
        
       //$objectSlug = $repository::getIdFromUnicity($this->parseObjectParams($objectSlug));
        
        $aId = $repository::getListBySlugName($objectSlug[$this->objectName]);
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

        /*
        var_dump($params);
        die;
         */
        foreach ($params as $key => $param) { 
            if (in_array($key, $aFieldAttribute)) { 
                foreach ($this->externalAttributeSet as $externalAttribute) {
                    if ($externalAttribute['link'] == 'simple' && $key === $externalAttribute['type']) {
                        $aFields = explode(",", $externalAttribute['fields']);
                        $iId =  $externalAttribute['objectClass']::getIdByParameter($aFields[1], $params[$externalAttribute['type']]);

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
 
                        foreach ($aDatas as $sData) {
                            $sData = trim($sData);
                           
                            $iId =  $externalAttribute['objectClass']::getIdByParameter($aFields[1], $sData);
                            if (count($iId) > 0) {
                                $finalParamList[$key] = $iId[0];
                            } else {
                                $sMessage = static::OBJ_NOT_EXIST;
                                if (!empty($externalAttribute['message'])) {
                                    $sMessage = $externalAttribute['message'];
                                }
                                throw new \Exception($sMessage);
                            }
                        }
                    }
                }
                 
            } else {
               $finalParamList[$key] = $param; 
            }
        }
        /*
        var_dump($finalParamList);
        die;
         */
        return $finalParamList;
    }
    
    
    /**
     * 
     * @param string $params
     */
    public function createAction($params)
    {
        $repository = $this->repository;
        $paramList = $this->parseObjectParams($params);
        $paramList['object'] = $this->objectName;

        $idOfCreatedElement = $repository::create(
                    $paramList,
                    'api',
                    $this->objectBaseUrl . '/update'
                );
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

        $paramList = $this->parseObjectParams($params);
        $paramList['object'] = $this->objectName;

        $aId = $repository::getListBySlugName($object[$this->objectName]);
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
     * @param type $object
     */
    public function deleteAction($object)
    {
        $repository = $this->repository;
        $id = '';
        //$id = $repository::getIdFromUnicity($this->parseObjectParams($object));
        $aId = $repository::getListBySlugName($object[$this->objectName]);
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
}
