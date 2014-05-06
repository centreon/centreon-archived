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
namespace CentreonCustomview\Controllers;

use \CentreonCustomview\Repository\CustomviewRepository,
    \CentreonCustomview\Repository\WidgetRepository;

/**
 * Widget controller
 *
 * @author Sylvestre Ho
 * @package Centreon
 * @subpackage Controllers
 */
class WidgetController extends \Centreon\Internal\Controller
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
        $params = $this->getParams();
        $this->widgetId = $params['id'];
        $data = WidgetRepository::getWidgetData($params['id']);
        $commonName = str_replace(' ', '', ucwords(str_replace('-', ' ', $data['shortname'])));
        $filename = "../widgets/".$commonName."/".$data['url'];
        if (file_exists($filename)) {
           include_once $filename;
        } else {
            throw new \Centreon\Internal\Exception(sprintf('Could not find file %s', $filename));
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
        return \Centreon\Internal\Di::getDefault()->get('template');
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
        throw new \Centreon\Internal\Exception('No user session defined');
    }

    /**
     * Get cache
     *
     * @return \Centreon\Internal\Cache
     */
    protected function getCache()
    {
        return \Centreon\Internal\Di::getDefault()->get('cache');
    }

    /**
     * Get configuration database
     *
     * @return \Centreon\Internal\Db
     */
    protected function getConfigurationDb()
    {
        return \Centreon\Internal\Di::getDefault()->get('db_centreon');
    }

    /**
     * Get monitoring database
     *
     * @return \Centreon\Internal\Db
     */
    protected function getMonitoringDb()
    {
        return \Centreon\Internal\Di::getDefault()->get('db_centstorage');
    }
}
