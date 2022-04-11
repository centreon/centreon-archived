<?php

/*
 * Copyright 2005-2022 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
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

declare(strict_types=1);

abstract class JsonFormat
{
    /**
     * @var mixed
     */
    protected mixed $cacheData = null;

    /**
     * @var string|null
     */
    protected ?string $filePath = null;

    /**
     * @param mixed $data
     */
    public function setContent(mixed $data): void
    {
        $this->cacheData = $data;
    }

    /**
     * Defines the path of the file where the data should be written.
     *
     * @param string $filePath
     */
    public function setFilePath(string $filePath): void
    {
        $this->filePath = $filePath;
    }

    /**
     * Writes the content of the cache only if it is not empty.
     *
     * @return int Number of bytes written
     * @throws Exception
     */
    public function flushContent(): int
    {
        if (empty($this->filePath)) {
            throw new Exception('No file path defined');
        }
        if (! empty($this->cacheData)) {
            $data = json_encode($this->cacheData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            $this->cacheData = null;

            $writtenBytes = file_put_contents($this->filePath, $data);
            if ($writtenBytes === false) {
                $file = $this->retrieveLastDirectory() . DIRECTORY_SEPARATOR . pathinfo($this->filePath)['basename'];
                throw new Exception(
                    sprintf('Error while writing the \'%s\' file ', $file)
                );
            }
            return $writtenBytes;
        }
        return 0;
    }

    /**
     * Retrieve the last directory.
     * (ex: /var/log/centreon/file.log => centreon)
     *
     * @return string
     */
    private function retrieveLastDirectory(): string
    {
        if ($this->filePath === null) {
            return '';
        }
        $directories = explode('/', pathinfo($this->filePath)['dirname']);
        return array_pop($directories);
    }
}
