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

namespace Centreon\Infrastructure\Serializer\Exception;

/**
 * This class is designed to contain all exceptions concerning deserialization.
 *
 * @package Centreon\Infrastructure\Serializer\Exception
 */
class SerializerException extends \Exception
{
    /**
     * @param string $classname
     * @param \Throwable $ex
     * @return self
     */
    public static function notEnoughConstructorArguments(string $classname, \Throwable $ex): self
    {
        return new self(sprintf(_('There are not enough arguments to build the object %s'), $classname), 0, $ex);
    }
}
