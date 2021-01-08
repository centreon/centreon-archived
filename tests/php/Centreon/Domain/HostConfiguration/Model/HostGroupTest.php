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
use PHPUnit\Framework\TestCase;
use Centreon\Domain\Media\Model\Image;
use Centreon\Domain\HostConfiguration\Model\HostGroup;
use Centreon\Domain\HostConfiguration\Exception\HostGroupArgumentException;

/**
 * This class is designed to test all setters of the HostGroup entity, especially those with exceptions.
 *
 * @package Tests\Centreon\Domain\HostConfiguration\Model
 */
class HostGroupTest extends TestCase
{
    /**
     * Too long name test
     */
    public function testNameTooLongException(): void
    {
        $name = str_repeat('.', HostGroup::MAX_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $name,
                strlen($name),
                HostGroup::MAX_NAME_LENGTH,
                'HostGroup::name'
            )->getMessage()
        );
        (new HostGroup('hg-name'))->setName($name);
    }

    /**
     * Too long alias test
     */
    public function testAliasTooLongException(): void
    {
        $alias = str_repeat('.', HostGroup::MAX_ALIAS_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $alias,
                strlen($alias),
                HostGroup::MAX_ALIAS_LENGTH,
                'HostGroup::alias'
            )->getMessage()
        );
        (new HostGroup('hg-name'))->setAlias($alias);
    }

    /**
     * Test the notes
     */
    public function testBadNotes(): void
    {
        $notes = str_repeat('.', HostGroup::MAX_NOTES + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $notes,
                strlen($notes),
                HostGroup::MAX_NOTES,
                'HostGroup::notes'
            )->getMessage()
        );
        (new HostGroup('hg-name'))->setNotes($notes);
    }

    /**
     * Test the url notes
     */
    public function testBadNotesUrl(): void
    {
        $notesUrl = str_repeat('.', HostGroup::MAX_NOTES_URL + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $notesUrl,
                strlen($notesUrl),
                HostGroup::MAX_NOTES_URL,
                'HostGroup::notesUrl'
            )->getMessage()
        );
        (new HostGroup('hg-name'))->setNotesUrl($notesUrl);
    }

    /**
     * Test the action url
     */
    public function testBadActionUrl(): void
    {
        $actionUrl = str_repeat('.', HostGroup::MAX_ACTION_URL + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $actionUrl,
                strlen($actionUrl),
                HostGroup::MAX_ACTION_URL,
                'HostGroup::actionUrl'
            )->getMessage()
        );
        (new HostGroup('hg-name'))->setActionUrl($actionUrl);
    }

    /**
     * Too long comments test
     */
    public function testCommentTooLongException(): void
    {
        $comments = str_repeat('.', HostGroup::MAX_COMMENTS_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $comments,
                strlen($comments),
                HostGroup::MAX_COMMENTS_LENGTH,
                'HostGroup::comment'
            )->getMessage()
        );
        (new HostGroup('hg-name'))->setComment($comments);
    }

    /**
     * Too long geo coords test
     */
    public function testGeoCoordsTooLongException(): void
    {
        $geoCoords = str_repeat('.', HostGroup::MAX_GEO_COORDS + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $geoCoords,
                strlen($geoCoords),
                HostGroup::MAX_GEO_COORDS,
                'HostGroup::geoCoords'
            )->getMessage()
        );
        (new HostGroup('hg-name'))->setGeoCoords($geoCoords);
    }

    /**
     * @return HostGroup
     * @throws \Assert\AssertionFailedException
     */
    public static function createEntity(): HostGroup
    {
        return (new HostGroup('hg-name'))
            ->setId(10)
            ->setName('hg-name')
            ->setAlias('host group name')
            ->setActivated(true)
            ->setIcon((new Image())->setId(1)->setName('my icon')->setPath('/'))
            ->setIconMap((new Image())->setId(2)->setName('my map image')->setPath('/'));
    }
}
