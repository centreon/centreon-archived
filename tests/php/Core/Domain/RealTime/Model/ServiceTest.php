<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Tests\Core\Domain\RealTime\Model;

use PHPUnit\Framework\TestCase;
use Core\Domain\RealTime\Model\Icon;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Domain\RealTime\Model\Service;
use Core\Domain\RealTime\Model\ServiceStatus;

class ServiceTest extends TestCase
{
    /**
    * test name too long exception
    */
    public function testNameTooLongException(): void
    {
        $name = str_repeat('.', Service::MAX_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $name,
                strlen($name),
                Service::MAX_NAME_LENGTH,
                'Service::name'
            )->getMessage()
        );
        new Service(10, 1, $name, new ServiceStatus('OK', 0, 0));
    }

    /**
     * test name empty exception
     */
    public function testNameEmptyException(): void
    {
        $name = '';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::notEmpty(
                'Service::name'
            )->getMessage()
        );
        new Service(10, 1, $name, new ServiceStatus('OK', 0, 0));
    }

    /**
     * @return Service
     */
    public static function createServiceModel(): Service
    {
        $status = (new ServiceStatus('OK', 0, 0))
            ->setOrder(ServiceStatus::STATUS_ORDER_OK);

        $icon = (new Icon())
            ->setName('dog')
            ->setUrl('/dog.png');

        return (new Service(10, 1, 'Ping', $status))
            ->setIcon($icon)
            ->setCommandLine('/usr/lib64/nagios/plugins/check_icmp -H 127.0.0.1 -n 3 -w 200,20% -c 400,50%')
            ->setIsFlapping(false)
            ->setIsInDowntime(false)
            ->setIsAcknowledged(false)
            ->setActiveChecks(true)
            ->setPassiveChecks(false)
            ->setSeverityLevel(10)
            ->setLastStatusChange(new \DateTime('1991-09-10'))
            ->setLastNotification(new \DateTime('1991-09-10'))
            ->setLastTimeOk(new \DateTime('1991-09-10'))
            ->setLastCheck(new \DateTime('1991-09-10'))
            ->setNextCheck(new \DateTime('1991-09-10'))
            ->setOutput('Ping check output')
            ->setPerformanceData('rta=0.342ms;200.000;400.000;0; pl=0%;20;50;0;100 rtmax=0.439ms;;;; rtmin=0.260ms;;;;')
            ->setExecutionTime(0.1)
            ->setLatency(0.214)
            ->setMaxCheckAttempts(5)
            ->setCheckAttempts(2);
    }
}
