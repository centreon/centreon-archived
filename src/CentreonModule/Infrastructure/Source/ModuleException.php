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

namespace CentreonModule\Infrastructure\Source;

class ModuleException extends \Exception
{
    /**
     * @param string[] $modules
     * @return self
     */
    public static function modulesNeedToBeRemovedFirst(array $modules): self
    {
        return new self(
            sprintf(_('Following modules need to be removed first: %s'), implode(', ', $modules)),
        );
    }

    /**
     * @param string $module
     * @return self
     */
    public static function moduleIsMissing(string $module): self
    {
        return new self(
            sprintf(_('Module "%s" is missing'), $module),
        );
    }

    /**
     * @param string $module
     * @return self
     */
    public static function cannotFindModuleDetails(string $module): self
    {
        return new self(
            sprintf(_('An error occured while retrieving details of module "%s"'), $module),
        );
    }
}
