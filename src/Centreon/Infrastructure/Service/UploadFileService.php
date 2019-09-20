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

namespace Centreon\Infrastructure\Service;

use Psr\Container\ContainerInterface;
use Centreon\Infrastructure\FileManager\File;

class UploadFileService
{
    /**
     * @var string $filesRequest
     */
    protected $filesRequest;

    /**
     * Construct
     *
     * @param \Psr\Container\ContainerInterface $services
     * @param array $filesRequest Copy of $_FILES
     */
    public function __construct(ContainerInterface $services, array $filesRequest = null)
    {
        $this->filesRequest = $filesRequest;
    }

    /**
     * Get all files
     *
     * @return array
     */
    public function getFiles(string $fieldName, array $withExtension = null) : array
    {
        $filesFromRequest = $this->prepare($fieldName);

        $result = [];
        foreach ($filesFromRequest as $data) {
            $file = new File($data);

            if ($withExtension !== null && in_array($file->getExtension(), $withExtension) === false) {
                continue;
            }

            $result[] = $file;
        }

        return $result;
    }

    public function prepare(string $fieldName) : array
    {
        $result = [];

        if (array_key_exists($fieldName, $this->filesRequest) === false) {
            return $result;
        }

        foreach ($this->filesRequest[$fieldName] as $prop => $values) {
            if (is_array($values)) {
                foreach ($values as $key => $value) {
                    $result[$key][$prop] = $value;
                }
            } else {
                $result[0][$prop] = $values;
            }
        }

        return $result;
    }
}
