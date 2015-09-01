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

namespace CentreonMain\Repository;


use Centreon\Internal\Di;
use Centreon\Internal\Exception;
use Centreon\Internal\Form\Validators\Validator;
use CentreonMain\Events\PreSave as PreSaveEvent;
use CentreonMain\Events\PostSave as PostSaveEvent;
use Centreon\Internal\CentreonSlugify;

/**
 * Abstact class for configuration repository
 *
 * @version 3.0.0
 * @author Sylvestre Ho <sho@centreon.com>
 */
abstract class FormRepository extends ListRepository
{
    
    /**
     *
     * @var array
     */
    public static $exposedParams = array();
    
    /**
     * Get list of objects
     *
     * @param string $searchStr
     * @return array
     */
    public static function getFormList($searchStr = "", $objectId = null, $additionalGetParams = null)
    {
        if (!empty(static::$secondaryObjectClass)) {
            $class = static::$secondaryObjectClass;
        } else {
            $class = static::$objectClass;
        }
        
        $idField = $class::getPrimaryKey();
        $uniqueField = $class::getUniqueLabelField();
        $filters = array(
            $uniqueField => '%'.$searchStr.'%'
        );

        $columns = $class::getColumns();
        if (in_array(static::ORGANIZATION_FIELD, $columns)) {
           $filters[static::ORGANIZATION_FIELD] = Di::getDefault()->get('organization');
        }

        if(!empty($additionalGetParams)){
            foreach($additionalGetParams as $key=>$additionalGetParam){
                if(isset(static::$exposedParams[$key])){
                    if(in_array(static::$exposedParams[$key], $columns)){
                        $filters[static::$exposedParams[$key]] = $additionalGetParam;
                    }
                }
            }
        }
        
        $list = $class::getList(array($idField, $uniqueField), -1, 0, null, "ASC", $filters, "AND");
        $finalList = array();
        foreach ($list as $obj) {
            $finalList[] = array(
                "id" => $obj[$idField],
                "text" => $obj[$uniqueField]
            );
        }
        return $finalList;
    }
    
    /**
     * 
     * @param type $givenParameters
     * @param type $origin
     * @param type $route
     */
    protected static function validateForm($givenParameters, $origin = "", $route = "", $validateMandatory = true)
    {
        $formValidator = new Validator($origin, array('route' => $route, 'params' => array(), 'version' => '3.0.0'));
        
        if (is_a($givenParameters, '\Klein\DataCollection\DataCollection')) {
            $givenParameters = $givenParameters->all();
        }
        
        $formValidator->validate($givenParameters, $validateMandatory);
    }

