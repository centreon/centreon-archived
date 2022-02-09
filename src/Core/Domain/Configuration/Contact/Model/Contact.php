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

namespace Core\Domain\Configuration\Contact\Model;

use Centreon\Domain\Common\Assertion\Assertion;
use Core\Domain\Configuration\TimePeriod\Model\TimePeriod;

class Contact
{
    public const MAX_NAME_LENGTH = 200,
                 MAX_ALIAS_LENGTH = 200,
                 MAX_MAIL_LENGTH = 200;

    public const EVENT_HOST_DOWN = 'DOWN',
                 EVENT_HOST_UNREACHABLE = 'UNREACHABLE';

    public const EVENT_SERVICE_CRITICAL = 'CRITICAL',
                 EVENT_SERVICE_UNKNOWN = 'UNKNOWN',
                 EVENT_SERVICE_WARNING = 'WARNING';

    public const EVENT_RECOVERY = 'RECOVERY',
                 EVENT_SCHEDULED_DOWNTIME = 'SCHEDULED_DOWNTIME',
                 EVENT_FLAPPING = 'FLAPPING',
                 EVENT_NONE = 'NONE';

    /**
     * @var Timeperiod|null
     */
    private $serviceNotificationTimePeriod;

    /**
     * @var Timeperiod|null
     */
    private $hostNotificationTimePeriod;

    /**
     * @var string[]
     */
    private $notifiedOnHostEvents;

    /**
     * @var string[]
     */
    private $notifiedOnServiceEvents;

    /**
     * @param integer $id
     * @param string $name
     * @param string $alias
     * @param string $mail
     */
    public function __construct(
        private int $id,
        private string $name,
        private string $alias,
        private string $mail
    ) {
        Assertion::maxLength($name, self::MAX_NAME_LENGTH, 'Contact::name');
        Assertion::notEmpty($name, 'Contact::name');
        Assertion::maxLength($alias, self::MAX_ALIAS_LENGTH, 'Contact::alias');
        Assertion::notEmpty($alias, 'Contact::alias');
        Assertion::maxLength($mail, self::MAX_MAIL_LENGTH, 'Contact::mail');
        Assertion::notEmpty($alias, 'Contact::mail');
    }

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getAlias(): string
    {
        return $this->alias;
    }

    /**
     * @return string
     */
    public function getMail(): string
    {
        return $this->mail;
    }

    /**
     * @param TimePeriod|null $timePeriod
     * @return self
     */
    public function setHostNotificationTimePeriod(?TimePeriod $timePeriod): self
    {
        $this->hostNotificationTimePeriod = $timePeriod;
        return $this;
    }

    /**
     * @return TimePeriod|null
     */
    public function getHostNotificationTimePeriod(): ?TimePeriod
    {
        return $this->hostNotificationTimePeriod;
    }
    /**
     * @param TimePeriod|null $timePeriod
     * @return self
     */
    public function setServiceNotificationTimePeriod(?TimePeriod $timePeriod): self
    {
        $this->serviceNotificationTimePeriod = $timePeriod;
        return $this;
    }

    /**
     * @return TimePeriod|null
     */
    public function getServiceNotificationTimePeriod(): ?TimePeriod
    {
        return $this->serviceNotificationTimePeriod;
    }

    /**
     * @param string[] $events
     * @return self
     */
    public function setNotifiedOnHostEvents(array $events): self
    {
        $this->notifiedOnHostEvents = $events;
        return $this;
    }

    /**
     * @return string[] $events
     */
    public function getNotifiedOnHostEvents(): array
    {
        return $this->notifiedOnHostEvents;
    }

    /**
     * @param string[] $events
     * @return self
     */
    public function setNotifiedOnServiceEvents(array $events): self
    {
        $this->notifiedOnServiceEvents = $events;
        return $this;
    }

    /**
     * @return string[] $events
     */
    public function getNotifiedOnServiceEvents(): array
    {
        return $this->notifiedOnServiceEvents;
    }
}
