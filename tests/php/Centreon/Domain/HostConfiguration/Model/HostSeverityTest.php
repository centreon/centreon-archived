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

namespace Tests\Centreon\Domain\HostConfiguration\Model;

use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Domain\HostConfiguration\Model\HostSeverity;
use PHPUnit\Framework\TestCase;

/**
 * This class is designed to test all setters of the HostSeverity entity, especially those with exceptions.
 *
 * @package Tests\Centreon\Domain\HostConfiguration\Model
 */
class HostSeverityTest extends TestCase
{
    /**
     * Too long name test
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
        (new HostSeverity())->setName($name);
    }
    
    /**
     * Too long alias test
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
        (new HostSeverity())->setAlias($alias);
    }
    
    /**
     * Too long comments test
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
        (new HostSeverity())->setComments($comments);
    }
    
    /**
     * IsActivated property test
     */
    public function testIsActivatedProperty(): void
    {
        $hostSeverity = new HostSeverity();
        $this->assertTrue($hostSeverity->isActivated());
        $hostSeverity->setIsActivated(false);
        $this->assertFalse($hostSeverity->isActivated());
    }
    
    /**
     * Id property test
     */
    public function testIdProperty(): void
    {
        $newHostId = 1;
        $hostSeverity = new HostSeverity();
        $this->assertNull($hostSeverity->getId());
        $hostSeverity->setId($newHostId);
        $this->assertEquals($newHostId, $hostSeverity->getId());
    }
    
    /**
     * @return HostSeverity
     * @throws \Assert\AssertionFailedException
     */
    public static function createEntity(): HostSeverity
    {
        return (new HostSeverity())
            ->setId(10)
            ->setName('Severity')
            ->setAlias('Alias severity')
            ->setIsActivated(true)
            ->setComments("blablabla");
    }
}