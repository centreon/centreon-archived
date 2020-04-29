<?php
/*
 * Copyright 2005-2019 Centreon
 * Centreon is developed by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give Centreon
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of Centreon choice, provided that
 * Centreon also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

namespace Centreon\Infrastructure\Event;

use Centreon\Infrastructure\Event\DispatcherLoaderInterface;

/**
 * This class is used to find and include a specific php file in a tree.
 *
 * @package Centreon\Domain\Entity
 */
class FileLoader implements DispatcherLoaderInterface
{
    /**
     * @var string Path where we will try to find php files
     */
    private $pathModules;

    /**
     * @var string Name of the php file to find in path
     */
    private $filename;

    /**
     * FileLoader constructor.
     *
     * @param string $pathModules Path where we will try to find php files
     * @param string $filename Name of the php file to find in path
     */
    public function __construct(string $pathModules, string $filename)
    {
        $this->pathModules = $pathModules;
        $this->filename = $filename;
    }

    /**
     * Include all php file found.
     *
     * @throws \Exception
     */
    public function load():void
    {
        if (! is_dir($this->pathModules)) {
            throw new \Exception(_('The path does not exist'));
        }
        $modules = scandir($this->pathModules);

        foreach ($modules as $module) {
            $fileToInclude = $this->pathModules . '/' . $module . '/' . $this->filename;
            if (preg_match('/^(?!\.)/', $module)
                && is_dir($this->pathModules . '/' . $module)
                && file_exists($fileToInclude)
            ) {
                require_once($fileToInclude);
            }
        }
    }
}
