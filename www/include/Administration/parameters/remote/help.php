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

$help["username"] = dgettext(
    "help",
    "Enter the name of an active contact who have the right to 'reach API configuration' on the Central." .
    "This user will be used to call the API from the Remote to the Central."
);
$help["password"] = dgettext(
    "help",
    "Enter the current password of this account."
);

$help["tip_api_uri"] = dgettext(
    "help",
    "Full URL allowing access to the API of the Centreon's central server."
);

$help["tip_api_peer_validation"] = dgettext(
    "help",
    "Allows to skip the SSL certificate check on the Centreon's central server."
);
