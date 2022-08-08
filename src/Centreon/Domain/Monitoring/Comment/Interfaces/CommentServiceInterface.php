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

namespace Centreon\Domain\Monitoring\Comment\Interfaces;

use Centreon\Domain\Monitoring\Host;
use Centreon\Domain\Monitoring\Service;
use Centreon\Domain\Monitoring\Comment\Comment;
use Centreon\Domain\Contact\Interfaces\ContactFilterInterface;

interface CommentServiceInterface extends ContactFilterInterface
{
    /**
     * Function allowing contact to add a comment to a service
     *
     * @param  Comment $comment Comment to add to the service
     * @param Service $service Service that will receive the comment
     */
    public function addServiceComment(Comment $comment, Service $service): void;

    /**
     * Function allowing contact to add a comment to a service
     *
     * @param  Comment $comment Comment to add to the service
     * @param Service $metaService Meta service that will receive the comment
     */
    public function addMetaServiceComment(Comment $comment, Service $metaService): void;

    /**
     * Function allowing contact to add a comment to a host
     *
     * @param  Comment $comment Comment to add to the host
     * @param Host $host Host that will receive the comment
     * @return void
     */
    public function addHostComment(Comment $comment, Host $host): void;

    /**
     * Function allowing contact to add multiple comments to multiple resources
     *
     * @param Comment[] $comments Comments added for each resources
     * @param array $resourceIds IDs of the resources
     * @return void
     */
    public function addResourcesComment(array $comments, array $resourceIds): void;
}
