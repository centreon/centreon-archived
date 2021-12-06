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
 *Controller
 */
declare(strict_types=1);

namespace Centreon\Domain\Service\JsonValidator;

use Centreon\Domain\Service\JsonValidator\Interfaces\JsonValidatorInterface;
use Centreon\Domain\Service\JsonValidator\Interfaces\ValidatorCacheInterface;
use JsonSchema\Constraints\Constraint;
use JsonSchema\Validator as JsonSchemaValidator;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\Config\Resource\ResourceInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Validator used to validate a JSON from a json-schema description.
 *
 * @package Centreon\Domain\Service\JsonValidator
 */
class Validator implements JsonValidatorInterface
{
    public const VERSION_LATEST = 'latest';
    public const VERSION_BETA = 'beta';

    private const VERSION_DEFAULT = 'default';

    private const COMPONENTS_REFERENCE = '$components';

    /**
     * @var JsonSchemaValidator
     */
    private $validator;

    /**
     * @var array List of definitions that will be used to validate the JSON
     */
    private $definitions = [];

    /**
     * @var ResourceInterface[] List of YAML definition files
     */
    private $definitionFiles = [];

    /**
     * @var string Directory that will contain all the default definition files if no version is given.
     */
    private $defaultDefinitionFilesDirectory;

    /**
     * @var string Version of the definition files to use for the validation process
     */
    private $version;

    /**
     * @var string Path where the definition files are stored
     */
    private $validationFilePath;

    /**
     * @var ValidatorCacheInterface
     */
    private $validatorCache;

    /**
     * Validator constructor.
     *
     * @param string $validationFilePath
     * @param ValidatorCacheInterface $validatorCache
     */
    public function __construct(string $validationFilePath, ValidatorCacheInterface $validatorCache)
    {
        if (is_dir($validationFilePath)) {
            $this->defaultDefinitionFilesDirectory =
                $validationFilePath . DIRECTORY_SEPARATOR . self::VERSION_DEFAULT;
        }
        $this->validationFilePath = $validationFilePath;
        $this->validatorCache = $validatorCache;
        $this->version = self::VERSION_DEFAULT;
    }

