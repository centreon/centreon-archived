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

namespace CentreonCustomview\Controllers;

use CentreonCustomview\Repository\CustomviewRepository;
use CentreonCustomview\Repository\WidgetRepository;
use Centreon\Internal\Controller;
use Centreon\Internal\Di;
use Centreon\Internal\Exception;

/**
 * Widget controller
 *
 * @author Sylvestre Ho
 * @package Centreon
 * @subpackage Controllers
 */
class WidgetController extends Controller
{
    /**
     * Unique id of the widget
     *
     * @var $widgetId
     */
    protected $widgetId;

    /**
     * Action for widget action
     *
     * @method get
     * @route /widget/[i:id]
     */
    public function widgetAction()
    {
        $path = rtrim(Di::getDefault()->get('config')->get('global', 'centreon_path'));
        $params = $this->getParams();
        $this->widgetId = $params['id'];
        $data = WidgetRepository::getWidgetData($params['id']);
        $commonName = str_replace(' ', '', ucwords(str_replace('-', ' ', $data['shortname'])));
        $dir = glob($path . "/widgets/" . $commonName . "Widget/");
        if (!isset($dir[0])) {
            $dir = glob($path . "/modules/*Module/widgets/" . $commonName ."Widget/");
        }
        if (!isset($dir[0])) {
            throw new Exception(sprintf('Could not find directory %s', $commonName."Widget"));
        }
        $filename = $dir[0] . $commonName . "Widget.php";
        if (file_exists($filename)) {
            include_once $filename;
        } else {
            throw new Exception(sprintf('Could not find file %s', $filename));
        }
    }

    /**
     * Get widget params
     *
     * @return array
     */
    protected function getWidgetParams()
    {
        return WidgetRepository::getWidgetPreferences($this->widgetId);
    }

    /**
     * Get template object
     *
     * @return \Centreon\Internal\Template
     */
    protected function getTemplate()
    {
        return Di::getDefault()->get('template');
    }

    /**
     * Get user
     *
     * @return \Centreon\Internal\User
     */
    protected function getUser()
    {
        if (isset($_SESSION['user'])) {
            return $_SESSION['user'];
        }
        throw new Exception('No user session defined');
    }

    /**
     * Get cache
     *
     * @return \Centreon\Internal\Cache
     */
    protected function getCache()
    {
        return Di::getDefault()->get('cache');
    }

    /**
     * Get configuration database
     *
     * @return \Centreon\Internal\Db
     */
    protected function getConfigurationDb()
    {
        return Di::getDefault()->get('db_centreon');
    }

    /**
     * Get monitoring database
     *
     * @return \Centreon\Internal\Db
     */
    protected function getMonitoringDb()
    {
        return Di::getDefault()->get('db_centreon');
    }
}
