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
declare(strict_types=1);

namespace Centreon\Domain\ConfigurationLoader;

use Symfony\Component\Yaml\Tag\TaggedValue;
use Symfony\Component\Yaml\Yaml;

/**
 * This class is designed to load a Yaml configuration file taking into account a custom tag to load other files.
 *
 * @package Centreon\Domain\Gorgone
 */
class YamlConfigurationLoader
{
    private const INCLUDE_TOKEN = 'include';

    /**
     * @var string Root configuration file
     */
    private $configurationFile;

    /**
     * YamlConfigurationLoader constructor.
     *
     * @param string $configurationFile
     */
    public function __construct(string $configurationFile)
    {
        $this->configurationFile = $configurationFile;
    }

    /**
     * Loads the configuration file defined in the constructor and returns its equivalent in the form of an array.
     *
     * @return array Configuration data
     */
    public function load(): array
    {
        $configuration = $this->loadFile($this->configurationFile);

        return $this->iterateConfiguration(
            $configuration,
            realpath(dirname($this->configurationFile)),
            realpath($this->configurationFile)
        );
    }

    /**
     * Iterate each key and value to detect the request to load another configuration file.
     *
     * @param array  $configuration     Configuration data to analyse
     * @param string $currentDirectory  Directory of the currently analyzed configuration file
     * @param string $historyLoadedFile History of analyzed configuration files
     *
     * @return array Returns the configuration data including other configuration data from the include files
     */
    private function iterateConfiguration(
        array $configuration,
        string $currentDirectory,
        string $historyLoadedFile
    ): array {
        foreach ($configuration as $key => $value) {
            if (is_array($value)) {
                $configuration[$key] = $this->iterateConfiguration($value, $currentDirectory, $historyLoadedFile);
            } elseif ($value instanceof TaggedValue && $value->getTag() === self::INCLUDE_TOKEN) {
                $fileToLoad = $value->getValue();
                if ($fileToLoad[0] !== DIRECTORY_SEPARATOR) {
                    $fileToLoad = $currentDirectory . DIRECTORY_SEPARATOR . $fileToLoad;
                }
                $dataToIterate = $this->loadFile($fileToLoad);

                if (!$this->isLoopDetected($fileToLoad, $historyLoadedFile)) {
                    $configuration[$key] = $this->iterateConfiguration(
                        $dataToIterate,
                        realpath(dirname($fileToLoad)),
                        $historyLoadedFile . '::' . realpath($fileToLoad)
                    );
                } else {
                    $loadedFile = explode('::', $historyLoadedFile);
                    throw new \Exception('Loop detected in file ' . array_pop($loadedFile));
                }
            }
        }

        return $configuration;
    }

    /**
     * Indicates if a loop is detected.
     *
     * @param string $fileToLoad        File to load
     * @param string $historyLoadedFile File load History
     *
     * @return bool Returns TRUE if a loop is detected
     */
    private function isLoopDetected(string $fileToLoad, string $historyLoadedFile): bool
    {
        $fileToLoad = realpath($fileToLoad);
        $loadedFile = explode('::', $historyLoadedFile);

        return in_array($fileToLoad, $loadedFile);
    }

    /**
     * Load and parse a Yaml configuration file.
     *
     * @param string $yamlFile Yaml configuration file to load
     *
     * @return array Returns the configuration data in the form of an array
     */
    private function loadFile(string $yamlFile): array
    {
        if (!file_exists($yamlFile)) {
            throw new \Exception('The configuration file \'' . $yamlFile . '\' does not exists');
        }

        return (array)Yaml::parseFile($yamlFile, Yaml::PARSE_CUSTOM_TAGS);
    }
}
