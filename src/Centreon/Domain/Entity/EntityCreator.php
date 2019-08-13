<?php
declare(strict_types=1);

namespace Centreon\Domain\Entity;

use Centreon\Domain\Annotation\EntityDescriptor;
use Doctrine\Common\Annotations\AnnotationReader;
use ReflectionClass;

class EntityCreator
{
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
     * @param string $className
     * @param array $data
     * @return mixed
     * @throws \Exception
     */
    public static function createEntityByArray(string $className, array $data)
    {
        $ec = new self($className);
        return $ec->createByArray($data);
    }

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
     * @param $value
     * @param $destinationType
     * @param bool $allowNull
     * @return bool|\DateTime|float|int|string
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
     * @throws \ReflectionException
     */
    private function readPublicMethod()
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
    private function readAnnotations()
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
     * @param string $property
     * @return string
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
     * @param string $camelCaseName
     * @return string
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
