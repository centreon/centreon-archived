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
$help = [];

/**
 * Gorgone Settings
 */
$help['tip_gorgone_cmd_timeout'] = dgettext(
    'help',
    "Timeout value in seconds. Used to make actions calls timeout."
);
$help['tip_enable_broker_stats'] = dgettext(
    'help',
    'Enable Centreon Broker statistics collection into the central server.'
    . ' Be careful: if disabled, Broker statistics will not be'
    . ' available into Home > Broker statistics.'
);
$help['tip_gorgone_illegal_characters'] = dgettext(
    'help',
    'Illegal characters in external commands. Those characters will be removed ' .
    'before being processed by Centreon Gorgone.'
);
$help['tip_gorgone_api_address'] = dgettext(
    'help',
    'IP Address or hostname to communicate with Gorgone API. Should remain default value ' .
    '(Default: "127.0.0.1").'
);
$help['tip_gorgone_api_port'] = dgettext(
    'help',
    'Port on which Gorgone API is listening. It must match Gorgone httpserver module definition. ' .
    'Should remain default value (Default: "8085").'
);
$help['tip_gorgone_api_username'] = dgettext(
    'help',
    'Username used to connect to Gorgone API. It must match Gorgone httpserver module definition ' .
    '(Default: none).'
);
$help['tip_gorgone_api_password'] = dgettext(
    'help',
    'Password used to connect to Gorgone API. It must match Gorgone httpserver module definition ' .
    '(Default: none).'
);
$help['tip_gorgone_api_ssl'] = dgettext(
    'help',
    'Define if SSL/TLS must be used to connect to API. It must match Gorgone httpserver module definition ' .
    '(Default: no).'
);
$help['tip_gorgone_api_allow_self_signed'] = dgettext(
    'help',
    'Define if connection to Gorgone API can be done even if a self signed certificat is used ' .
    '(Default: yes).'
);
