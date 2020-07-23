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
declare(strict_types=1);

namespace Centreon\Domain\Entity;

use Centreon\Domain\Annotation\EntityDescriptor;
use Doctrine\Common\Annotations\AnnotationException;
use Doctrine\Common\Annotations\AnnotationReader;
use Centreon\Domain\Service\EntityDescriptorMetadataInterface;
use Centreon\Domain\Contact\Contact;
use ReflectionClass;

class EntityCreator
{
    /**
     * @var string Class name to create
     */
    private $className;

    /**
     * @var EntityDescriptor[]
     */
    private $entityDescriptors;

    /**
     * @var \ReflectionMethod[]
     */
    private $publicMethods;

    /**
     * @var Contact
     */
    private static $contact;

    /**
     * Create a new object entity based on the given values.
     * Used to create a new object entity with the values found in the database.
     *
     * @param string $className Class name to create
     * @param array $data Data used to fill the new object entity
     * @param string|null $prefix The prefix is used to retrieve only certain records when the table contains data
     * from more than one entity
     * @return mixed Return an new instance of the class
     * @throws \Exception
     */
    public static function createEntityByArray(string $className, array $data, string $prefix = null)
    {
        return (new self($className))->createByArray($data, $prefix);
    }

    /**
     * Set contact
     *
     * @param Contact $contact The contact
     */
    public static function setContact(Contact $contact): void
    {
        static::$contact = $contact;
    }

    /**
     * EntityCreator constructor.
     *
     * @param string $className
     */
    public function __construct(string $className)
    {
        $this->className = $className;
    }

    /**
     * Create an entity and complete it according to the data array
     *
     * @param array $data Array that contains the data that will be used to complete entity
     * @param string|null $prefix The prefix is used to retrieve only certain records when the table contains data
     * from more than one entity
     * @return mixed Return an instance of class according to the class name given into constructor
     * @throws AnnotationException
     * @throws \ReflectionException
     * @throws \Exception
     */
    public function createByArray(array $data, string $prefix = null)
    {
        if (!class_exists($this->className)) {
            throw new \Exception(
                sprintf(_('The class %s does not exist'), $this->className)
            );
        }
        $this->readPublicMethod();
        $this->readAnnotations();
        $objectToSet = (new ReflectionClass($this->className))->newInstance();

        if (!empty($prefix)) {
            // If a prefix is defined, we keep only $data for which the keys start
            // with the prefix
            $data = array_filter($data, function ($column) use ($prefix) {
                if (substr($column, 0, strlen($prefix)) === $prefix) {
                    return true;
                }
                return false;
            }, ARRAY_FILTER_USE_KEY);

            // Next, we remove the prefix
            $newData = [];
            foreach ($data as $column => $value) {
                $column = substr($column, strlen($prefix));
                $newData[$column] = $value;
            }
            $data = $newData;
        }

        foreach ($data as $column => $value) {
            if (array_key_exists($column, $this->entityDescriptors)) {
                $descriptor = $this->entityDescriptors[$column];
                $setterMethod = ($descriptor !== null && $descriptor->modifier !== null)
                    ? $descriptor->modifier
                    : $this->createSetterMethod($column);
                if (array_key_exists($setterMethod, $this->publicMethods)) {
                    $parameters = $this->publicMethods[$setterMethod]->getParameters();
                    if (empty($parameters)) {
                        throw new \Exception(
                            sprintf(_('The public method %s::$setterMethod has no parameters'), $this->className)
                        );
                    }
                    $firstParameter = $parameters[0];
                    if ($firstParameter->hasType()) {
                        try {
                            $value = $this->convertValueBeforeInsert(
                                $value,
                                $firstParameter->getType()->getName(),
                                $firstParameter->allowsNull()
                            );
                        } catch (\Exception $error) {
                            throw new \Exception(
                                sprintf(
                                    '[%s::%s]: %s',
                                    $this->className,
                                    $setterMethod,
                                    $error->getMessage()
                                )
                            );
                        }
                    }

                    call_user_func_array(array($objectToSet, $setterMethod), [$value]);
                } else {
                    throw new \Exception(
                        sprintf(_('The public method %s::$setterMethod is not found'), $this->className)
                    );
                }
            }
        }
        return $objectToSet;
    }

