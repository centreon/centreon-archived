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
use Core\Domain\RealTime\Model\Host;
use Core\Domain\RealTime\Model\Icon;
use Core\Domain\RealTime\Model\Status;
use Core\Domain\RealTime\Model\Hostgroup;
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Domain\RealTime\Model\Downtime;
use DateTime;

class HostTest extends TestCase
{
    /**
    * test Name too long exception
    */
   public function testNameTooLongException(): void
   {
       $hostName = str_repeat('.', Host::MAX_NAME_LENGTH + 1);
       $this->expectException(\InvalidArgumentException::class);
       $this->expectExceptionMessage(
           AssertionException::maxLength(
               $hostName,
               strlen($hostName),
               Host::MAX_NAME_LENGTH,
               'Host::name'
           )->getMessage()
       );
       new Host(1, $hostName, 'localhost', 'central', new Status('UP', 0, 0));
   }
    /**
     * @return Host
     */
    public static function createHostModel(): Host
    {
        /**
         * @var Status
         */
        $status = (new Status('UP', 0, 1))
            ->setOrder(Status::STATUS_ORDER_OK);

        /**
         * @var Icon
         */
        $icon = (new Icon())
            ->setName('dog')
            ->setUrl('/dog.png');

        /**
         * @var Hostgroup
         */
        $hostgroup = new Hostgroup(10, 'ALL');

        return (new Host(1, 'Centreon-Central', 'localhost', 'Central', $status))
            ->setAlias('Central')
            ->setIcon($icon)
            ->setCommandLine('/usr/lib64/nagios/plugins/check_icmp -H 127.0.0.1 -n 3 -w 200,20% -c 400,50%')
            ->setTimeZone(':Europe/Paris')
            ->setIsFlapping(false)
            ->setIsInDowntime(false)
            ->setIsAcknowledged(false)
            ->setActiveChecks(true)
            ->setPassiveChecks(false)
            ->setSeverityLevel(10)
            ->setLastStatusChange(new \DateTime('yesterday'))
            ->setLastNotification(new \DateTime('yesterday'))
            ->setLastTimeUp(new \DateTime('yesterday'))
            ->setLastCheck(new \DateTime('tomorrow'))
            ->setNextCheck(new \DateTime('tomorrow'))
            ->setOutput('Host check output')
            ->setPerformanceData('rta=0.342ms;200.000;400.000;0; pl=0%;20;50;0;100 rtmax=0.439ms;;;; rtmin=0.260ms;;;;')
            ->setExecutionTime(0.1)
            ->setLatency(0.214)
            ->addHostgroup($hostgroup)
            ->setMaxCheckAttempts(5)
            ->setCheckAttemps(2);
    }
}