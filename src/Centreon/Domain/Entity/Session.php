<?php
/**
 * Copyright 2005-2019 Centreon
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
declare(strict_types=1);

namespace Centreon\Domain\Entity;

class Session
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $sessionId;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var \DateTime
     */
    private $lastReload;

    /**
     * @var string
     */
    private $ipAddress;

    /**
     * @var bool
     */
    private $isValid;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return Session
     */
    public function setId(int $id): Session
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->sessionId;
    }

    /**
     * @param string $sessionId
     * @return Session
     */
    public function setSessionId(string $sessionId): Session
    {
        $this->sessionId = $sessionId;
        return $this;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $userId
     * @return Session
     */
    public function setUserId(int $userId): Session
    {
        $this->userId = $userId;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastReload(): \DateTime
    {
        return $this->lastReload;
    }

    /**
     * @param \DateTime $lastReload
     * @return Session
     */
    public function setLastReload(\DateTime $lastReload): Session
    {
        $this->lastReload = $lastReload;
        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->ipAddress;
    }

    /**
     * @param string $ipAddress
     * @return Session
     */
    public function setIpAddress(string $ipAddress): Session
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    /**
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * @param bool $isValid
     * @return Session
     */
    public function setIsValid(bool $isValid): Session
    {
        $this->isValid = $isValid;
        return $this;
    }
}
