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

namespace Centreon\Domain\Monitoring\Exception;

class MonitoringServiceException extends \Exception
{
    /**
     * @return self
     */
    public static function hostIdNotNull(): self
    {
        return new self(_('Host id cannot be null'));
    }

    /**
     * @return self
     */
    public static function serviceIdNotNull(): self
    {
        return new self(_('Service id cannot be null'));
    }

    /**
     * @return self
     */
    public static function configurationHasChanged(): self
    {
        return new self(_('Configuration has changed'));
    }

    /**
     * @return self
     */
    public static function macroPasswordNotDetected(): self
    {
        return new self(_('Macro passwords cannot be detected'));
    }

    /**
     * @return self
     */
    public static function configurationCommandNotSplitted(): self
    {
        return new self(_('Configuration command cannot be splitted by macros'));
    }
}
