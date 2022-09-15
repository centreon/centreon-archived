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

namespace Core\Security\Authentication\Application\UseCase\Login;

use Core\Application\Common\UseCase\BodyResponseInterface;
use Core\Application\Common\UseCase\ResponseStatusInterface;
use Core\Application\Common\UseCase\UnauthorizedResponse;

class PasswordExpiredResponse extends UnauthorizedResponse implements ResponseStatusInterface, BodyResponseInterface
{
    /**
     * @var mixed
     */
    private $body;

    /**
     * @inheritDoc
     */
    public function setBody(mixed $body): void
    {
        $this->body = $body;
    }

    /**
     * @inheritDoc
     */
    public function getBody(): mixed
    {
        return $this->body;
    }
}
