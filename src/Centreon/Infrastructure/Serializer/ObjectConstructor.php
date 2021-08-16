<?php

/*
 * Copyright 2005 - 2021 Centreon (https://www.centreon.com/)
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

namespace Centreon\Infrastructure\Serializer;

use Centreon\Infrastructure\Serializer\Exception\SerializerException;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;

/**
 * This class is designed to allow the use of class constructors during deserialization phases.
 *
 * @package Centreon\Infrastructure\Serializer
 */
class ObjectConstructor implements ObjectConstructorInterface
{
    /**
     * {@inheritDoc}
     * @throws SerializerException
     * @throws \ReflectionException
     */
    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ): ?object {
        $className = $metadata->name;
        if (!class_exists($className)) {
            throw new \ReflectionException(sprintf(_('Class %s not found'), $className));
        }
        $reflection = new \ReflectionClass($className);
        $constructor = $reflection->getConstructor();
        if ($constructor !== null && $constructor->getNumberOfParameters() > 0) {
            $parameters = $constructor->getParameters();
            $constructorParameters = [];
            foreach ($parameters as $parameter) {
                if (array_key_exists($parameter->getName(), $data)) {
                    $constructorParameters[$parameter->getPosition()] = $data[$parameter->getName()];
                } elseif ($parameter->isOptional() === true) {
                    $constructorParameters[$parameter->getPosition()] = $parameter->getDefaultValue();
                }
            }
            try {
                return $reflection->newInstanceArgs($constructorParameters);
            } catch (\Throwable $ex) {
                if ($ex instanceof \ArgumentCountError) {
                    throw SerializerException::notEnoughConstructorArguments($className, $ex);
                }
                throw $ex;
            }
        } else {
            return new $className();
        }
    }
}
