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
use Centreon\Domain\HostConfiguration\Model\HostSeverity;
use Centreon\Domain\Media\Model\Image;
use PHPUnit\Framework\TestCase;

/**
 * This class is designed to test all setters of the HostSeverity entity, especially those with exceptions.
 *
 * @package Tests\Centreon\Domain\HostConfiguration\Model
 */
class HostSeverityTest extends TestCase
{
    /**
     * @var Image Define the image that should be associated with this severity.
     */
    protected $icon;

    protected function setUp(): void
    {
        $this->icon = (new Image())->setId(1)->setName('my icon')->setPath('/');
    }

    /**
     * Too long name test
     * @throws \Assert\AssertionFailedException
     */
    public function testNameTooLongException(): void
    {
        $name = str_repeat('.', HostSeverity::MAX_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $name,
                strlen($name),
                HostSeverity::MAX_NAME_LENGTH,
                'HostSeverity::name'
            )->getMessage()
        );
        new HostSeverity($name, 'alias', 42, $this->icon);
    }

    /**
     * Too short name test
     * @throws \Assert\AssertionFailedException
     */
    public function testNameTooShortException(): void
    {
        $name = str_repeat('.', HostSeverity::MIN_NAME_LENGTH - 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::minLength(
                $name,
                strlen($name),
                HostSeverity::MIN_NAME_LENGTH,
                'HostSeverity::name'
            )->getMessage()
        );
        new HostSeverity($name, 'alias', 42, $this->icon);
    }

    /**
     * Too long alias test
     * @throws \Assert\AssertionFailedException
     */
    public function testAliasTooLongException(): void
    {
        $alias = str_repeat('.', HostSeverity::MAX_ALIAS_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $alias,
                strlen($alias),
                HostSeverity::MAX_ALIAS_LENGTH,
                'HostSeverity::alias'
            )->getMessage()
        );
        new HostSeverity('name', $alias, 42, $this->icon);
    }

    /**
     * Too short alias test
     * @throws \Assert\AssertionFailedException
     */
    public function testAliasTooShortException(): void
    {
        $alias = str_repeat('.', HostSeverity::MIN_ALIAS_LENGTH - 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::minLength(
                $alias,
                strlen($alias),
                HostSeverity::MIN_ALIAS_LENGTH,
                'HostSeverity::alias'
            )->getMessage()
        );
        new HostSeverity('name', $alias, 42, $this->icon);
    }

    /**
     * Too long level test
     * @throws \Assert\AssertionFailedException
     */
    public function testLevelTooLongException(): void
    {
        $level = HostSeverity::MAX_LEVEL_NUMBER + 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::max(
                $level,
                HostSeverity::MAX_LEVEL_NUMBER,
                'HostSeverity::level'
            )->getMessage()
        );
        new HostSeverity('name', 'alias', $level, $this->icon);
    }

    /**
     * Too short rrd test
     */
    public function testLevelTooShortException(): void
    {
        $level = HostSeverity::MIN_LEVEL_NUMBER - 1;
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::min(
                $level,
                HostSeverity::MIN_LEVEL_NUMBER,
                'HostSeverity::level'
            )->getMessage()
        );
        new HostSeverity('name', 'alias', $level, $this->icon);
    }

    /**
     * Too long comments test
     * @throws \Assert\AssertionFailedException
     */
    public function testCommentsTooLongException(): void
    {
        $comments = str_repeat('.', HostSeverity::MAX_COMMENTS_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $comments,
                strlen($comments),
                HostSeverity::MAX_COMMENTS_LENGTH,
                'HostSeverity::comments'
            )->getMessage()
        );
        (new HostSeverity('name', 'alias', 42, $this->icon))->setComments($comments);
    }

    /**
     * IsActivated property test
     */
    public function testIsActivatedProperty(): void
    {
        $hostSeverity = new HostSeverity('name', 'alias', 42, $this->icon);
        $this->assertTrue($hostSeverity->isActivated());
        $hostSeverity->setActivated(false);
        $this->assertFalse($hostSeverity->isActivated());
    }

    /**
     * Id property test
     */
    public function testIdProperty(): void
    {
        $newHostId = 1;
        $hostSeverity = new HostSeverity('name', 'alias', 42, $this->icon);
        $hostSeverity->setId($newHostId);
        $this->assertEquals($newHostId, $hostSeverity->getId());
    }

    /**
     * @return HostSeverity
     * @throws \Assert\AssertionFailedException
     */
    public static function createEntity(): HostSeverity
    {
        $icon = (new Image())->setId(1)->setName('my icon')->setPath('/');
        return (new HostSeverity('Severity', 'Alias severity', 42, $icon))
            ->setId(10)
            ->setActivated(true)
            ->setComments("blablabla");
    }
}
