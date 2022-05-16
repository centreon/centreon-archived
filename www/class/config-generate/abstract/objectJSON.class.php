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
 */

abstract class AbstractObjectJSON
{
    protected $backend_instance = null;
    protected $generate_filename = null;

    protected $dependencyInjector;

    protected $content = [];

    /**
     * @param \Pimple\Container $dependencyInjector
     * @return static
     */
    public static function getInstance(\Pimple\Container $dependencyInjector): static
    {
        /**
         * @var array<string, static>
         */
        static $instances = array();

        /**
         * @var class-string<static>
         */
        $calledClass = get_called_class();

        if (!isset($instances[$calledClass])) {
            $instances[$calledClass] = new $calledClass($dependencyInjector);
        }

        return $instances[$calledClass];
    }

    protected function __construct(\Pimple\Container $dependencyInjector)
    {
        $this->dependencyInjector = $dependencyInjector;
        $this->backend_instance = Backend::getInstance($this->dependencyInjector);
    }

    public function reset()
    {
    }

    protected function writeFile($dir)
    {
        $full_file = $dir . '/' . $this->generate_filename;
        if ($handle = fopen($full_file, 'w')) {
            if (!fwrite($handle, $this->content)) {
                throw new RuntimeException('Cannot write to file "' . $full_file . '"');
            }
            fclose($handle);
        } else {
            throw new Exception("Cannot open file " . $full_file);
        }
    }

    protected function generateFile($object)
    {
        $this->content = json_encode(['centreonBroker' => $object], JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
    }
}
