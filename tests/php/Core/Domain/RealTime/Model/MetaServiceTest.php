<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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
use Centreon\Domain\Common\Assertion\AssertionException;
use Core\Domain\RealTime\Model\MetaService;
use Core\Domain\RealTime\Model\ServiceStatus;

class MetaServiceTest extends TestCase
{
    /**
    * test name too long exception
    */
    public function testNameTooLongException(): void
    {
        $name = str_repeat('.', MetaService::MAX_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $name,
                strlen($name),
                MetaService::MAX_NAME_LENGTH,
                'MetaService::name'
            )->getMessage()
        );
        new MetaService(1, 10, 1, $name, 'Central', new ServiceStatus('OK', 0, 0));
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
                'MetaService::name'
            )->getMessage()
        );
        new MetaService(1, 10, 1, $name, 'Central', new ServiceStatus('OK', 0, 0));
    }

    /**
     * @return MetaService
     */
    public static function createMetaServiceModel(): MetaService
    {
        $status = (new ServiceStatus('OK', 0, 0))
            ->setOrder(ServiceStatus::STATUS_ORDER_OK);

        return (new MetaService(1, 10, 20, 'Meta test', 'Central', $status))
            ->setCommandLine('/usr/lib/centreon/plugins/centreon_centreon_central.pl --mode=metaservice --meta-id 1')
            ->setIsFlapping(false)
            ->setIsInDowntime(false)
            ->setIsAcknowledged(false)
            ->setActiveChecks(true)
            ->setPassiveChecks(false)
            ->setLastStatusChange(new \DateTime('1991-09-10'))
            ->setLastNotification(new \DateTime('1991-09-10'))
            ->setLastTimeOk(new \DateTime('1991-09-10'))
            ->setLastCheck(new \DateTime('1991-09-10'))
            ->setNextCheck(new \DateTime('1991-09-10'))
            ->setOutput('Meta output: 0.49')
            ->setPerformanceData('\'g[value]\'=0.49;;;;')
            ->setExecutionTime(0.1)
            ->setLatency(0.214)
            ->setMaxCheckAttempts(5)
            ->setCheckAttempts(2);
    }
}
