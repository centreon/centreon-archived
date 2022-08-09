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

namespace Centreon\Test\Api\Context;

use Centreon\Test\Behat\Api\Context\ApiContext;

class PlatformInstallationStatusContext extends ApiContext
{
    /**
     * Remove centreon databases, configuration file and symfony cache
     *
     * @Given Centreon Web is not installed
     */
    public function centreonWebIsNotInstalled()
    {
        $this->getContainer()->execute(
            'mysql -e "DROP DATABASE centreon_storage"',
            'web'
        );
        $this->getContainer()->execute(
            'mysql -e "DROP DATABASE centreon"',
            'web'
        );
        $this->getContainer()->execute(
            'rm -f /etc/centreon/centreon.conf.php',
            'web'
        );
        $this->getContainer()->execute(
            'rm -rf /var/cache/centreon/symfony',
            'web'
        );
    }
}