    /**
     * Generic create action
     *
     * @param array $givenParameters
     * @return int id of created object
     */
    public static function create($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
        $id = null;
        $db = Di::getDefault()->get('db_centreon');

        $extraParameters = array();
        foreach ($givenParameters as $name => $value) {
            $explodedName = explode("__", $name);
            if (count($explodedName) == 2) {
                $extraParameters[$explodedName[0]][$explodedName[1]] = $value;
                unset($givenParameters[$name]);
            }
        }

        $events = Di::getDefault()->get('events');
        $preSaveEvent = new PreSaveEvent('create', $givenParameters, $extraParameters);
        $events->emit('centreon-main.pre.save', array($preSaveEvent));

        try {
            if ($validate) {
                self::validateForm($givenParameters, $origin, $route, $validateMandatory);
            }
            
            if (isset($givenParameters['password']) && isset($givenParameters['password2'])) {
                $givenParameters['password'] = $givenParameters['password2'];
            }
        
            $class = static::$objectClass;
            $pk = $class::getPrimaryKey();
            $columns = $class::getColumns();
            $insertParams = array();
            $givenParameters[static::ORGANIZATION_FIELD] = Di::getDefault()->get('organization');
            
            $db->beginTransaction();
            
            $sField = $class::getUniqueLabelField();
            if (isset($sField) 
                    && isset($givenParameters[$sField]) 
                    && !is_null($class::getSlugField())
                    && (!isset($givenParameters[$class::getSlugField()])
                    || (isset($givenParameters[$class::getSlugField()]) && is_null($givenParameters[$class::getSlugField()])))) {
                
                $oSlugify = new CentreonSlugify($class, get_called_class());
                $sSlug = $oSlugify->slug($givenParameters[$sField]);
                $givenParameters[$class::getSlugField()] = $sSlug;
            }

            foreach ($givenParameters as $key => $value) {
                if (in_array($key, $columns)) {
                    if (!is_array($value)) {
                        $value = trim($value);
                        if (!empty($value) || $value === "0" || $value === 0) {
                            $insertParams[$key] = trim($value);
                        }
                    }
                }
            }
            
            $id = $class::insert($insertParams);
            if (is_null($id)) {
                $db->rollback();
                throw new Exception('Could not create object');
            }
            foreach (static::$relationMap as $k => $rel) {
                if (!isset($givenParameters[$k])) {
                    continue;
                }
                $arr = explode(',', ltrim($givenParameters[$k], ','));

                foreach ($arr as $relId) {
                    $relId = trim($relId);
                    if (is_numeric($relId)) {
                        if ($rel::$firstObject == static::$objectClass) {
                            $rel::insert($id, $relId);
                        } else {
                            $rel::insert($relId, $id);
                        }
                    } elseif (!empty($relId)) {
                        $complexeRelId = explode('_', $relId);
                        if ($rel::$firstObject == static::$objectClass) {
                            $rel::insert($id, $complexeRelId[1], $complexeRelId[0]);
                        }
                    }
                }
                unset($givenParameters[$k]);
            }
            $db->commit();
        
            if (method_exists(get_called_class(), 'postSave')) {
                static::postSave($id, 'add', $givenParameters);
            }
        } catch (\PDOException $e) {
            $db->rollback();
            throw new Exception($e->getMessage());
        } catch (\Exception $e) {
            throw new Exception($e->getMessage());
        }

        $givenParameters['object_id'] = $id;
        $postSaveEvent = new PostSaveEvent('create', $givenParameters, $extraParameters);
        $events->emit('centreon-main.post.save', array($postSaveEvent));

        return $id;
    }
    
    public static function getIdBySlugName($slug){
        $class = static::$objectClass;
        $slugField = $class::getSlugField();
        if(!is_null($slug)){
            try{
                $id = $class::getIdByParameter($slugField,array($slug));
            } catch (Exception $ex) {

            }
            if(!empty($id)){
                return $id[0];
            }
            
        }
        return null;
    }
    
    public static function getSlugNameById($id){
        $class = static::$objectClass;
        $slug = $class::getSlugField();
        $object = null;
        if(!is_null($slug)){
            try{
                $object = $class::getParameters($id, $slug);
            } catch (Exception $ex) {

            }
            
        }
        if(!empty($object)){
            return $object[$slug];
        }
        return "";
    }
    
    public static function disable($givenParameters)
    {
        static::update($givenParameters, '', '', false, false);
    }

