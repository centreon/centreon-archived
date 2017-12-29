<?php

$installFactory = new \CentreonLegacy\Core\Install\Factory($dependencyInjector);
$information = $installFactory->newInformation();

$step = $information->getStep();

require_once __DIR__ . '/functions.php';
$template = getTemplate(__DIR__ . '/templates');
$template->assign('step', $step);
$template->display('install.tpl');
