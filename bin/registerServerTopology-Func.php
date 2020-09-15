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
    $data = file_get_contents($path);
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
        : false;

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
        $configOptions["PROXY_PORT"] = (int) $options["PROXY_PORT"] ?? '';
        $configOptions["PROXY_USERNAME"] = $options["PROXY_USERNAME"] ?? '';
        if (!empty($configOptions['PROXY_USERNAME'])) {
            $configOptions['PROXY_PASSWORD'] = $options["PROXY_PASSWORD"] ?? '';
        }
    }

    return $configOptions;
}
