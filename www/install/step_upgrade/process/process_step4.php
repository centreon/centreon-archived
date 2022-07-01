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

session_start();
require_once __DIR__ . '/../../../../bootstrap.php';
require_once __DIR__ . '/../../../class/centreonDB.class.php';
require_once __DIR__ . '/../../steps/functions.php';

use Core\Platform\Application\Repository\WriteVersionRepositoryInterface;

$current = $_POST['current'];
$next = $_POST['next'];
$status = 0;

$kernel = \App\Kernel::createForWeb();
$upgradeWriteRepository = $kernel->getContainer()->get(WriteVersionRepositoryInterface::class);
try {
    $upgradeWriteRepository->runUpdate($next);
} catch (\Throwable $e) {
    exitUpgradeProcess(1, $current, $next, $e->getMessage());
}

$current = $next;

$upgradeReadRepository = $kernel->getContainer()->get(ReadVersionRepositoryInterface::class);
$availableUpdates = $upgradeReadRepository->getOrderedAvailableUpdates($current);
$next = empty($availableUpdates) ? '' : array_shift($availableUpdates);

$_SESSION['CURRENT_VERSION'] = $current;
$okMsg = "<span style='color:#88b917;'>OK</span>";

exitUpgradeProcess($status, $current, $next, $okMsg);
