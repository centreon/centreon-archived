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
     * @var string Json file name that will be generated.
     */
    private $jsonFilename;
    
    /**
     * @var string Root translation path where the translation files need
     * to be placed.
     */
    private $rootTranslationPath;
    
    /**
     * @var string Translation file that contains translation for one language
     */
    private $translationFile;
    
    /**
     * @var int Token length used to parse lines of translation files
     */
    private $tokenLength = 6;
    
    /**
     * @var string Language of the user
     */
    private $userLanguage;
    
    /**
     * @var string Label of the default translation language to generate
     */
    private $defaultLanguage = 'en';
    
    /**
     * Return a table containing all the translations.
     *
     * @return array
     * @throws \Exception
     */
    public function getTranslation(): array
    {
        if (empty($this->filesGenerationPath)) {
            throw new \Exception('Json destination directory not defined');
        }
        $langsArray = [];
        if (is_dir($this->filesGenerationPath)) {
            $jsonFile = "{$this->filesGenerationPath}/{$this->jsonFilename}";
            if (file_exists($jsonFile)) {
                return unserialize(file_get_contents($jsonFile));
            } else {
                $langs = $this->getAvailableLanguages();

                /**
                 * If the language of user is defined, we only keep this one
                 */
                if (!empty($this->userLanguage)) {
                    $userLanguage[] = $this->userLanguage;
                    
                    /**
                     * We only process if the language of user is available in
                     * the translation files
                     */
                    $languageToProcess = array_intersect($userLanguage, $langs);
                    if (!empty($languageToProcess)) {
                        $filename = $this->rootTranslationPath
                            . '/' . $languageToProcess[0]
                            . '/' . $this->translationFile;
                        $langsArray[$languageToProcess[0]] =
                            $this->transformTranslationFileIntoArray($filename);
                    }
                }
                
                /**
                 * We use the first translation to create the default translation.
                 * The default translation is in English and the process
                 * consists in defining the values with the values of the
                 * respective keys.
                 */
                $defaultTranslationFilename =
                    $this->rootTranslationPath
                    . '/' . $langs[0]
                    . '/' . $this->translationFile;
                $defaultTranslation = $this->transformTranslationFileIntoArray(
                    $defaultTranslationFilename,
                    true
                );
                $langsArray[$this->defaultLanguage] = $defaultTranslation;
                file_put_contents($jsonFile, serialize($langsArray));
            }
        } else {
            throw new \Exception('Files generation path doesn\'t exist');
        }
        return $langsArray;
    }
    
    /**
     * Retrieve Transform a translation definition file into an array
     *
     * @param string $filename File name to process
     * @param bool $isDefaultTranslation
     * @return array
     */
    private function transformTranslationFileIntoArray(
        string $filename,
        bool $isDefaultTranslation = false
    ): array {
        $isDefaultTranslationEmpty = empty($defaultTranslation);
        $translations = [];
        if ($fleHandler = fopen($filename, 'r')) {
            while (false !== ($line = fgets($fleHandler))) {
                $line = trim($line);
                
                // Retrieves the token
                $token = $this->getToken($line);
                // Retrieves text after the token
                $text = $this->getTokenTranslation($line);
                
                switch ($token) {
                    case 'msgid': // Token that contains the translation label
                        $label = $text;
                        break;
                    case 'msgstr': // Token that contains the translation
                        if (!empty($label)) {
                            if ($isDefaultTranslation) {
                                $translations[$label] = $label;
                            } else {
                                $translations[$label] = $text;
                            }
                            $label = null;
                        }
                }
            }
            fclose($fleHandler);
        }
        return $translations;
    }
    
    /**
     * Retrieve an array that contains a list of available language.
     * The process will try to find if the translation file that is
     * specified exist from the root translation path.
     * Each translation file need to be placed in a subdirectory whose name will
     * be used to segment all translations. In our case the subdirectory name
     * will be named as 'us', 'fr' or any country initials.
     *
     * @throws \Exception
     * @return array
     */
    private function getAvailableLanguages(): array
    {
        $directories = [];
        if ($dirHandler = opendir($this->rootTranslationPath)) {
            while (($file = readdir($dirHandler)) !== false) {
                $filePath =
                    $this->rootTranslationPath
                    . '/' . $file . '/' . $this->translationFile;
                if (file_exists($filePath)) {
                    $directories[] = $file;
                }
            }
            fclose($dirHandler);
        } else {
            throw new \Exception('Error opening the root translation path');
        }
        return $directories;
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
     * @param bool $createDirectory Indicates if the files generation path
     * should be created if it does not exist (true by default).
     */
    public function setFilesGenerationPath(
        string $filesGenerationPath,
        bool $createDirectory = true
    ): void {
        if ($createDirectory && !is_dir($filesGenerationPath)) {
            mkdir($filesGenerationPath);
        }
        if (mb_substr($filesGenerationPath, -1) === DIRECTORY_SEPARATOR) {
            substr($filesGenerationPath, -1);
        }
        $this->filesGenerationPath = $filesGenerationPath;
    }

    /**
     * Retrieves the json file name that will be generated.
     *
     * @return string
     */
    public function getJsonFilename(): ?string
    {
        return $this->jsonFilename;
    }

    /**
     * Defines the json file name that will be generated.
     *
     * @param string $jsonFilename
     */
    public function setJsonFilename(string $jsonFilename): void
    {
        $this->jsonFilename = $jsonFilename;
    }

    /**
     * Retrieves the root translation path where the translation files need
     * to be placed.
     *
     * @return string
     */
    public function getRootTranslationPath(): ?string
    {
        return $this->rootTranslationPath;
    }

    /**
     * Defines the root translation path where the translation files need
     * to be placed.
     *
     * @param string $rootTranslationPath
     */
    public function setRootTranslationPath(string $rootTranslationPath): void
    {
        if (mb_substr($rootTranslationPath, -1) === DIRECTORY_SEPARATOR) {
            substr($rootTranslationPath, -1);
        }
        $this->rootTranslationPath = $rootTranslationPath;
    }
    
    /**
     * Retrieves the translation file name.
     *
     * @return string
     */
    public function getTranslationFile(): ?string
    {
        return $this->translationFile;
    }

    /**
     * Defines the translation file name.
     *
     * @param string $translationFile
     */
    public function setTranslationFile($translationFile): void
    {
        $this->translationFile = $translationFile;
    }
    
    /**
     * Retrieves the token of the line.
     *
     * @param string $line Line to analyse
     * @return string Token found
     */
    private function getToken($line): string
    {
        return trim(
            substr($line, 0, $this->tokenLength)
        );
    }
    
    /**
     * Retrieves the translation for the line given
     *
     * @param string $line Line to analyse
     * @return string Translation
     */
    private function getTokenTranslation($line): string
    {
        $text = trim(
            substr(
                $line,
                $this->tokenLength,
                strlen($line) - $this->tokenLength
            )
        );
        // Removes double-quotes character that surround the text
        return substr($text, 1, strlen($text) - 2);
    }
    
    /**
     * Retrieve the language of the user
     *
     * @return string Language of the user
     */
    public function getUserLanguage(): ?string
    {
        return $this->userLanguage;
    }
    
    /**
     * Define the language of the user
     *
     * @param string $userLanguage Language of the user
     */
    public function setUserLanguage(string $userLanguage): void
    {
        $this->userLanguage = $userLanguage;
    }
}
