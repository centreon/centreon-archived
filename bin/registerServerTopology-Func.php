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
 * @return array
 */
function registerRemote(string $ip, array $loginCredentials): array
{
    $db = new CentreonDB();

    //verifier que ce n'est pas un remote
    $db->query(" SELECT * FROM `informations` WHERE `key` = 'isRemote' AND value = 'yes'");
    $isRemote = $db->numberRows();
    if ($isRemote) {
        require_once _CENTREON_PATH_ . "/src/Centreon/Infrastructure/CentreonLegacyDB/ServiceEntityRepository.php";
        require_once _CENTREON_PATH_ . "/src/Centreon/Domain/Repository/InformationsRepository.php";
        require_once _CENTREON_PATH_ . "/src/Centreon/Domain/Repository/TopologyRepository.php";
        $topologyRepository = new \Centreon\Domain\Repository\TopologyRepository($db);
        $informationRepository = new \Centreon\Domain\Repository\InformationsRepository($db);
        die();
        //hide menu
        $topologyRepository->disableMenus();

        //register remote in db
        $informationRepository->toggleRemote('yes');

        //register master in db
        $informationRepository->authorizeMaster($ip);

        //Apply Remote Server mode in configuration file
        system(
            "sed -i -r 's/(\\\$instance_mode?\s+=?\s+\")([a-z]+)(\";)/\\1remote\\3/' " . _CENTREON_ETC_ . "/conf.pm"
        );
    }

    //register credentials
    registerCentralCredentials($db, $loginCredentials);

    //update platform_topology type
    $db->query("UPDATE `platform_topology` SET `type` = 'remote' WHERE `type` = 'central'");

    // return children
    return getChildren($db);
}

/**
 * @return bool
 */
function hasRemoteChild(): bool
{

    $db = new CentreonDB();
    $remoteQuery = $db->query("SELECT COUNT(*) AS total FROM `remote_servers`");
    $remote = $remoteQuery->fetch();
    if ($remote['total'] > 0) {
        return true;
    }
    return false;
}


/**
 * @param CentreonDB $db
 * @return array
 */
function getChildren(CentreonDB $db): array
{
    $registerChildren = [];
    // get local server address
    $localStmt = $db->query("SELECT `address` FROM platform_topology WHERE `type` = 'remote'");
    $parentAddress = $localStmt->fetchColumn();
    $localStmt = $db->query("SELECT `name`,`type`,`address` FROM platform_topology WHERE `type` != 'remote'");
    while ($row = $localStmt->fetch()) {
        $row['parent_address'] = $parentAddress;
        $registerChildren[] = $row;
    }
    return $registerChildren;
}


/**
 * @param CentreonDB $db
 * @param array $loginCredentials
 */
function registerCentralCredentials(CentreonDB $db, array $loginCredentials): void
{
    $queryValue = '';
    $count = 1;
    $bindValues = [];
    foreach ($loginCredentials as $key => $value) {
        if($count === count($loginCredentials)) {
            $queryValue .= " ('$key', :$key)";
        }else {
            $queryValue .= " ('$key', :$key), ";
        }
        $count++;
        switch ($key) {
            case 'apiPort':
                $bindValues[':' . $key] = [
                    \PDO::PARAM_INT => $value
                ];
                break;
            default:
                $bindValues[':' . $key] = [
                    \PDO::PARAM_STR => $value
                ];
                break;
        }
    }
    $db->query("DELETE FROM informations WHERE `key` LIKE '%api%'");
    $query = "INSERT INTO `informations` (`key`, `value`) VALUES $queryValue";
    var_dump($query);
    $statement = $db->prepare($query);
    foreach($bindValues as $token => $bindParams) {
        foreach($bindParams as $paramType => $paramValue) {
            $statement->bindValue($token, $paramValue, $paramType);
        }
    }
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

/**
 * Parse the template to an array usable by the script
 *
 * @param string $path
 * @return array
 */
function parseTemplateFile(string $path): array
{
    if (!file_exists(getcwd() . '/' . $path)) {
        throw new \InvalidArgumentException('File ' . $path . ' not found' . PHP_EOL);
    }
    $data = file_get_contents(getcwd() . '/' . $path);
    //Remove the blank line
    $data = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $data);
    $lines = preg_split("/(\n)/", $data);
    $configVariables = [];
    foreach ($lines as $line) {
        if (preg_match('/^(.+?)=(.+)$/', $line, $match)) {
            $configVariables[trim($match[1])] = trim($match[2]);
        }
    }
    return castTemplateValue($configVariables);
}

