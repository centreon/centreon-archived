<?php

/**
 * Copyright 2005-2022 Centreon
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

if (strlen($argv[1]) !== 2) {
    exit(
        sprintf("The length of the language code must be 2\n")
    );
}

$languageCode = strtolower($argv[1]);

if ($argc === 3 || $argc === 4) {
    if (!file_exists($argv[2])) {
        exit(
            sprintf("Translation file '%s' does not exist\n", $argv[2])
        );
    }
} else {
    $currentFileInfos = pathinfo(__FILE__);
    $execFile = $currentFileInfos['filename'] . '.' . $currentFileInfos['extension'];
    printf("usage:  {$execFile} code_language translation_file.po translated_file.ser\n"
        . "  code_language          code of the language (ex: fr, es, de, ...)\n"
        . "  translation_file.po    file where the translation exists\n"
        . "  translated_file.ser    Serialized file where the translation will be converted\n");
}

define('TOKEN_LENGTH', 6);

if ($argc === 3) {
    $translationFileInfos = pathinfo($argv[1]);
    $destinationFile = $translationFileInfos['dirname'] . '/'
        . $translationFileInfos['filename'] . '.json';
    createTranslationFile($languageCode, $argv[2], $destinationFile);
}

if ($argc === 4) {
    if (file_exists($argv[3])) {
        if (false === unlink($argv[3])) {
            exit("Destination file already exists, impossible to delete it\n");
        }
    }
    $destinationFileInfos = pathinfo($argv[3]);
    $destinationDirectory = $destinationFileInfos['dirname'];
    if (!is_dir($destinationDirectory)) {
        if (false === mkdir($destinationDirectory, 0775, true)) {
            exit(
                sprintf("Impossible to create directory '%s'\n", $destinationDirectory)
            );
        }
    }
    createTranslationFile($languageCode, $argv[2], $argv[3]);
}

/**
 * Create translation file for React
 *
 * @param string $languageCode Code of the language (ex: fr, es, de, ...)
 * @param string $translationFile  File where the translation exists
 * @param string $destinationFile  Serialized file where the translation will be converted
 */
function createTranslationFile(
    string $languageCode,
    string $translationFile,
    string $destinationFile
): void {

    $translations = [];
    $englishTranslation = [];
    $isDefaultTranslation = $languageCode === 'en';

    if ($fleHandler = fopen($translationFile, 'r')) {
        while (false !== ($line = fgets($fleHandler))) {
            $line = trim($line);

            // Retrieves the token
            $token = trim(
                substr($line, 0, TOKEN_LENGTH)
            );
            // Retrieves text after the token
            $text = trim(
                substr(
                    $line,
                    TOKEN_LENGTH,
                    strlen($line) - TOKEN_LENGTH
                )
            );
            // Removes double-quotes character that surround the text
            $text = substr($text, 1, strlen($text) - 2);

            switch ($token) {
                case 'msgid': // Token that contains the translation label
                    $label = $text;
                    break;
                case 'msgstr': // Token that contains the translation
                    if (!empty($label)) {
                        $englishTranslation[$label] = $label;
                        if (!$isDefaultTranslation) {
                            // Only if the code of language is not 'en'
                            $translations[$label] = $text;
                        }
                        $label = null;
                    }
            }
        }

        fclose($fleHandler);
    }
    $final['en'] = $englishTranslation;
    if (!$isDefaultTranslation) {
        // Only if the code of language is not 'en'
        $final[$languageCode] = $translations;
    }
    if (0 === file_put_contents($destinationFile, serialize($final))) {
        exit(
            sprintf("Impossible to create destination file '%s'\n", $destinationFile)
        );
    }
    chmod($destinationFile, 0664);
}
