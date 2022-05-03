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

namespace CentreonRemote\Domain\Resources\DefaultConfig;

/**
 * Get broker configuration template
 */
class CfgNagiosLogger
{
    /**
     * Get template configuration
     *
     * @return array<string,string|int>
     */
    public static function getConfiguration(): array
    {
        return [
            'cfg_nagios_id' => 1,
            'log_v2_logger' => 'file',
            'log_level_functions' => 'err',
            'log_level_config' => 'info',
            'log_level_events' => 'info',
            'log_level_checks' => 'info',
            'log_level_notifications' => 'err',
            'log_level_eventbroker' => 'err',
            'log_level_external_command' => 'info',
            'log_level_commands' => 'err',
            'log_level_downtimes' => 'err',
            'log_level_comments' => 'err',
            'log_level_macros' => 'err',
            'log_level_process' => 'info',
            'log_level_runtime' => 'err',
        ];
    }
}
