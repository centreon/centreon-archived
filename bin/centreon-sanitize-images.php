<?php
/**
 * Copyright 2005-2021 Centreon
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
 * SVN : $URL$
 * SVN : $Id$
 *
 */
require(__DIR__ . '/../vendor/autoload.php');
use enshrined\svgSanitize\Sanitizer;

// Get Script options
$options = getopt('', ['list', 'sanitize']);
if (empty($options)) {
    echo "Missing parameters --list or --sanitize";
    exit(1);
}

// --list option
if (isset($options['list'])) {
    $files = listImages();
    if (empty($files['svgImages']) && empty($files['invalidFiles'])) {
        echo PHP_EOL . "Nothing to do, everything is fine." . PHP_EOL;
    } else {
        echo PHP_EOL . "You can execute the script with the --sanitize options to apply the modifications." . PHP_EOL;
    }
    exit(0);
}

// --sanitize option
if (isset($options['sanitize'])) {
    $files = listImages();
    if (!empty($files['svgImages'])) {
        foreach($files['svgImages'] as $svgImage) {
            sanitizeSvg($svgImage);
        }
    }
    if (!empty($files['invalidFiles'])) {
        foreach ($files['invalidFiles'] as $invalidImg) {
            // Get image name and extension
            $invalidImgPathExploded = explode('/', $invalidImg);
            $invalidImgName = end($invalidImgPathExploded);

            // Get only extension
            $invalidImgNameExploded = explode('.', $invalidImgName);
            $invalidImgExtension = end($invalidImgNameExploded);

            // Get size
            $invalidImgSize =getimagesize($invalidImg);
            if($invalidImgSize === false) {
                $width = 100;
                $height = 100;
            } else {
                $width = $invalidImgSize[0];
                $height = $invalidImgSize[1];
            }
            $newImg = imagecreatetruecolor($width, $height);
            imagecolorallocate($newImg, 255, 255, 255);
            $lineColor = imagecolorallocate($newImg, 255, 0, 0);
            imageline($newImg, 0, 0, $width, $height, $lineColor);
            imageline($newImg, $width, 0, $height, 0, $lineColor);
            switch(true) {
                case $invalidImgExtension === "jpeg":
                case $invalidImgExtension === "jpg":
                    unlink($invalidImg);
                    imagejpeg($newImg, $invalidImg);
                    break;
                default:
                    break;
            }
        }
    }
}

###############################
#        COMMON FUNCTION      #
###############################

/**
 * Scan directory recursively.
 *
 * @param string $root
 * @param array $files
 * @return array
 */
function scanDirRecursively(string $root, array &$files): array
{
    // starts the scan
    $dirs = scandir($root);
    foreach ($dirs as $dir) {
        if (in_array($dir,['.','..','.keep','.htaccess'])) {
            continue;
        }
        $path = $root . '/' . $dir;
        if (is_file($path)) {
            $fileExploded = explode(".", $dir);
            if (end($fileExploded) === "html") {
                continue;
            }
            $files[] = $path;
        }
        if (is_dir($path)) {
            scanDirRecursively($path, $files);
        }
    }

    return $files;
}

/**
 * Check MIME Type of file
 *
 * @param array $file
 * @return boolean
 */
function getInvalidImages(array $files): array
{
    $mimeTypeFileExtensionConcordance = [
        "svg" => "image/svg+xml",
        "jpg" => "image/jpeg",
        "jpeg" => "image/jpeg",
        "gif" => "image/gif",
        "png" => "image/png"
    ];
    $invalidFiles = [];
    foreach ($files as $file) {
        $fileExploded = explode(".", $file);
        $fileExtension = end($fileExploded);
        $mimeType = mime_content_type($file);
        /**
         * If MIME type is invalid or extension doesn't match MIME type
         */
        if (
            !preg_match('/(^image\/(jpg|jpeg|svg\+xml|gif|png)$)/', $mimeType)
            || (preg_match('/^image\//', $mimeType) && $mimeType !== $mimeTypeFileExtensionConcordance[$fileExtension])
        ) {
            $invalidFiles[] = $file;
        }
    }

    return $invalidFiles;
}

/**
 * Get all the svg images.
 *
 * @param array $files
 * @return array
 */
function getSvgImages(array $files): array
{
    $svgFiles = [];
    foreach($files as $file) {
        $fileExploded = explode(".", $file);
        $fileExtension = end($fileExploded);
        $mimeType = mime_content_type($file);
        if (preg_match('/(^image\/svg\+xml$)/', $mimeType) && $fileExtension === "svg") {
            $svgFiles[] = $file;
        }
    }
    return $svgFiles;
}

/**
 * Sanitize a SVG file.
 *
 * @param string $file
 * @return void
 */
function sanitizeSvg(string $file): void
{
    try {
        $sanitizer = new Sanitizer();
        $svg = file_get_contents($file);
        $cleanSvg = $sanitizer->sanitize($svg);
        file_put_contents($file, $cleanSvg);
    } catch (\Exception $ex) {
        echo 'ERROR - ' . $ex->getMessage();
    }
}

/**
 * List all the invalid and/or svg images
 *
 * @return array
 */
function listImages(): array
{
    $imgFilesDir = [];
    $imgFilesDir = scanDirRecursively(__DIR__ . '/../www/img/media', $imgFilesDir);
    $invalidFiles = getInvalidImages($imgFilesDir);
    $svgImages = getSvgImages($imgFilesDir);

    $files = [
        "invalidFiles" => [],
        "svgImages" => []
    ];

    if(!empty($invalidFiles)) {
        echo PHP_EOL;
        echo "The following images have an invalid MIME type or a mismatch between MIME type and file extension and "
        . "will be replace by a generic image:" . PHP_EOL . PHP_EOL;
    }

    foreach($invalidFiles as $invalidFile) {
        $files['invalidFiles'] = [$invalidFile];
        $pattern = str_replace('/','\/', __DIR__ . '/../www/img/media/');
        echo preg_replace('/' . $pattern . '/', '', $invalidFile) . PHP_EOL;
    }

    if(!empty($svgImages)) {
        echo PHP_EOL;
        echo "The following SVG will be sanitized to prevent any injections:" . PHP_EOL . PHP_EOL;
    }

    foreach($svgImages as $svgImage) {
        $files['svgImages'] = [$svgImage];
        $pattern = str_replace('/','\/', __DIR__ . '/../www/img/media/');
        echo preg_replace('/' . $pattern . '/', '', $svgImage) . PHP_EOL;
    }

    return $files;
}