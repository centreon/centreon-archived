<?php

require_once __DIR__ . "/../../../bootstrap.php";

$installFactory = new \CentreonLegacy\Core\Install\Factory($dependencyInjector);
$information = $installFactory->newInformation();

$parameters = filter_input_array(INPUT_GET);
$action = isset($parameters['action']) ? $parameters['action'] : 'stepContent';

switch ($action) {
    case 'stepContent':
        echo $information->getStepContent();
        break;
    case 'nextStep':
        echo $information->nextStepContent();
        break;
    case 'previousStep':
        echo $information->previousStepContent();
        break;
}
