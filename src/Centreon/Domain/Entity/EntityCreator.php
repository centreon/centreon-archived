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
     * @return mixed Entity according to the class given
     * @throws \Exception
     */
    public function createByArray(array $data)
    {
        if (!class_exists($this->className)) {
            throw new \Exception('The class' . $this->className . ' does not exist');
        }
        $this->readPublicMethod();
        $this->readAnnotations();
        $object = new $this->className;
        foreach ($data as $column => $value) {
            if (array_key_exists($column, $this->entityDescriptors)) {
                $descriptor = $this->entityDescriptors[$column];
                $setterMethod = ($descriptor !== null && $descriptor->modifier !== null)
                    ? $descriptor->modifier
                    : $this->createSetterMethod($column);
                if (array_key_exists($setterMethod, $this->publicMethods)) {
                    $parameters = $this->publicMethods[$setterMethod]->getParameters();
                    if (empty($parameters)) {
                        throw new \Exception("The plublic method {$this->className}::$setterMethod has no parameters");
                    }
                    $firstParameter = $parameters[0];
                    if ($firstParameter->hasType()) {
                        $value = $this->convertValueBeforeInsert($value, $firstParameter->getType()->getName());
                    }

                    call_user_func_array(array($object, $setterMethod), [$value]);
                } else {
                    throw new \Exception("The public method {$this->className}::$setterMethod is not found");
                }
            }
        }
        return $object;
    }

    /**
     * @param $value
     * @param $destinationType
     * @return bool|\DateTime|float|int|string
     * @throws \Exception
     */
    private function convertValueBeforeInsert($value, $destinationType)
    {
        switch ($destinationType) {
            case 'double':
                return (double)$value;
            case 'integer':
            case 'int':
                return (int)$value;
            case 'string':
                return (string)$value;
            case 'boolean':
            case 'bool':
                return (bool)$value;
            case 'DateTime':
                if (is_numeric($value)) {
                    return (new \DateTime())->setTimestamp((int)$value);
                }
            default:
                return $value;
        }
    }

    private function readPublicMethod()
    {
        $this->publicMethods = [];
        $reflectionClass = new ReflectionClass($this->className);
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
