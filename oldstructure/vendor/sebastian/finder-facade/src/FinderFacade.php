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
    use Symfony\Component\Finder\Finder;

    /**
     * Convenience wrapper for Symfony's Finder component.
     *
     * @author    Sebastian Bergmann <sebastian@phpunit.de>
     * @copyright 2012-2013 Sebastian Bergmann <sebastian@phpunit.de>
     * @license   http://www.opensource.org/licenses/BSD-3-Clause  The BSD 3-Clause License
     * @version   Release: @package_version@
     * @link      http://github.com/sebastianbergmann/finder-facade/tree
     * @since     Class available since Release 1.0.0
     */
    class FinderFacade
    {
        /**
         * @var array
         */
        protected $items = array();

        /**
         * @var array
         */
        protected $excludes = array();

        /**
         * @var array
         */
        protected $names = array();


        /**
         * @var array
         */
        protected $notNames = array();

        /**
         * @param array $items
         * @param array $excludes
         * @param array $names
         * @param array $notNames
         */
        public function __construct(array $items = array(), array $excludes = array(), array $names = array(), array $notNames = array())
        {
            $this->items    = $items;
            $this->excludes = $excludes;
            $this->names    = $names;
            $this->notNames = $notNames;
        }

        /**
         * @return array
         */
        public function findFiles()
        {
            $files   = array();
            $finder  = new Finder;
            $iterate = FALSE;

            foreach ($this->items as $item) {
                if (!is_file($item)) {
                    $finder->in($item);
                    $iterate = TRUE;
                }

                else {
                    $files[] = realpath($item);
                }
            }

            foreach ($this->excludes as $exclude) {
                $finder->exclude($exclude);
            }

            foreach ($this->names as $name) {
                $finder->name($name);
            }

            foreach ($this->notNames as $notName) {
                $finder->notName($notName);
            }

            if ($iterate) {
                foreach ($finder as $file) {
                    $files[] = $file->getRealpath();
                }
            }

            return $files;
        }

        /**
         * @param string $file
         */
        public function loadConfiguration($file)
        {
            $configuration = new Configuration($file);
            $configuration = $configuration->parse();

            $this->items    = $configuration['items'];
            $this->excludes = $configuration['excludes'];
            $this->names    = $configuration['names'];
            $this->notNames = $configuration['notNames'];
        }
    }
}
