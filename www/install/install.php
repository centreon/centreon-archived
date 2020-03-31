<?php

require_once __DIR__ . "/../../bootstrap.php";
require_once __DIR__ . '/../class/centreonSession.class.php';

CentreonSession::start();
ini_set("track_errors", true);

$installFactory = new \CentreonLegacy\Core\Install\Factory($dependencyInjector);
$information = $installFactory->newInformation();

$step = $information->getStep();

require_once __DIR__ . '/steps/functions.php';
$template = getTemplate(__DIR__ . '/steps/templates');
$template->display('install.tpl');