    /**
     * Generic update function
     *
     * @param array $givenParameters
     * @throws \Centreon\Internal\Exception
     */
    public static function update($givenParameters, $origin = "", $route = "", $validate = true, $validateMandatory = true)
    {
        if ($validate) {
            self::validateForm($givenParameters, $origin, $route, $validateMandatory);
        }
        
        if (isset($givenParameters['password']) && isset($givenParameters['password2'])) {
            $givenParameters['password'] = $givenParameters['password2'];
        }

        $extraParameters = array();
        foreach ($givenParameters as $name => $value) {
            $explodedName = explode("__", $name);
            if (count($explodedName) == 2) {
                $extraParameters[$explodedName[0]][$explodedName[1]] = $value;
                unset($givenParameters[$name]);
            }
        }

        $events = Di::getDefault()->get('events');
        $preSaveEvent = new PreSaveEvent('update', $givenParameters, $extraParameters);
        $events->emit('centreon-main.pre.save', array($preSaveEvent));
        
        $class = static::$objectClass;
        $pk = $class::getPrimaryKey();
        $givenParameters[$pk] = $givenParameters['object_id'];
        
        $sField = $class::getUniqueLabelField();
        if (isset($givenParameters[$sField]) 
                && !is_null($class::getSlugField())
                && (
                    !isset($givenParameters[$class::getSlugField()])
                    || (isset($givenParameters[$class::getSlugField()]) && is_null($givenParameters[$class::getSlugField()])))
            ) {
            $oSlugify = new CentreonSlugify($class, get_called_class());
            $sSlug = $oSlugify->slug($givenParameters[$sField], $givenParameters['object_id']);
            $givenParameters[$class::getSlugField()] = $sSlug;
        }
                       
        if (!isset($givenParameters[$pk])) {
            throw new \Exception('Primary key of object is not defined');
        }
        $db = Di::getDefault()->get('db_centreon');
        $id = $givenParameters[$pk];
        unset($givenParameters[$pk]);
        foreach (static::$relationMap as $k => $rel) {
            try {
                if (!isset($givenParameters[$k])) {
                    continue;
                }
                try {
                    if ($rel::$firstObject == static::$objectClass) {
                        $rel::delete($id);
                    } else {
                        $rel::delete(null, $id);
                    }
                } catch (Exception $e) {
                    ; // it's okay if nothing got deleted
                }
                $arr = explode(',', ltrim($givenParameters[$k], ','));
                $db->beginTransaction();

                foreach ($arr as $relId) {
                    $relId = trim($relId);
                    if (is_numeric($relId)) {
                        if ($rel::$firstObject == static::$objectClass) {
                            $rel::insert($id, $relId);
                        } else {
                            $rel::insert($relId, $id);
                        }
                    } elseif (!empty($relId)) {
                        $complexeRelId = explode('_', $relId);
                        if ($rel::$firstObject == static::$objectClass) {
                            $rel::insert($id, $complexeRelId[1], $complexeRelId[0]);
                        }
                    }
                }
                $db->commit();
                unset($givenParameters[$k]);
            } catch (Exception $e) {
                throw new Exception('Error while updating', 0, $e);
            }
        }
        $columns = $class::getColumns();
        $updateValues = array();
        foreach ($givenParameters as $key => $value) {
            if (in_array($key, $columns)) {
                if (is_string($value)) {
                    $updateValues[$key] = trim($value);
                } else {
                    $updateValues[$key] = $value;
                }
            }
        }
        
        $class::update($id, $updateValues);
       
        $postSaveEvent = new PostSaveEvent('update', $givenParameters, $extraParameters);
        $events->emit('centreon-main.post.save', array($postSaveEvent));
 
        if (method_exists(get_called_class(), 'postSave')) {
            static::postSave($id, 'update', $givenParameters);
        }
    }

    /**
     * Delete an object
     *
     * @param array $ids | array of ids to delete
     */
    public static function delete($ids)
    {
        $objClass = static::$objectClass;
        foreach ($ids as $id) {
            if (method_exists(get_called_class(), 'preSave')) {
                static::preSave($id, 'delete', array());
            }
            
            $objClass::delete($id);
            
            if (method_exists(get_called_class(), 'postSave')) {
                static::postSave($id, 'delete', array());
            }
        }
    }

    /**
     * Duplicate a object
     *
     * @param array $listDuplicate
     */
    public static function duplicate($listDuplicate)
    {
        $objClass = static::$objectClass;
        foreach ($listDuplicate as $id => $nb) {
            $objClass::duplicate($id, $nb);
        }
    }
    
    /**
     * Get list of objects
     *
     * @param string $searchStr
     * @return array
     */
    public static function getListBySlugName($searchStr = "")
    {
        if (!empty(static::$secondaryObjectClass)) {
            $class = static::$secondaryObjectClass;
        } else {
            $class = static::$objectClass;
        }
        
        $idField = $class::getPrimaryKey();
        $slugField = $class::getSlugField();
        $filters = array(
            $slugField => $searchStr
        );

        $columns = $class::getColumns();
        if (in_array(static::ORGANIZATION_FIELD, $columns)) {
           $filters[static::ORGANIZATION_FIELD] = Di::getDefault()->get('organization');
        }
       

        $list = $class::getList(array($idField, $slugField), -1, 0, null, "ASC", $filters, "AND");
        $finalList = array();
        foreach ($list as $obj) {
            $finalList[] = array(
                "id" => $obj[$idField],
                "text" => $obj[$slugField]
            );
        }
        return $finalList;
    }
}