    /**
     * @inheritDoc
     */
    public function forVersion(string $version): JsonValidatorInterface
    {
        $this->version = $version;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function validate(string $json, string $modelName): ConstraintViolationListInterface
    {
        if ($this->validator === null) {
            $this->validator = new JsonSchemaValidator();
        }
        $dataToValidate = json_decode($json);
        if ($dataToValidate === null) {
            throw new \Exception(_('The JSON cannot be decoded'));
        }
        if (empty($this->definitions) && $this->validationFilePath !== null) {
            $this->loadDefinitionFile();
        }

        $definitionsToUseForValidation = [];

        /*
         * First of all, we look for definitions according to the given version.
         * otherwise, we look for the default definitions.
         */
        if (
            array_key_exists($this->version, $this->definitions)
            && array_key_exists($modelName, $this->definitions[$this->version])
        ) {
            $definitionsToUseForValidation = $this->populateComponentsToDefinitions(
                $this->definitions[$this->version][$modelName],
                $this->definitions[self::VERSION_DEFAULT]
            );
        } elseif (
            array_key_exists(self::VERSION_DEFAULT, $this->definitions)
            && array_key_exists($modelName, $this->definitions[self::VERSION_DEFAULT])
        ) {
            $definitionsToUseForValidation = $this->populateComponentsToDefinitions(
                $this->definitions[self::VERSION_DEFAULT][$modelName],
                $this->definitions[self::VERSION_DEFAULT]
            );
        }

        if (empty($definitionsToUseForValidation)) {
            throw new \Exception(
                sprintf(_('The definition model "%s" to validate the JSON does not exist or is empty'), $modelName)
            );
        }

        $this->validator->validate(
            $dataToValidate,
            $definitionsToUseForValidation,
            Constraint::CHECK_MODE_ONLY_REQUIRED_DEFAULTS
        );

        return (!$this->validator->isValid())
            ? $this->formatErrors($this->validator->getErrors(), $json)
            : new ConstraintViolationList();
    }

    /**
     * Add component references to definitions
     *
     * @param array $definitionsToPopulate
     * @param array $versionedDefinitions
     * @return array The definitions with component refs if exist
     */
    private function populateComponentsToDefinitions(
        array $definitionsToPopulate,
        array $versionedDefinitions
    ): array {
        if (array_key_exists(self::COMPONENTS_REFERENCE, $versionedDefinitions)) {
            $definitionsToPopulate[self::COMPONENTS_REFERENCE] = $versionedDefinitions[self::COMPONENTS_REFERENCE];
        }

        return $definitionsToPopulate;
    }

    /**
     * Load the definition files for the filesystem or cache.
     */
    private function loadDefinitionFile(): void
    {
        $this->definitionFiles = [];
        if (!$this->validatorCache->isCacheValid()) {
            // We will load the definition files and create the cache
            if (is_file($this->validationFilePath)) {
                $info = pathinfo($this->validationFilePath);
                if (($info['extension'] ?? '') === 'yaml') {
                    $this->getDefinitionsByFile($this->validationFilePath);
                }
            } elseif (is_dir($this->validationFilePath)) {
                foreach (new \DirectoryIterator($this->validationFilePath) as $fileInfo) {
                    if ($fileInfo->isDir() && !in_array($fileInfo->getFilename(), ['.', '..'])) {
                        $version = $fileInfo->getFilename();
                        $this->definitions = array_merge_recursive(
                            $this->definitions,
                            $this->getDefinitionsByVersion($version)
                        );
                    }
                };
            }
            // The definitions are loaded, we put them in the cache
            $this->validatorCache->setCache(
                serialize($this->definitions),
                $this->definitionFiles
            );
        } else {
            // We retrieve data from cache
            if (($cache = $this->validatorCache->getCache()) !== null) {
                $this->definitions = unserialize($cache);
            }
        }
    }

    private function getDefinitionsByVersion(string $version): array
    {
        $versionPath = $this->validationFilePath . DIRECTORY_SEPARATOR . $version;
        $directoryIterator = new \RecursiveDirectoryIterator($versionPath);
        $recursiveIterator = new \RecursiveIteratorIterator($directoryIterator);
        $yamlFiles = new \RegexIterator($recursiveIterator, '/^.+\.yaml/i', \RecursiveRegexIterator::GET_MATCH);
        $definitions = [];

        foreach ($yamlFiles as $file) {
            $definitions = array_merge_recursive(
                $definitions,
                $this->getDefinitionsByFile($file[0])
            );
        }
        return [$version => $definitions];
    }

    /**
     * Get the definitions found in the file.
     *
     * @param string $pathFilename Path name of the definition file
     * @return array Returns the definitions found
     */
    private function getDefinitionsByFile(string $pathFilename): array
    {
        $this->definitionFiles[] = new FileResource($pathFilename);
        if (($yamlData = file_get_contents($pathFilename)) !== false) {
            return Yaml::parse($yamlData);
        }
        return [];
    }

    /**
     * Format the validation errors.
     *
     * @param array $errors Errors list
     * @param string $json Serialized JSON data to analyse
     * @return ConstraintViolationListInterface Returns the formatted error list
     */
    private function formatErrors(array $errors, string $json): ConstraintViolationListInterface
    {
        $jsonData = json_decode($json, true);
        $constraints = new ConstraintViolationList();
        foreach ($errors as $error) {
            $originalValue = $this->getOriginalValue(explode('.', $error['property']), $jsonData);
            $constraints->add(
                new ConstraintViolation(
                    $error['message'],
                    null,
                    [],
                    'Downtime',
                    $error['property'],
                    $originalValue
                )
            );
        }
        return $constraints;
    }

    /**
     * Retrieve the original value corresponding to the property in the JSON data.
     *
     * @param array $root property path (ex: my_object.date or date)
     * @param array $data Data for which we want to find the value of the given key
     * @return string|null If found, returns the value otherwise null
     */
    private function getOriginalValue(array $root, array $data): ?string
    {
        $firstKeyToFind = array_shift($root);
        if (array_key_exists($firstKeyToFind, $data)) {
            if (is_array($data[$firstKeyToFind])) {
                return $this->getOriginalValue($root, $data[$firstKeyToFind]);
            } else {
                if (\is_bool($data[$firstKeyToFind])) {
                    return ($data[$firstKeyToFind]) ? 'true' : 'false';
                }
                return (string) $data[$firstKeyToFind];
            }
        }
        return null;
    }
}
