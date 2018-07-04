<?php

/**
 * Example scenario file
 *
 * [
 *   {
 *     "name": "Test",
 *     "description": "My super description",
 *     "actions": [
 *        {
 *          "from": "centreon.api.auth",
 *          "name": "Authentication"
 *        },
 *        {
 *          "from": "centreon.api.config.command",
 *          "name": "Add command"
 *        }
 *     ]
 *   }
 * ]
 */


/* Validate the options */
$options = getopt('f:s:d:');

if (!is_file($options['f']) || !is_readable($options['f'])) {
  echo "The configuration must be exits and be readable.\n";
  exit(1);
}

if (!is_dir($options['s']) || !is_writable($options['s'])) {
  echo "The source directory must be exits and be readable.\n";
  exit(1);
}

if (!is_dir($options['d']) || !is_writable($options['d'])) {
  echo "The destination directory must be exits and be writable.\n";
  exit(1);
}

/* Open the configuration file */
try {
  $fileContent = file_get_contents($options['f']);
  $listScenario = json_decode($fileContent, true);
  unset($fileContent);
} catch (\Exception $err) {
  echo $err;
  exit(1);
}

$jsonContents = array();
foreach ($listScenario as $scenario) {
  $jsonOutput = array(
    'info' => array(
      'name' => $scenario['name'],
      '_postman_id' => uniqid(),
      'description' => $scenario['description'],
      'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json'
    ),
    'item' => array()
  );
  foreach ($scenario['actions'] as $action) {
    if (!isset($jsonContents[$action['from']])) {
      $filename = $options['s'] . '/' . $action['from'] . '.postman_collection.json';
      if (!file_exists($filename)) {
        echo "Source file '" . $filename . "' does not exist (" . $options['f'] . ").\n";
        exit(1);
      }
      $fileContent = file_get_contents($filename);
      $jsonContents[$action['from']] = json_decode($fileContent, true);
      unset($fileContent);
    }
    $find = false;
    foreach ($jsonContents[$action['from']]['item'] as $item) {
      if ($item['name'] === $action['name']) {
        $find = true;
        $jsonOutput['item'][] = $item;
      }
    }
    if (!$find) {
      echo "Request '" . $action['name'] . "' does not exist (" . $options['f'] . ").\n";
      exit(1);
    }
  }
  file_put_contents(
    $options['d'] . '/' . str_replace(array(' ', '\\', '/') , '-', strtolower($scenario['name'])). '.postman_collection.json',
    json_encode($jsonOutput)
  );
  unset($jsonOutput);
}
