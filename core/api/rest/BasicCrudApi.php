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

namespace Centreon\Api\Rest;

use Centreon\Internal\Exception;
use Centreon\Internal\Api;
use Centreon\Internal\Module\Informations;
use Centreon\Internal\Exception\Validator\MissingParameterException;
use Centreon\Internal\Exception\Http\BadRequestException;
use Centreon\Internal\Exception\HttpException;

/**
 * Description of BasicCrudApi
 *
 * @author lionel
 */
class BasicCrudApi extends Api
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
     * @param type $request
     * @throws Exception
     */
    public function __construct($request)
    {
        parent::__construct($request);
        $this->parseManifest();
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

        $rc = new \ReflectionClass(get_class($this));
        $moduleName = Informations::getModuleFromPath($rc->getFileName());
        static::$moduleShortName = Informations::getModuleSlugName($moduleName);

        $this->objectBaseUrl = '/' . static::$moduleShortName . '/' . $this->objectName;
    }
    
    /**
     * 
     * @param string $manifestFile
     */
    private function parseManifest()
    {
        $reflector = new \ReflectionClass($this);
        $manifestFile = dirname($reflector->getFileName()) . '/' . $this->objectName . 'Manifest.json';
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
     * @method GET
     * @route /{object}
     * @auth
     */
    public function listAction()
    {
        $headers = $this->request->headers();
        $version = null;
        if (isset($headers['centreon-version'])) {
            $version = trim($headers['centreon-version']);
        } /* mode strict */
        $calledMethod = '\\' . get_called_class() . '::' . __FUNCTION__;
        static::executeRoute($calledMethod, $version);
    }
    
    /**
     * @api /{object}
     * @method GET
     * @since 3.0.0
     */
    public function list300Action()
    {
        $headers = $this->request->headers();
        $params = $this->getParams();
        $repository = $this->repository;
        $objLink = $this->objectBaseUrl . '/[i:id]';
        
        //
        $count = (isset($params['count'])) ? $params['count'] : 25;
        $offset = (isset($params['offset'])) ? $params['offset'] : 0;
        $fields = (isset($params['fields'])) ? $params['fields'] : $this->liteAttributesSet;
        $list = $repository::getList($fields, $count, $offset);
        
        $this->normalizeParams($list);
        
        foreach ($list as &$singleObject) {
            $finalLink = $this->router->getPathFor($objLink, array('id' => $singleObject['id']));
            $singleObject['href'] = 'http://' . $headers['host'] . $finalLink;
        }
        $this->sendJsonApiResponse($this->objectName, $list);
    }
    
    /**
     * @api /{object}
     * @method GET
     * @since 3.1.0
     */
    protected function list310Action()
    {
        echo "aaaaaaa";
    }


    /**
     * 
     */
    private function viewObject()
    {
        $headers = $this->request->headers();
        
        $params = $this->getParams();
        $hostUrl = 'http://' . $headers['host'];
        $repository = $this->repository;
        $obj = $this->objectClass;
        $objPrimaryKey = $obj::getPrimaryKey();
        $objLink = $this->objectBaseUrl . '/[i:id]';

        //
        $fields = (isset($params['fields'])) ? $params['fields'] : '*';
        $linkedObjects = (isset($params['linkedobject'])) ? explode(',', $params['linkedobject']) : array();
        $ids = explode(',', $params['id']);

        try {
            if (count($ids) > 1) {
                $object = $repository::getList($fields, -1, 0, null, 'asc', array($objPrimaryKey => $ids));
                $this->normalizeParams($object);
                foreach ($object as &$singleObject) {
                    $singleObject['links'] = $this->getLinkedObjects($singleObject['id'], $linkedObjects);
                    $finalLink = $this->router->getPathFor($objLink, array('id' => $singleObject['id']));
                    $singleObject['href'] = $hostUrl . $finalLink;
                }
            } else {
                $object = $repository::load($params['id'], $fields);
                $this->normalizeSingleSet($object);
                $object['links'] = $this->getLinkedObjects($params['id'], $linkedObjects);
                $finalLink = $this->router->getPathFor($objLink, array('id' => $object['id']));
                $object['href'] = $hostUrl . $finalLink;
            }

            //
            $links = array();
            foreach ($linkedObjects as $linkedObject) {
                $links[$this->objectName . '.' . $linkedObject] =
                    $hostUrl . "/$linkedObject/" . '{' . $linkedObject . '}';
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
     * @method GET
     * @route /{object}/[:id]
     * @auth
     */
    public function viewAction()
    {
        $this->viewObject();
    }

    /**
     * @method GET
     * @route /{object}/[:id]/links/[:linkedobject]
     * @auth
     */
    public function viewWithRelationsAction()
    {
        $this->viewObject();
    }

    /**
     * 
     * @method POST
     * @route /{object}
     * @auth
     */
    public function createAction()
    {
        //
        $params = $this->getParams();
        $apiResourceObjects = json_decode($params['data'], true);

        try {
            $object = array();
            $repository = $this->repository;
            foreach ($apiResourceObjects as $apiResourceObject) {
                // If the resource type param is the right one we processed
                if (!isset($apiResourceObject['type'])) {
                    throw new MissingParameterException("type is not provided", 400);
                }
                if ($apiResourceObject['type'] == $this->objectName) {
                    unset($apiResourceObject['type']);
                    $idOfCreatedElement = $repository::create(
                        $apiResourceObject,
                        'api',
                        $this->objectBaseUrl . '/update'
                    );
                    $object[] = $repository::load($idOfCreatedElement);
                } else {
                    throw new BadRequestException('Wrong Type', 'The type you set does not match expected');
                }
            }

            $this->sendJsonApiResponse($this->objectName, $object);
        } catch (MissingParameterException $ex) {
            $this->router->response()->code(400)->json($ex->getMessage());
        } catch (\PDOException $ex) {
            if ($ex->getCode() == 23000) {
                $this->router->response()->code(409);
            }
        } catch (HttpException $ex) {
            $this->router->response()->code($ex->getCode())->json($ex->getMessage());
        } catch (Exception $ex) {
            $this->router->response()->code(500);
        }
    }
    
    /**
     * @method PUT
     * @route /{object}/[:id]
     * @auth
     */
    public function fullUpdateAction()
    {
        
    }
    
    /**
     * @method PATCH
     * @route /{object}/[:id]
     * @auth
     */
    public function partialUpdateAction()
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
     * @method PATCH
     * @route /{object}/[:id]/links/[:linkedobject]
     * @auth
     */
    public function updateRelationsAction()
    {
        
    }
    
    /**
     * @method DELETE
     * @route /{object}/[:id]
     * @auth
     */
    public function deleteAction()
    {
        //
        $params = $this->getParams();
        $repository = $this->repository;
        $returnCode = 204;
        
        try {
            $ids = explode(',', $params['id']);
            $repository::delete($ids);
        } catch (Exception $ex) {
            if ($ex->getMessage() === static::OBJ_NOT_EXIST) {
                $returnCode = 404;
            } else {
                $returnCode = 500;
            }
        }
        
        $this->router->response()->code($returnCode);
    }
    
    /**
     * 
     * @param type $linkedObjects
     */
    private function getLinkedObjects($objectId, $linkedObjects = array(), $asResource = false)
    {
        $linked = array();
        $repository = $this->repository;
        
        foreach ($linkedObjects as $linkedObject) {
            $fList = array();
            if (isset($this->relationMap[$linkedObject])) {
                $relClass = $this->relationMap[$linkedObject];
                $list = $repository::getRelations($relClass, $objectId);
                
                foreach ($list as $obj) {
                    if ($asResource) {
                        $fList[] = $obj;
                    } else {
                        $fList[] = $obj['id'];
                    }
                }
                
            } elseif (isset($this->simpleRelationMap[$linkedObject])) {
                $fList = $repository::getSimpleRelation(
                    $this->simpleRelationMap[$linkedObject],
                    $linkedObject,
                    $objectId
                );
            }
            
            if (count($fList) > 0) {
                $linked[$linkedObject] = $fList;
            }
        }
        
        return $linked;
    }
}
