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

namespace SebastianBergmann\FinderFacade
{
    use TheSeer\fDOM\fDOMDocument;

    /**
     * <code>
     * <fileset>
     *   <include>
     *    <directory>/path/to/directory</directory>
     *    <file>/path/to/file</file>
     *   </include>
     *   <exclude>/path/to/directory</exclude>
     *   <name>*.php</name>
     * </fileset>
     * </code>
     *
     * @author    Sebastian Bergmann <sebastian@phpunit.de>
     * @copyright 2012-2013 Sebastian Bergmann <sebastian@phpunit.de>
     * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
     * @version   Release: @package_version@
     * @link      http://github.com/sebastianbergmann/finder-facade/tree
     * @since     Class available since Release 1.0.0
     */
    class Configuration
    {
        /**
         * @var string
         */
        protected $basePath;

        /**
         * @var fDOMDocument
         */
        protected $xml;

        /**
         * @param string $file
         */
        public function __construct($file)
        {
            $this->basePath = dirname($file);

            $this->xml = new fDOMDocument;
            $this->xml->load($file);
        }

        /**
         * @param  string $xpath
         * @return array
         */
        public function parse($xpath = '')
        {
            $result = array(
              'items' => array(), 'excludes' => array(), 'names' => array(), 'notNames' => array()
            );

            foreach ($this->xml->getDOMXPath()->query($xpath . 'include/directory') as $item) {
                $result['items'][] = $this->toAbsolutePath($item->nodeValue);
            }

            foreach ($this->xml->getDOMXPath()->query($xpath . 'include/file') as $item) {
                $result['items'][] = $this->toAbsolutePath($item->nodeValue);
            }

            foreach ($this->xml->getDOMXPath()->query($xpath . 'exclude') as $exclude) {
                $result['excludes'][] = $exclude->nodeValue;
            }

            foreach ($this->xml->getDOMXPath()->query($xpath . 'name') as $name) {
                $result['names'][] = $name->nodeValue;
            }

            foreach ($this->xml->getDOMXPath()->query($xpath . 'notName') as $name) {
                $result['notNames'][] = $name->nodeValue;
            }

            return $result;
        }

        /**
         * @param  string $path
         * @return string
         */
        protected function toAbsolutePath($path)
        {
            // Check whether the path is already absolute.
            if ($path[0] === '/' || $path[0] === '\\' ||
                (strlen($path) > 3 && ctype_alpha($path[0]) &&
                 $path[1] === ':' && ($path[2] === '\\' || $path[2] === '/'))) {
                return $path;
            }

            // Check whether a stream is used.
            if (strpos($path, '://') !== FALSE) {
                return $path;
            }

            return $this->basePath . DIRECTORY_SEPARATOR . $path;
        }
    }
}
