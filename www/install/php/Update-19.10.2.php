<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
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
include_once __DIR__ . "/../../class/centreonLog.class.php";
$centreonLog = new CentreonLog();

// fix contact auto login default value when not set
try {
    $pearDB->query(
        "UPDATE `contact` SET `contact_autologin_key` = NULL WHERE `contact_autologin_key` =''"
    );
} catch (\PDOException $e) {
    $centreonLog->insertLog(
        2,
        "UPGRADE : 19.10.2 Unable to set default contact_autologin_key"
    );
}

