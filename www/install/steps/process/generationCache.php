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

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;

set_time_limit(0);
require __DIR__ . '/../../../../vendor/autoload.php';

function extractErrorMessage(BufferedOutput $output): ?string
{
    $rawMessage = $output->fetch();
    $messages = explode("\n", $rawMessage);
    $filteredMessages = [];
    foreach ($messages as $rawMessage) {
        if (!empty(trim($rawMessage))) {
            $filteredMessages[] = $rawMessage;
        }
    }
    if (!empty($filteredMessages)) {
        if (substr(strtolower($filteredMessages[0]), 0, 2) == 'in') {
            array_shift($filteredMessages);
        }
        return implode('<br/>', $filteredMessages);
    }
    return null;
}

$return = array(
    'id' => 'generationCache',
    'result' => 0,
    'msg' => 'OK'
);

try {
    if (!class_exists(Application::class)) {
        throw new RuntimeException('You need to add "symfony/framework-bundle" as a Composer dependency.');
    }

    require __DIR__ . '/../../../../config/bootstrap.php';
    $kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
    $application = new Application($kernel);
    $application->setAutoExit(false);

    $consoleOutput = new BufferedOutput();
    $consoleOutput->setVerbosity(OutputInterface::VERBOSITY_QUIET | OutputInterface::OUTPUT_RAW);
    $input = new ArgvInput(['', 'cache:clear']);

    $code = $application->run($input, $consoleOutput);
    if (!is_null($message = extractErrorMessage($consoleOutput))) {
        throw new \Exception($message);
    }
} catch (\Exception $e) {
    $return['result'] = 1;
    $return['msg'] = $e->getMessage();
    echo json_encode($return);
    exit;
}

echo json_encode($return);
