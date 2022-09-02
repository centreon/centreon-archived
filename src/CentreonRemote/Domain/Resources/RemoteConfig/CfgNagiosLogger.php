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

namespace CentreonRemote\Domain\Resources\RemoteConfig;

use CentreonRemote\Domain\Resources\DefaultConfig\CfgNagiosLogger as DefaultLoggerCfg;

/**
 * Get broker configuration template
 */
class CfgNagiosLogger
{
    /**
     * Get template configuration
     *
     * @param int $nagiosId
     * @return array<string,string|int>
     */
    public static function getConfiguration(int $nagiosId): array
    {
        $cfg = DefaultLoggerCfg::getConfiguration();
        $cfg['cfg_nagios_id'] = $nagiosId;
        return $cfg;
    }
}
