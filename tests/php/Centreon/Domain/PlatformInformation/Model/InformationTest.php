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

namespace Tests\Centreon\Domain\PlatformInformation\Model;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Common\Assertion\AssertionException;
use Centreon\Domain\PlatformInformation\Model\Information;

class InformationTest extends TestCase
{
    /**
     * Too long Key Test.
     */
    public function testKeyTooLongException(): void
    {
        $key = str_repeat('.', Information::MAX_KEY_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $key,
                strlen($key),
                Information::MAX_KEY_LENGTH,
                'Information::key'
            )->getMessage()
        );
        (new Information())->setKey($key);
    }

    /**
     * Too Short Key Test.
     */
    public function testKeyTooShortException(): void
    {
        $key = str_repeat('.', Information::MIN_KEY_LENGTH - 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::minLength(
                $key,
                strlen($key),
                Information::MIN_KEY_LENGTH,
                'Information::key'
            )->getMessage()
        );
        (new Information())->setKey($key);
    }

    /**
     * Too long Value Test.
     */
    public function testValueTooLongException(): void
    {
        $value = str_repeat('.', Information::MAX_VALUE_LENGTH + 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::maxLength(
                $value,
                strlen($value),
                Information::MAX_VALUE_LENGTH,
                'Information::value'
            )->getMessage()
        );
        (new Information())->setValue($value);
    }

    /**
     * Too long Value Test.
     */
    public function testValueTooShortException(): void
    {
        $value = str_repeat('.', Information::MIN_VALUE_LENGTH - 1);
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            AssertionException::minLength(
                $value,
                strlen($value),
                Information::MIN_VALUE_LENGTH,
                'Information::value'
            )->getMessage()
        );
        (new Information())->setValue($value);
    }

    /**
     * @return array<Information>
     * @throws \Assert\AssertionFailedException
     */
    public static function createEntities(): array
    {
        $request = [
            'isRemote' => true,
            'centralServerAddress' => '1.1.1.10',
            'apiUsername' => 'admin',
            'apiCredentials' => 'centreon',
            'apiScheme' => 'http',
            'apiPort' => 80,
            'apiPath' => 'centreon',
            'peerValidation' => false
        ];

        $information = [];
        foreach ($request as $key => $value) {
            $newInformation = (new Information())
                ->setKey($key)
                ->setValue($value);
            $information[] = $newInformation;
        }

        return $information;
    }
}
