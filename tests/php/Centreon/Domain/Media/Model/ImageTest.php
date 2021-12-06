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

namespace Tests\Centreon\Domain\Media\Model;

use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Domain\Media\Model\Image;
use PHPUnit\Framework\TestCase;

class ImageTest extends TestCase
{
    /**
     * Test the name
     */
    public function testBadNameException(): void
    {
        $name = str_repeat('.', Image::MAX_NAME_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $name,
                strlen($name),
                Image::MAX_NAME_LENGTH,
                'Image::name'
            )->getMessage()
        );
        (new Image())->setName($name);
    }

    /**
     * Test the path
     */
    public function testBadPathException(): void
    {
        $path = str_repeat('.', Image::MAX_PATH_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $path,
                strlen($path),
                Image::MAX_PATH_LENGTH,
                'Image::path'
            )->getMessage()
        );
        (new Image())->setPath($path);
    }

    /**
     * Test the comments
     */
    public function testBadCommentsException(): void
    {
        $comment = str_repeat('.', Image::MAX_COMMENTS_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $comment,
                strlen($comment),
                Image::MAX_COMMENTS_LENGTH,
                'Image::comment'
            )->getMessage()
        );
        (new Image())->setComment($comment);
    }
}
