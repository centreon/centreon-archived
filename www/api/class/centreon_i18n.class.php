<?php
/**
 * Copyright 2005-2018 Centreon
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

/**
 * Webservice that allow to retrieve all translations in one json file.
 * If the file doesn't exist it will be created at the first reading.
 */
class CentreonI18n extends CentreonWebService
{
    /**
     * @var string Path where the translation file will be generate.
     */
    private $filesGenerationPath;


    /**
     * Return a table containing all the translations.
     *
     * @return array
     * @throws \Exception
     */
    public function getTranslation(): array
    {
        if (file_exists($this->filesGenerationPath)) {
            $data = unserialize(
                file_get_contents($this->filesGenerationPath)
            );
            return $data;
        } else {
            throw new \Exception("Translation files does not exists");
        }
    }
    
    /**
     * Retrieves the path where the translation file will be generate.
     *
     * @return string
     */
    public function getFilesGenerationPath(): ?string
    {
        return $this->filesGenerationPath;
    }

    /**
     * Defined the path where the translation file will be generate.
     *
     * @param string $filesGenerationPath
     */
    public function setFilesGenerationPath(string $filesGenerationPath): void
    {
        $this->filesGenerationPath = $filesGenerationPath;
    }
}
