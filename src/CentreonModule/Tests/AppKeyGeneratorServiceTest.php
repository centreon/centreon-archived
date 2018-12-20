<?php

namespace Centreon\Tests;

use PHPUnit\Framework\TestCase;
use Centreon\Domain\Service\AppKeyGeneratorService;

class AppKeyGeneratorServiceTest extends TestCase
{
    const MD5_REGEX = '/^[a-f0-9]{32}$/i';

    public function testGenerateKey()
    {
        $service = $this->getMockBuilder(AppKeyGeneratorService::class)->getMock();
        $key = $service->generateKey();

        /**
         *  string generated is an md5
         */
        $this->assertStringMatchesFormat(self::MD5_REGEX, $key);

        /**
         * second string different and matches format
         */
        $key2 = $service->generateKey();
        $this->assertStringMatchesFormat(self::MD5_REGEX, $key2);
        $this->assertNotSame($key,$key2);
    }
}