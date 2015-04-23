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
     * @method post
     * @route /form/help
     */
    public function getFormHelpAction()
    {
        try {
            // Get request params and thowing exception if missing
            $requestParams = $this->getParams('post');
            if (!isset($requestParams['form'])) {
                throw new BadRequestException('Missing parameter', 'The form parameter is missing');
            }
            if (!isset($requestParams['field'])) {
                throw new BadRequestException('Missing parameter', 'The field parameter is missing');
            }
            
            $fieldHelp = BasicRepository::getFormHelp($requestParams['form'], $requestParams['field']);
            $this->router->response()->json($fieldHelp);
            
        } catch(\Exception $ex) {
            $this->router->response()->code($ex->getCode())->json($ex->getMessage());
        }
    }
}
