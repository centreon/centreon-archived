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

namespace Centreon\Domain\RequestParameters;

/**
 * This class is designed to contain all exceptions for the context of the request parameters.
 *
 * @package Centreon\Domain\RequestParameters
 */
class RequestParametersException extends \InvalidArgumentException
{
    /**
     * @param string $parameterName
     * @return RequestParametersException
     */
    public static function integer(string $parameterName): self
    {
        return new self(sprintf(_('The parameter "%s" must be an integer'), $parameterName));
    }
}
