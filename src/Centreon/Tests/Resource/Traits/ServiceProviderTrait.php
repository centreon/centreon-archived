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

/**
 * Trait with extension methods for ServiceProvider testing
 *
 * @author Centreon
 * @version 1.0.0
 * @package centreon-license-manager
 * @subpackage test
 */
trait ServiceProviderTrait
{

    /**
     * Check list of services if they return specific instance
     *
     * <code>
     * $this->checkServices([
     *     \MyComponent\ServiceProvider::MY_SERVICE => \MyComponenct\Infrastructure\Service\MyService::class,
     * ]);
     * </code>
     *
     * @param array $checkList
     */
    public function checkServices(array $checkList)
    {
        // check list of services
        foreach ($checkList as $serviceName => $className) {
            $this->assertTrue($this->container->offsetExists($serviceName));

            $service = $this->container->offsetGet($serviceName);

            $this->assertInstanceOf($className, $service);
        }
    }
}