/**
 * Cast correct type for each values
 *
 * @param array $configVariables
 * @return array
 */
function castTemplateValue(array $configVariables): array
{
    foreach ($configVariables as $configKey => $configValue) {
        switch ($configKey) {
            case 'INSECURE':
            case 'PROXY_USAGE':
                $configVariables[$configKey] = (bool) $configValue;
                break;
            case 'PROXY_PORT':
                $configVariables[$configKey] = (int) $configValue;
                break;
        }
    }
    return $configVariables;
}

/**
 * Refactor all the settings options mechanism to avoid duplication in the script part
 *
 * @param array $options
 * @param string $helpMessage
 * @return array
 */
function setConfigOptionsFromTemplate(array $options, string $helpMessage): array
{
    $configOptions = [];
    if (
        !isset(
            $options['API_USERNAME'],
            $options['API_PASSWORD'],
            $options['SERVER_TYPE'],
            $options['HOST_ADDRESS'],
            $options['SERVER_NAME']
        )
    ) {
        throw new \InvalidArgumentException(
            PHP_EOL .
            'missing value: API_USERNAME, API_PASSWORD, SERVER_TYPE, HOST_ADDRESS and SERVER_NAME are mandatories'
            . PHP_EOL . $helpMessage
        );
    }

    $configOptions['API_USERNAME'] = $options['API_USERNAME'];
    $configOptions['SERVER_TYPE'] = in_array(strtolower($options['SERVER_TYPE']), SERVER_TYPES)
        ? strtolower($options['SERVER_TYPE'])
        : null;

    if (!$configOptions['SERVER_TYPE']) {
        throw new \InvalidArgumentException(
            "SERVER_TYPE must be one of those value"
            . PHP_EOL . "Poller, Remote, MAP, MBI" . PHP_EOL
        );
    }

    $configOptions['API_PASSWORD'] = $options['API_PASSWORD'] ?? '';
    $configOptions['ROOT_CENTREON_FOLDER'] = $options['ROOT_CENTREON_FOLDER'] ?? 'centreon';
    $configOptions['HOST_ADDRESS'] = $options['HOST_ADDRESS'];
    $configOptions['SERVER_NAME'] = $options['SERVER_NAME'];

    if (isset($options['DNS'])) {
        $configOptions['DNS'] = filter_var($options['DNS'], FILTER_VALIDATE_DOMAIN);
        if (!$configOptions['DNS']) {
            throw new \InvalidArgumentException(PHP_EOL . "Bad DNS Format" . PHP_EOL);
        }
    }

    if (isset($options['INSECURE']) && $options['INSECURE'] === true) {
        $configOptions['INSECURE'] = true;
    }

    if (isset($options['PROXY_USAGE']) && $options['PROXY_USAGE'] === true) {
        $configOptions['PROXY_USAGE'] = $options['PROXY_USAGE'];
        $configOptions["PROXY_HOST"] = $options["PROXY_HOST"] ?? '';
        $configOptions["PROXY_PORT"] = (int)$options["PROXY_PORT"] ?? '';
        $configOptions["PROXY_USERNAME"] = $options["PROXY_USERNAME"] ?? '';
        if (!empty($configOptions['PROXY_USERNAME'])) {
            $configOptions['PROXY_PASSWORD'] = $options["PROXY_PASSWORD"] ?? '';
        }
    }

    return $configOptions;
}
