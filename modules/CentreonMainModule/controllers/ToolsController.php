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

namespace CentreonMain\Controllers;

use Centreon\Internal\Di;
use Centreon\Internal\Controller;
use Centreon\Models\File;
use CentreonMain\Repository\BasicRepository;
use Centreon\Internal\Exception\Http\BadRequestException;

/**
 * Tools controller
 *
 * @authors Maximilien Bersoult
 * @package Centreon
 * @subpackage Controllers
 */
class ToolsController extends Controller
{
    /**
     *
     * @var type 
     */
    private $centreonPath = "";
    
    public function __construct($request)
    {
        $di = Di::getDefault();
        $this->centreonPath = $di->get('config')->get('global', 'centreon_path');
        parent::__construct($request);
    }
    /**
     * Action for compile LESS
     *
     * @method GET
     * @route @\.css$
     */
    public function lessAction()
    {
        $di = Di::getDefault();
        $router = $di->get('router');
        $route = $router->request()->pathname();
        $response = $router->response();
        
        /* Get path to  */
        $baseUrl = $di->get('config')->get('global', 'base_url');
        $route = preg_replace('!' . $baseUrl . '!', '/', $route, 1);
        $route = str_replace('css', 'less', $route);
        
        /* Remove min */
        $route = str_replace('.min.', '.', $route);
        $centreonPath = realpath($this->centreonPath . '/www/');
        if (false === file_exists($centreonPath . $route)) {
            $this->notFoundAction();
            return;
        }
        
        // Set Options
        $tempDir = $di->get('config')->get('global', 'centreon_generate_tmp_dir');
        $options = array(
            'cache_dir' => $tempDir,
            'use_cache' => false,
            'compress' => false,
            'relativeUrls' => false
        );
        if ("dev" !== $di->get('config')->get('global', 'env')) {
            $options['use_cache'] = true;
            $options['compress'] = true;
        }
        
        /* Response compiled CSS */
        $response->header('Content-Type', 'text/css');
        
        $imgUrl = rtrim($baseUrl, '/') . '/static/centreon/img/login.jpg")';
        $variables = array('login-background-image' => 'url("' . $imgUrl);
        $less_file = array($centreonPath . $route => $route);
        $css_file_name = \Less_Cache::Get($less_file, $options, $variables);
        $compiled = file_get_contents($tempDir . $css_file_name );
        $response->body($compiled);
    }

    /**
     * Action for display image from database
     *
     * @method GET
     * @route /uploads/[*:image][png|jpg|gif|jpeg:format]
     */
    public function imageAction()
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $router = $di->get('router');
        $params = $router->request()->paramsNamed();
        $filename = $params['image'] . $params['format'];
        $query = 'SELECT binary_content, mimetype
            FROM cfg_binaries
            WHERE filename = :filename
                AND filetype = 1';
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':filename', urldecode($filename), \PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        if (false === $row) {
            $this->notFoundAction();
            return;
        }

