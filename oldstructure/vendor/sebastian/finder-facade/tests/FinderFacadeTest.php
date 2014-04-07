<?php
/**
 * FinderFacade
 *
 * Copyright (c) 2012-2013, Sebastian Bergmann <sebastian@phpunit.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Sebastian Bergmann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @package   FinderFacade
 * @author    Sebastian Bergmann <sebastian@phpunit.de>
 * @copyright 2012-2013 Sebastian Bergmann <sebastian@phpunit.de>
 * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
 * @since     File available since Release 1.0.0
 */

namespace SebastianBergmann\FinderFacade\Tests
{
    use SebastianBergmann\FinderFacade\FinderFacade;

    class FinderFacadeTest extends \PHPUnit_Framework_TestCase
    {
        protected $fixtureDir;

        protected function setUp()
        {
            $this->fixtureDir = __DIR__ . DIRECTORY_SEPARATOR . 'fixture' . DIRECTORY_SEPARATOR;
        }

        /**
         * @covers SebastianBergmann\FinderFacade\FinderFacade::__construct
         * @covers SebastianBergmann\FinderFacade\FinderFacade::findFiles
         */
        public function testFilesCanBeFoundBasedOnConstructorArguments()
        {
            $facade = new FinderFacade(
              array($this->fixtureDir, $this->fixtureDir . 'bar.phtml'),
              array('bar'),
              array('*.php'),
              array('*.fail.php')
            );

            $this->assertEquals(
              array(
                $this->fixtureDir . 'bar.phtml',
                $this->fixtureDir . 'foo' . DIRECTORY_SEPARATOR . 'bar.php'
              ),
              $facade->findFiles()
            );
        }

        /**
         * @covers SebastianBergmann\FinderFacade\FinderFacade::loadConfiguration
         */
        public function testFilesCanBeFoundBasedOnXmlConfiguration()
        {
            $facade = new FinderFacade;
            $facade->loadConfiguration($this->fixtureDir . 'test.xml');

            $this->assertEquals(
              array(
                $this->fixtureDir . 'bar.phtml',
                $this->fixtureDir . 'foo' . DIRECTORY_SEPARATOR . 'bar.php'
              ),
              $facade->findFiles()
            );
        }
    }
}
