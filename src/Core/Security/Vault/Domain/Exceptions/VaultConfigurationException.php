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

namespace Core\Security\Vault\Domain\Exceptions;

class VaultConfigurationException extends \Exception
{
    /**
     * Exception thrown when type is not allowed.
     *
     * @param string $type
     *
     * @return self
     */
    public static function invalidType(string $type): self
    {
        return new self(_(sprintf('Invalid vault type: %s', $type)));
    }

    /**
     * Exception thrown when vault configuration already exists.
     *
     * @return self
     */
    public static function configurationExists(): self
    {
        return new self(_('Vault configuration with these properties already exists'));
    }

    /**
     * Exception thrown when parameters are not valid.
     *
     * @param string[] $parameters
     *
     * @return self
     */
    public static function invalidParameters(array $parameters): self
    {
        return new self(_(sprintf('Invalid parameter(s): %s', implode(', ', $parameters))));
    }

    /**
     * Exception thrown when unhandled error occurs.
     *
     * @return self
     */
    public static function impossibleToCreate(): self
    {
        return new self(_('Impossible to create vault configuration'));
    }
}
