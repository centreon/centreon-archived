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

namespace Centreon\Domain\Monitoring\Comment;

class Comment
{
    /**
     * @var int Resource ID
     */
    public $resourceId;

    /**
     * @var int|null Parent Resource ID
     */
    private $parentResourceId;

    /**
     * @var string added comment
     */
    public $comment;

    /**
     * Date of the comment
     *
     * @var \DateTime|null
     */
    public $date;

    public function __construct(int $resourceId, string $comment)
    {
        $this->resourceId = $resourceId;
        $this->setComment($comment);
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
     * @return Comment
     */
    public function setResourceId(int $resourceId): Comment
    {
        $this->resourceId = $resourceId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getParentResourceId(): ?int
    {
        return $this->parentResourceId;
    }

    /**
     * @param int|null $parentResourceId
     * @return Comment
     */
    public function setParentResourceId(?int $parentResourceId): Comment
    {
        $this->parentResourceId = $parentResourceId;
        return $this;
    }

    /**
     * Get added comment
     *
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * Set added comment
     *
     * @param string $comment added comment
     * @return Comment
     */
    public function setComment(string $comment): Comment
    {
        if (empty($comment)) {
            throw new \InvalidArgumentException(
                "Comment can not be empty"
            );
        }
        $this->comment = $comment;
        return $this;
    }

    /**
     * Get date of the comment
     *
     * @return \DateTime|null
     */
    public function getDate(): ?\DateTime
    {
        return $this->date;
    }

    /**
     * Set date of the comment
     *
     * @param \DateTime $date Date of the comment
     * @return Comment
     */
    public function setDate(\DateTime $date)
    {
        $this->date = $date;
        return $this;
    }
}