        /* Write file in filesystem for serve file by http server */
        $filefs = $this->centreonPath . '/www/uploads/images/' . $filename;
        if (false === file_exists($filename)) {
            file_put_contents($filefs, $row['binary_content']);
        }
        $router->response()->header('Content-Type', $row['mimetype']);
        $router->response()->body($row['binary_content']);
        $router->response()->send();
    }

    /**
     * Page 404
     *
     * @method GET
     * @route 404
     */
    public function notFoundAction()
    {
        $di = Di::getDefault();
        $response = $di->get('router')->response();
        $response->code(404);
        $tpl = $di->get('template');
        $tpl->display('404.tpl');
    }
    
    /**
     * Action for uploading files from database
     *
     * @method POST
     * @route /file/upload
     */
    public function fileUploadAction()
    {
        $di = Di::getDefault();
        $dbconn = $di->get('db_centreon');
        $router = $di->get('router');
        
        $uploadedFile = $_FILES['centreonUploadedFile'];
        
        $fileChecksum = md5_file($uploadedFile['tmp_name']);
        $mimetype = mime_content_type($uploadedFile['tmp_name']);
        
        $fileType = "";
        switch($mimetype) {
            default:
            case 'image/jpeg':
            case 'image/jpg':
            case 'image/png':
                $fileType = 'images';
                break;
        }
        
        $query = 'SELECT `checksum` 
            FROM `cfg_binaries`
            WHERE `checksum` = :checksum
            AND `mimetype` = :mimetype';
        $stmt = $dbconn->prepare($query);
        $stmt->bindParam(':checksum', $fileChecksum, \PDO::PARAM_STR);
        $stmt->bindParam(':mimetype', $mimetype, \PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch();
        
        if (false === $row) {
            $di = Di::getDefault();
            $config = $di->get('config');
            $baseUrl = rtrim($config->get('global', 'base_url'), '/').'/uploads/'.$fileType.'/';
            $fileDestination = realpath($this->centreonPath . '/www/uploads/'.$fileType.'/').'/'.$uploadedFile['name'];

            if (move_uploaded_file($uploadedFile['tmp_name'], $fileDestination)) {
                $fileParam = array(
                    'filename' => $uploadedFile['name'],
                    'checksum' => $fileChecksum,
                    'mimetype' => $mimetype,
                    'filetype' => 1,
                    'binary_content' => file_get_contents($fileDestination)
                );
                File::insert($fileParam);
                
                $fileUploadResult = array(
                    'url' => $baseUrl.$uploadedFile['name'],
                    'name' => $uploadedFile['name'],
                    'type' => $mimetype,
                    'size' => filesize($fileDestination),
                    'deleteUrl' => '',
                    'deleteType' => 'DELETE',
                );
                
                // If the file is an image, we need to produce a thumbnail
                if ($fileType == "images") {
                    
                    switch($mimetype) {
                        default:
                        case 'image/jpeg':
                            $imageCreateFunction = 'imagecreatefromjpeg';
                            $imageGenerateFunction = 'imagejpeg';
                            break;
                        case 'image/png':
                            $imageCreateFunction = 'imagecreatefrompng';
                            $imageGenerateFunction = 'imagepng';
                            break;
                        case 'image/gif':
                            $imageCreateFunction = 'imagecreatefromgif';
                            $imageGenerateFunction = 'imagegif';
                            break;
                    }
                    
                    $thumbDestination = realpath($this->centreonPath . '/www/uploads/imagesthumb/').'/'.$uploadedFile['name'];
                    $thumbBaseUrl = rtrim($config->get('global', 'base_url'), '/').'/uploads/imagesthumb/';
                    
                    // Calcul des nouvelles dimensions
                    list($width, $height) = getimagesize($fileDestination);
                    if (($width > 80) || ($height > 80)) {
                        $currentRatio = $width / $height;
                        
                        if ($currentRatio > 1) {
                            $new_width = 80;
                            $new_height = 80 / $currentRatio;
                        } else {
                            $new_width = 80 * $currentRatio;
                            $new_height = 80;
                        }

                        // Redimensionnement
                        $image_p = \imagecreatetruecolor($new_width, $new_height);
                        $image = $imageCreateFunction($fileDestination);
                        \imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
                        $imageGenerateFunction($image_p, $thumbDestination);
                        
                        $fileUploadResult['thumbnailUrl'] = $thumbBaseUrl.$uploadedFile['name'];
                    } else {
                        $fileUploadResult['thumbnailUrl'] = $baseUrl.$uploadedFile['name'];
                    }
                }

                $router->response()->code(200)->json(array("files" => array($fileUploadResult)));
            }
        } else {
            $fileUploadResult = array(
                'error' => _('This file already exist on the server')
            );
            $router->response()->code(409)->json(array('files' => array($fileUploadResult)));
        }
    }
    
    /**
     * 
     * @method get
     * @route /form/help
     */
    public function getFormHelpAction()
    {
        try {
            // Get request params and thowing exception if missing
            $requestParams = $this->getParams('get');
            /*
            if (!isset($requestParams['form'])) {
                throw new BadRequestException('Missing parameter', 'The form parameter is missing');
            }
            if (!isset($requestParams['field'])) {
                throw new BadRequestException('Missing parameter', 'The field parameter is missing');
            }
            
             */
            if (isset($requestParams['form']) && isset($requestParams['field'])) {       
                $fieldHelp = BasicRepository::getFormHelp($requestParams['form'], $requestParams['field']);
                $this->router->response()->json($fieldHelp);
            }
            
        } catch(\Exception $ex) {
            $this->router->response()->code($ex->getCode())->json($ex->getMessage());
        }
    }
}
