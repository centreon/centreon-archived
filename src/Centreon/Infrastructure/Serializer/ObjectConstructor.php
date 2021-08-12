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

use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\Visitor\DeserializationVisitorInterface;
use Doctrine\Instantiator\Instantiator;

/**
 * This class is designed to allow the use of class constructors during deserialization phases.
 *
 * @package Centreon\Infrastructure\Serializer
 */
class ObjectConstructor implements ObjectConstructorInterface
{
    /**
     * @var Instantiator
     */
    private $instantiator;

    /**
     * @inheritDoc
     */
    public function construct(
        DeserializationVisitorInterface $visitor,
        ClassMetadata $metadata,
        $data,
        array $type,
        DeserializationContext $context
    ): ?object {
        $className = $metadata->name;
        $constructor = (new \ReflectionClass($className))->getConstructor();
        if ($constructor !== null && count($constructor->getParameters()) === 0) {
            return new $className();
        }

        return $this->getInstantiator()->instantiate($metadata->name);
    }

    /**
     * get instantiator
     *
     * @return Instantiator
     */
    private function getInstantiator(): Instantiator
    {
        if (null === $this->instantiator) {
            $this->instantiator = new Instantiator();
        }

        return $this->instantiator;
    }
}
