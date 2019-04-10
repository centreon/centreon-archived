<?php
/**
 * Copyright 2019 Centreon
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Centreon\Tests\Resource\Traits;

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
