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

namespace Core\Security\ProviderConfiguration\Domain\OpenId\Exceptions;

class OpenIdConfigurationException extends \Exception
{
    /**
     * Exception thrown when token endpoint is needed but missing
     *
     * @return self
     */
    public static function missingTokenEndpoint(): self
    {
        return new self(_('Missing token endpoint in your configuration'));
    }

    /**
     * Exception thrown when both user information endpoints are missing
     *
     * @return self
     */
    public static function missingInformationEndpoint(): self
    {
        return new self(_('Missing userinfo and introspection token endpoint'));
    }

    /**
     * Exception thrown when auto import is enabled but mandatory parameters are missing
     *
     * @param string[] $missingAutoImportParameters
     * @return self
     */
    public static function missingAutoImportMandatoryParameters(array $missingAutoImportParameters): self
    {
        return new self(_(sprintf(
            'Missing auto import mandatory parameters: %s',
            implode(', ', $missingAutoImportParameters)
        )));
    }

    /**
     * Exception thrown when contact template link to configuration doesn't exist
     *
     * @param string $contactTemplateName
     * @return self
     */
    public static function contactTemplateNotFound(string $contactTemplateName): self
    {
        return new self(_(sprintf(
            "The contact template '%s' doesn't exist",
            $contactTemplateName
        )));
    }

    /**
     * Exception thrown when contact group link to configuration doesn't exist
     *
     * @param integer $contactGroupId
     * @return self
     */
    public static function contactGroupNotFound(int $contactGroupId): self
    {
        return new self(_(sprintf(
            "The contact group id #%d doesn't exist",
            $contactGroupId
        )));
    }

    /**
     * Exception thrown when mandatory parameters are missing
     *
     * @param array<string> $missingParameters
     * @return self
     */
    public static function missingMandatoryParameters(array $missingParameters): self
    {
        return new self(_(sprintf(
            'Missing mandatory parameters: %s',
            implode(', ', $missingParameters)
        )));
    }

    /**
     * Exception thrown when the Authentication Endpoints is not valid.
     *
     * @param string $endpoint
     * @return self
     */
    public static function invalidAuthenticationConditionsEndpoint(string $endpoint): self
    {
        return new self(_(sprintf(
            'The authentication conditions endpoint should match your introspection or user information endpoints:'
                . ' %s given',
            $endpoint
        )));
    }
}
