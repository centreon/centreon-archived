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

require_once _CENTREON_PATH_ . '/www/class/centreonDB.class.php';

use Centreon\Domain\Repository\InformationsRepository;
use Centreon\Domain\Repository\TopologyRepository;

/**
 * Ask question. The echo of keyboard can be disabled
*
* @param string $question
* @param boolean $hidden
* @return string
*/
function askQuestion(string $question, $hidden = false): string
{
    if ($hidden) {
        system("stty -echo");
    }
    printf("%s", $question);
    $handle = fopen("php://stdin", "r");
    $response = trim(fgets($handle));
    fclose($handle);
    if ($hidden) {
        system("stty echo");
    }
    printf("\n");
    return $response;
}

/**
 * @param string $type
 * @return bool
 */
function isRemote(string $type): bool
{
    if ($type === TYPE_REMOTE) {
        return true;
    }
    return false;
}

/**
 * @param string $ip
 * @param array $loginCredentials
 */
function registerRemote(string $ip, array $loginCredentials): void
{
    $db = new CentreonDB();
    $topologyRepository = new TopologyRepository($db);
    $informationRepository = new InformationsRepository($db);

    //hide menu
    $topologyRepository->disableMenus();

    //register remote in db
    $informationRepository->toggleRemote('yes');

    //register master in db
    $informationRepository->authorizeMaster($ip);

    //register credentials
    registerCentralCredentials($db, $loginCredentials);

    //Apply Remote Server mode in configuration file
    system(
        "sed -i -r 's/(\\\$instance_mode?\s+=?\s+\")([a-z]+)(\";)/\\1remote\\3/' " . _CENTREON_ETC_ . "/conf.pm"
    );
}

/**
 * @return bool
 */
function haveRemoteChild(): bool
{
    $db = new CentreonDB();
    $remoteQuery = $db->query("SELECT COUNT(*) AS total FROM `remote_servers`");
    $remote = $remoteQuery->fetch();
    if(empty($remote)){
       return false;
    }
    return true;
}


/**
 * @param CentreonDB $db
 * @return array
 */
function getChildren(CentreonDB $db): array
{
    // get local server id
    $localStmt = $db->query("SELECT `id` FROM nagios_server WHERE localhost = '1'");
    $parentId = $localStmt->fetchColumn();




    $query = "INSERT INTO `informations` (`key`, `value`) VALUES ('apiUsername', :username), ('apiCredentials', :pwd)";
    $statement = $db->prepare($query);
    $statement->bindValue(':username', $loginCredentials['login'], \PDO::PARAM_STR);
    $statement->bindValue(':pwd', $loginCredentials['password'], \PDO::PARAM_STR);
    $statement->execute();
}


/**
 * @param CentreonDB $db
 * @param array $loginCredentials
 */
function registerCentralCredentials(CentreonDB $db, array $loginCredentials): void
{
    $query = "INSERT INTO `informations` (`key`, `value`) VALUES ('apiUsername', :username), ('apiCredentials', :pwd)";
    $statement = $db->prepare($query);
    $statement->bindValue(':username', $loginCredentials['login'], \PDO::PARAM_STR);
    $statement->bindValue(':pwd', $loginCredentials['password'], \PDO::PARAM_STR);
    $statement->execute();
}

/**
 * Format the response for API Request
 *
 * @param integer $code
 * @param string $message
 * @param string $type
 * @return string
 */
function formatResponseMessage(int $code, string $message, string $type = 'success'): string
{
    switch ($type) {
        case 'success':
            $responseMessage = 'code: ' . $code . PHP_EOL .
            'message: ' . $message . PHP_EOL;
            break;
        case 'error':
        default:
            $responseMessage = 'error code: ' . $code . PHP_EOL .
            'error message: ' . $message . PHP_EOL;
            break;
    }

    return sprintf('%s', $responseMessage);
}
