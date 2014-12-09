<?php

/*
 * Copyright 2005-2014 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of MERETHIS choice, provided that 
 * MERETHIS also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

namespace Centreon\Api\Rest;

use Centreon\Internal\Exception;

/**
 * Description of BasicFormApi
 *
 * @author lionel
 */
class BasicFormApi extends \Centreon\Internal\Api
{
    /**
     *
     * @var type 
     */
    protected $liteAttributesSet = array();
    
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
    public static $relationMap = array();
    
    /**
     *
     * @var type 
     */
    public static $simpleRelationMap = array();
    
    /**
     * 
     */
    const OBJ_NOT_EXIST = 'Object not in database.';
    
    /**
     * 
     * @param type $request
     * @throws Exception
     */
    public function __construct($request)
    {
        parent::__construct($request);
        if (is_null($this->repository)) {
            throw new Exception('Repository unspecified');
        }
        $repository = $this->repository;
        $repository::setRelationMap(static::$relationMap);
        $repository::setObjectName($this->objectName);
        $repository::setObjectClass($this->objectClass);
        if (!empty($this->secondaryObjectClass)) {
            $repository::setSecondaryObjectClass($this->secondaryObjectClass);
        }
        $this->objectBaseUrl = '/' . static::$moduleShortName . '/' . $this->objectName;
    }
    
    /**
     * 
     * @param type $set
     */
    public function listAction($set = '*')
    {
        // 
        $params = $this->getParams();
        $headers = $this->request->headers();
        $repository = $this->repository;
        $obj = $this->objectClass;
        $objPrimaryKey = $obj::getPrimaryKey();
        $objLink = $this->objectBaseUrl . '/[i:id]';
        
        //
        $count = (isset($params['count'])) ? $params['count'] : 25;
        $offset = (isset($params['offset'])) ? $params['offset'] : 0;
        $fields = (isset($params['fields'])) ? $params['fields'] : $set;
        $list = $repository::getList($fields, $count, $offset);
        
        // 
        foreach ($list as &$singleObject) {
            $finalLink = $this->router->getPathFor($objLink, array('id' => $singleObject[$objPrimaryKey]));
            $singleObject['href'] = 'http://' . $headers['host'] . $finalLink;
        }
        $this->sendJsonApiResponse($this->objectName, $list);
    }
    
    /**
     * 
     */
    public function viewAction()
    {
        // 
        $params = $this->getParams();
        $headers = $this->request->headers();
        $hostUrl = 'http://' . $headers['host'];
        $repository = $this->repository;
        $obj = $this->objectClass;
        $objPrimaryKey = $obj::getPrimaryKey();
        $objLink = $this->objectBaseUrl . '/[i:id]';
        
        //
        $fields = (isset($params['fields'])) ? $params['fields'] : '*';
        $linkedObjects = (isset($params['object'])) ? explode(',', $params['object']) : array();
        $ids = explode(',', $params['id']);
        
        try {
            if (count($ids) > 1) {
                $object = $repository::getList($fields, -1, 0, null, 'asc', array($objPrimaryKey => $ids));
                foreach ($object as &$singleObject) {
                    $singleObject['links'] = $this->getLinkedObjects($singleObject[$objPrimaryKey], $linkedObjects);
                    $finalLink = $this->router->getPathFor($objLink, array('id' => $singleObject[$objPrimaryKey]));
                    $singleObject['href'] = $hostUrl . $finalLink;
                }
            } else {
                $object = $repository::load($params['id'], $fields);
                $object['links'] = $this->getLinkedObjects($params['id'], $linkedObjects);
                $finalLink = $this->router->getPathFor($objLink, array('id' => $object[$objPrimaryKey]));
                $object['href'] = $hostUrl . $finalLink;
            }

            // 
            $links = array();
            foreach ($linkedObjects as $linkedObject) {
                $links[$this->objectName . '.' . $linkedObject] = $hostUrl . "/$linkedObject/" . '{' . $linkedObject . '}';
            }

            // aaaa
            $this->sendJsonApiResponse($this->objectName, $object, $links);
        } catch (Exception $ex) {
            if ($ex->getMessage() === static::OBJ_NOT_EXIST) {
                $this->router->response()->code(404);
            } else {
                $this->router->response()->code(500);
            }
        }
    }
    
    /**
     * 
     */
    public function createAction()
    {
        // 
        $params = $this->getParams('post');
        
        try {
            if (isset($params[$this->objectName])) {
                $repository = $this->repository;
                $objectParams = $params[$this->objectName];

                if (isset($objectParams[0])) {
                    foreach ($objectParams as $singleObjectParams) {
                        $idOfCreatedElement = $repository::create($singleObjectParams);
                        $object[] = $repository::load($idOfCreatedElement);
                    }
                } else {
                    $idOfCreatedElement = $repository::create($objectParams);
                    $object = $repository::load($idOfCreatedElement);
                }

                $this->sendJsonApiResponse($this->objectName, $object);
            } else {
                $this->router->response()->code(400);
            }
        } catch (Exception $ex) {
            $this->router->response()->code(500);
        }
        
    }

    /**
     * 
     */
    public function updateAction()
    {
        $params = $this->getParams();
        
        try {
            if (isset($params['id'])) {
                $repository = $this->repository;
                $objectParams = $params[$this->objectName];
                
                if (isset($objectParams[0])) {
                    foreach ($objectParams as $singleObjectParams) {
                        $singleObjectParams['object_id'] = $params['id'];
                        $repository::update($singleObjectParams);
                    }
                } else {
                    $objectParams['object_id'] = $params['id'];
                    $repository::update($objectParams);
                }
                
                $this->router->response()->code(204);
            } else {
                $this->router->response()->code(400);
            }
        } catch (Exception $ex) {
            if ($ex->getMessage() === static::OBJ_NOT_EXIST) {
                $this->router->response()->code(404);
            } else {
                $this->router->response()->code(500);
            }
        }
    }
    
    /**
     * 
     */
    public function deleteAction()
    {
        // 
        $params = $this->getParams();
        $repository = $this->repository;
        
        try {
            $ids = explode(',', $params['id']);
            $repository::delete($ids);
        } catch (Exception $ex) {
            
        }
        
        $this->router->response()->code(204);
    }
    
    /**
     * 
     */
    public function deleteRelationsAction()
    {
        // 
        $params = $this->getParams();
        $repository = $this->repository;
        
        $ids = explode(',', $params['id']);
        if (count($ids) > 1) {
            $this->router->response()->code(401);
        }
        
        $repository::update($ids);
        
        $this->router->response()->code(204);
    }
    
    /**
     * 
     * @param type $linkedObjects
     */
    private function getLinkedObjects($objectId, $linkedObjects = array())
    {
        $linked = array();
        $repository = $this->repository;
        
        foreach ($linkedObjects as $linkedObject) {
            if (isset(static::$relationMap[$linkedObject])) {
                $relClass = static::$relationMap[$linkedObject];
                $list = $repository::getRelations($relClass, $objectId);
                
                $fList = array();
                foreach ($list as $obj) {
                    $fList[] = $obj['id'];
                }
                
            } elseif (isset(static::$simpleRelationMap[$linkedObject])) {
                $fList = $repository::getSimpleRelation(static::$simpleRelationMap[$linkedObject], $linkedObject, $objectId);
            }
            
            $linked[$linkedObject] = $fList;
        }
        
        return $linked;
    }
}
