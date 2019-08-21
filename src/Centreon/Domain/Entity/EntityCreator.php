<?php
/**
 * Copyright 2005-2019 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */
declare(strict_types=1);

namespace Centreon\Domain\Entity;

use Centreon\Domain\Annotation\EntityDescriptor;
use Doctrine\Common\Annotations\AnnotationReader;
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
     * Create a new object entity based on the given values.
     * Used to create a new object entity with the values found in the database.
     *
     * @param string $className Class name to create
     * @param array $data Data used to fill the new object entity
     * @return mixed Return an new instance of the class
     * @throws \Exception
     */
    public static function createEntityByArray(string $className, array $data)
    {
        $ec = new self($className);
        return $ec->createByArray($data);
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
     * @return mixed Return an instance of class according to the class name given into constructor
     * @throws \Exception
     */
    public function createByArray(array $data)
    {
        if (!class_exists($this->className)) {
            throw new \Exception('The class' . $this->className . ' does not exist');
        }
        $this->readPublicMethod();
        $this->readAnnotations();
        $objectToSet = new $this->className;
        foreach ($data as $column => $value) {
            if (array_key_exists($column, $this->entityDescriptors)) {
                $descriptor = $this->entityDescriptors[$column];
                $setterMethod = ($descriptor !== null && $descriptor->modifier !== null)
                    ? $descriptor->modifier
                    : $this->createSetterMethod($column);
                if (array_key_exists($setterMethod, $this->publicMethods)) {
                    $parameters = $this->publicMethods[$setterMethod]->getParameters();
                    if (empty($parameters)) {
                        throw new \Exception("The public method {$this->className}::$setterMethod has no parameters");
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
                    throw new \Exception("The public method {$this->className}::$setterMethod is not found");
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
                throw new \Exception("The value cannot be null");
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
                    return (new \DateTime())->setTimestamp((int) $value);
                }
                throw new \Exception("Numeric value expected");
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
     * @throws \Doctrine\Common\Annotations\AnnotationException
     * @throws \ReflectionException
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
        return 'set' . $camelCaseName;
    }

    /**
     * Convert a string in camel case format to snake case
     *
     * @param string $camelCaseName Name in camelCase format
     * @return string Returns the name converted in snake case format
     */
    private function convertCamelCaseToSnakeCase(string $camelCaseName): string
    {
        $snakeCaseName = '';
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
}
