<?php

/*
 * Copyright 2005 - 2020 Centreon (https://www.centreon.com/)
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

namespace Centreon\Domain\Monitoring\SubmitResult;

class SubmitResult
{
    /**
     * @var array Allowed statuses code to submit
     */
    public const ALLOWED_STATUSES = [0, 1, 2, 3];
    /**
     * @var int Resource ID
     */
    public $resourceId;

    /**
     * @var int|null Parent Resource ID
     */
    private $parentResourceId;

    /**
     * @var string|null submitted output
     */
    private $output;

    /**
     * @var string|null submitted performance data
     */
    private $performanceData;

    /**
     * @var int submitted status
     */
    public $status;

    public function __construct(int $resourceId, int $status)
    {
        $this->resourceId = $resourceId;
        $this->setStatus($status);
    }

    /**
     * @return int
     */
    public function getResourceId(): int
    {
        return $this->resourceId;
    }

    /**
     * @param int $resourceId
     * @return SubmitResult
     */
    public function setResourceId(int $resourceId): SubmitResult
    {
        $this->resourceId = $resourceId;
        return $this;
    }

    /**
     * @return int
     */
    public function getParentResourceId(): ?int
    {
        return $this->parentResourceId;
    }

    /**
     * @param int|null $parentResourceId
     * @return SubmitResult
     */
    public function setParentResourceId(?int $parentResourceId): SubmitResult
    {
        $this->parentResourceId = $parentResourceId;
        return $this;
    }

    /**
     * Get submitted status
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Set submitted status
     *
     * @param  int $status submitted status
     * @return  SubmitResult
     */
    public function setStatus(int $status): SubmitResult
    {
        if (!in_array($status, self::ALLOWED_STATUSES)) {
            throw new \InvalidArgumentException(
                sprintf(
                    _('Status provided %d is invalid'),
                    $status
                )
            );
        }
        $this->status = $status;
        return $this;
    }

    /**
     * Get submitted perfData
     *
     * @return string|null
     */
    public function getPerformanceData(): ?string
    {
        return $this->performanceData;
    }

    /**
     * Set submitted performance data
     *
     * @param  string|null $performanceData submitted performance data
     * @return  SubmitResult
     */
    public function setPerformanceData(?string $performanceData): SubmitResult
    {
        $this->performanceData = $performanceData;
        return $this;
    }

    /**
     * Get submitted output
     *
     * @return string|null
     */
    public function getOutput(): ?string
    {
        return $this->output;
    }

    /**
     * Set submitted output
     *
     * @param  string|null $output submitted output
     * @return  SubmitResult
     */
    public function setOutput(?string $output): SubmitResult
    {
        $this->output = $output;
        return $this;
    }
}
