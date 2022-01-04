<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
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
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace Centreon\Tests\Resources\Traits;

use Pimple\Container;
use Centreon\ServiceProvider;

/**
 * Trait with extension methods for Webservice testing
 *
 * @author Centreon
 * @version 1.0.0
 * @package centreon-license-manager
 * @subpackage test
 */
trait WebserviceTrait
{
    /**
     * Set up webservice service in container
     *
     * <code>
     * public function setUp()
     * {
     *     $container = new \Pimple\Container;
     *     $this->setUpWebservice($container);
     * }
     * </code>
     *
     * @param \Pimple\Container $container
     */
    public function setUpWebservice(Container $container)
    {
        $this->container[ServiceProvider::CENTREON_WEBSERVICE] = new class {

            protected $services = [];

            public function add($class)
            {
                $this->services[$class] = $class;
            }

            public function getServices(): array
            {
                return $this->services;
            }
        };
    }

    /**
     * Check list of webservices if they are registered in webservice chain service
     *
     * <code>
     * $this->checkWebservices([
     *     \MyComponenct\Application\Webservice\MyWebservice::class,
     * ]);
     * </code>
     *
     * @param array $checkList
     */
    public function checkWebservices(array $checkList)
    {
        // check webservices
        $webservices = $this->container[ServiceProvider::CENTREON_WEBSERVICE]->getServices();
        foreach ($checkList as $webservice) {
            $this->assertArrayHasKey($webservice, $webservices);
        }
    }
}
