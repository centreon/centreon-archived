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
 *
 */
declare(strict_types=1);

namespace Tests\Centreon\Domain\HostConfiguration\Model;

use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Domain\HostConfiguration\Model\HostCategory;
use PHPUnit\Framework\TestCase;

/**
 * This class is designed to test all setters of the HostCategory entity, especially those with exceptions.
 *
 * @package Tests\Centreon\Domain\HostConfiguration\Model
 */
class HostCategoryTest extends TestCase
{
    /**
     * Too long name test
     */
    public function testNameTooShortException(): void
    {
        $name = str_repeat('.', HostCategory::MIN_NAME_LENGTH - 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::minLength(
                $name,
                strlen($name),
                HostCategory::MIN_NAME_LENGTH,
                'HostCategory::name'
            )->getMessage()
        );
        new HostCategory($name, 'alias');
    }

    /**
     * Too long name test
     */
    public function testNameTooLongException(): void
    {
        $name = str_repeat('.', HostCategory::MAX_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $name,
                strlen($name),
                HostCategory::MAX_NAME_LENGTH,
                'HostCategory::name'
            )->getMessage()
        );
        new HostCategory($name, 'alias');
    }

    /**
     * Too short alias test
     */
    public function testAliasTooShortException(): void
    {
        $alias = str_repeat('.', HostCategory::MIN_ALIAS_LENGTH - 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::minLength(
                $alias,
                strlen($alias),
                HostCategory::MIN_ALIAS_LENGTH,
                'HostCategory::alias'
            )->getMessage()
        );
        new HostCategory('name', $alias);
    }

    /**
     * Too long alias test
     */
    public function testAliasTooLongException(): void
    {
        $alias = str_repeat('.', HostCategory::MAX_ALIAS_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $alias,
                strlen($alias),
                HostCategory::MAX_ALIAS_LENGTH,
                'HostCategory::alias'
            )->getMessage()
        );
        new HostCategory('name', $alias);
    }

    /**
     * Too long comments test
     */
    public function testCommentsTooLongException(): void
    {
        $comments = str_repeat('.', HostCategory::MAX_COMMENTS_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $comments,
                strlen($comments),
                HostCategory::MAX_COMMENTS_LENGTH,
                'HostCategory::comments'
            )->getMessage()
        );
        (new HostCategory('name', 'alias'))->setComments($comments);
    }

    /**
     * IsActivated property test
     */
    public function testIsActivatedProperty(): void
    {
        $hostCategory = new HostCategory('name', 'alias');
        $this->assertTrue($hostCategory->isActivated());
        $hostCategory->setActivated(false);
        $this->assertFalse($hostCategory->isActivated());
    }

    /**
     * Id property test
     */
    public function testIdProperty(): void
    {
        $newHostId = 1;
        $hostCategory = new HostCategory('name', 'alias');
        $this->assertNull($hostCategory->getId());
        $hostCategory->setId($newHostId);
        $this->assertEquals($newHostId, $hostCategory->getId());
    }

    /**
     * @return HostCategory
     * @throws \Assert\AssertionFailedException
     */
    public static function createEntity(): HostCategory
    {
        return (new HostCategory('name', 'alias'))
            ->setId(10)
            ->setName('Category')
            ->setAlias('Alias category')
            ->setActivated(true)
            ->setComments("blablabla");
    }
}