    /**
     * Convert the value according to the type given.
     *
     * @param mixed $value Value to convert
     * @param string $destinationType Destination type
     * @param bool $allowNull Indicates whether the null value is allowed
     * @return mixed Return the converted value
     * @throws \Exception
     */
    private function convertValueBeforeInsert(
        $value,
        $destinationType,
        bool $allowNull = true
    ) {
        if (is_null($value)) {
            if ($allowNull) {
                return $value;
            } else {
                throw new \Exception(_('The value cannot be null'));
            }
        }

        switch ($destinationType) {
            case 'double':
            case 'float':
                return (float) $value;
            case 'int':
                return (int) $value;
            case 'string':
                return (string) $value;
            case 'bool':
                return (bool) $value;
            case 'DateTime':
                if (is_numeric($value)) {
                    $value = (new \DateTime())->setTimestamp((int) $value);
                    if (static::$contact !== null) {
                        $value->setTimezone(static::$contact->getTimezone());
                    }
                    return $value;
                }
                throw new \Exception(_('Numeric value expected'));
            default:
                return $value;
        }
    }

    /**
     * Read the public method of the class.
     *
     * @throws \ReflectionException
     */
    private function readPublicMethod(): void
    {
        $this->publicMethods = [];
        $reflectionClass = new \ReflectionClass($this->className);
        foreach ($reflectionClass->getMethods() as $method) {
            if ($method->isPublic()) {
                $this->publicMethods[$method->getName()] = $method;
            }
        }
    }

    /**
     * Read all specific annotations.
     *
     * @throws \ReflectionException
     * @throws AnnotationException
     */
    private function readAnnotations(): void
    {
        $this->entityDescriptors = [];
        $reflectionClass = new ReflectionClass($this->className);
        $properties = $reflectionClass->getProperties();
        $reader = new AnnotationReader();
        foreach ($properties as $property) {
            /**
             * @var $annotation EntityDescriptor
             */
            $annotation = $reader->getPropertyAnnotation(
                $property,
                EntityDescriptor::class
            );
            $key = ($annotation !== null && $annotation->column !== null)
                ? $annotation->column
                : $this->convertCamelCaseToSnakeCase($property->getName());
            $this->entityDescriptors[$key] = $annotation;
        }

        // load entity descriptor data via static method with metadata
        if ($reflectionClass->isSubclassOf(EntityDescriptorMetadataInterface::class)) {
            foreach ($this->className::loadEntityDescriptorMetadata() as $column => $modifier) {
                $descriptor = new EntityDescriptor();
                $descriptor->column = $column;
                $descriptor->modifier = $modifier;

                $this->entityDescriptors[$column] = $descriptor;
            }
        }
    }

    /**
     * Returns the name of the setter method based on the property name.
     *
     * @param string $property Property name for which we create the setter method
     * @return string Returns the name of the setter method
     */
    private function createSetterMethod(string $property): string
    {
        $camelCaseName = '';
        for ($index = 0; $index < strlen($property); $index++) {
            $char = $property[$index];
            if ($index === 0) {
                $camelCaseName .= strtoupper($char);
            } elseif ($char === '_') {
                $index++;
                $camelCaseName .= strtoupper($property[$index]);
            } else {
                $camelCaseName .= $char;
            }
        }
        return 'set' . ucfirst($this->convertSnakeCaseToCamelCase($property));
    }

    /**
     * Convert a string in camel case format to snake case
     *
     * @param string $camelCaseName Name in camelCase format
     * @return string Returns the name converted in snake case format
     */
    public static function convertCamelCaseToSnakeCase(string $camelCaseName): string
    {
        $snakeCaseName = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $camelCaseName));
        for ($index = 0; $index < strlen($camelCaseName); $index++) {
            $char = $camelCaseName[$index];
            if (strtoupper($char) === $char) {
                $snakeCaseName .= '_' . strtolower($char);
            } else {
                $snakeCaseName .= $char;
            }
        }
        return $snakeCaseName;
    }

    /**
     * Convert a string in snake case format to camel case
     *
     * @param string $snakeCaseName Name in snake format
     * @return string Returns the name converted in camel case format
     */
    public static function convertSnakeCaseToCamelCase(string $snakeCaseName): string
    {
        $camelCaseName = '';
    $camelCaseName = lcfirst(str_replace('_', '', ucwords($snakeCaseName, '_')));

        return $camelCaseName;
    }
}
