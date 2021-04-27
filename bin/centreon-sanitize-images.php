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
 */

require(__DIR__ . '/../vendor/autoload.php');
use enshrined\svgSanitize\Sanitizer;
use Symfony\Component\Finder\Finder;


###############################
#        COMMON FUNCTION      #
###############################

/**
 * Get the result of asked question.
 *
 * @param string $question
 * @param boolean $hidden
 * @return string
 */
$askQuestion = function (string $question, bool $hidden = false): string {
    if ($hidden) {
        system("stty -echo");
    }
    printf("%s", $question);
    $handle = fopen("php://stdin", "r");
    $response = '';
    if ($handle) {
        $fGets = fgets($handle);
        if ($fGets) {
            $response = trim($fGets);
        }
        fclose($handle);
    }
    if ($hidden) {
        system("stty echo");
    }
    printf("\n");
    return $response;
};

/**
 * Get the images where MIME Type is incorrect or image extension don't match the MIME Type.
 *
 * @param array<string> $files
 * @return array<string>
 * @throws \Exception
 */
$getInvalidImages = function (array $files): array {
    $mimeTypeFileExtensionConcordance = [
        "svg" => "image/svg+xml",
        "jpg" => "image/jpeg",
        "jpeg" => "image/jpeg",
        "gif" => "image/gif",
        "png" => "image/png"
    ];
    $invalidImages = [];
    foreach ($files as $file) {
        $fileExploded = explode(".", $file);
        $fileExtension = end($fileExploded);
        if (!array_key_exists($fileExtension, $mimeTypeFileExtensionConcordance)) {
            throw new \Exception(sprintf('Invalid image extension: %s', $fileExtension));
        }
        $mimeType = mime_content_type($file);
        if ($mimeType) {
            /**
             * If MIME type is invalid or extension doesn't match MIME type
             */
            if (
                !preg_match('/(^image\/(jpg|jpeg|svg\+xml|gif|png)$)/', $mimeType)
                || (
                    preg_match('/^image\//', $mimeType)
                    && $mimeType !== $mimeTypeFileExtensionConcordance[$fileExtension]
                )
            ) {
                $invalidImages[] = $file;
            }
        }
    }

    return $invalidImages;
};

/**
 * Get all the svg images.
 *
 * @param array<string> $files
 * @return array<string>
 */
$getSvgImages = function (array $files): array {
    $svgFiles = [];
    foreach ($files as $file) {
        $fileExploded = explode(".", $file);
        $fileExtension = end($fileExploded);
        $mimeType = mime_content_type($file);
        if (($mimeType && preg_match('/(^image\/svg\+xml$)/', $mimeType)) && $fileExtension === "svg") {
            $svgFiles[] = $file;
        }
    }
    return $svgFiles;
};

/**
 * Sanitize a SVG file.
 *
 * @param string $file
 * @throws \Exception
 */
$sanitizeSvg = function (string $file): void {
    $sanitizer = new Sanitizer();
    $svg = file_get_contents($file);
    if ($svg === false) {
        throw new \Exception('Unable to get content of file: ' . $file);
    }
    $cleanSvg = $sanitizer->sanitize($svg);
    if (file_put_contents($file, $cleanSvg) === false) {
        throw new \Exception('Unable to replace content of file: ' . $file);
    };
};

/**
 * List all the invalid and/or svg images
 *
 * @return array<string,array>
 */
$listImages = function () use ($getInvalidImages, $getSvgImages): array {
    $finder = new Finder();
    $images = $finder->in(__DIR__ . '/../www/img/media')->name(['*.jpg', '*.jpeg', '*.svg', '*.gif', '*.png']);
    $imagesPath = [];
    foreach ($images as $image) {
        $imagesPath[] = $image->getPathName();
    }
    $invalidImages = $getInvalidImages($imagesPath);
    $svgImages = $getSvgImages($imagesPath);

    $images = [
        "invalidImages" => [],
        "svgImages" => []
    ];


    foreach ($invalidImages as $invalidImage) {
        $images['invalidImages'][] = $invalidImage;
    }

    foreach ($svgImages as $svgImage) {
        $images['svgImages'][] = $svgImage;
    }

    return $images;
};

/**
 * Convert a corrupted image into a red cross on white background.
 *
 * @param string $invalidImg
 * @throws \Exception
 */
$convertCorruptedImageOrFail = function (string $invalidImg): void {
    // Get image extension
    $invalidImgPathExploded = explode('.', $invalidImg);
    $invalidImgExtension = end($invalidImgPathExploded);

    //Check that extension is handled.
    if (in_array($invalidImgExtension, ['jpg', 'jpeg', 'svg', 'gif', 'png']) === false) {
        throw new \Exception('Invalid format: ' . $invalidImgExtension);
    }

    // Get size
    $invalidImgSize = getimagesize($invalidImg);
    if ($invalidImgSize === false) {
        $width = 100;
        $height = 100;
    } else {
        $width = $invalidImgSize[0];
        $height = $invalidImgSize[1];
    }

    // Create the image
    $newImg = imagecreate($width, $height);
    if ($newImg === false) {
        throw new Exception('Unable to create a generic image for file: ' . $invalidImg);
    }
    imagecolorallocate($newImg, 255, 255, 255);

    $lineColor = imagecolorallocate($newImg, 255, 0, 0);
    if ($lineColor !== false) {
        imageline($newImg, $width, 0, 0, $height, $lineColor);
        imageline($newImg, 0, 0, $width, $height, $lineColor);
    }

    // Save image as correct MIME Type
    switch ($invalidImgExtension) {
        case "jpeg":
        case "jpg":
            if (@unlink($invalidImg) === false) {
                throw new \Exception(
                    sprintf("Unable to delete %s before replacing it by a generic image.", $invalidImg)
                );
            }
            imagejpeg($newImg, $invalidImg);
            break;
        case "gif":
            if (@unlink($invalidImg) === false) {
                throw new \Exception(
                    sprintf("Unable to delete %s before replacing it by a generic image.", $invalidImg)
                );
            }
            imagegif($newImg, $invalidImg);
            break;
        //svg will be recreated as PNG as we don't have possibility to recreate a svg.
        case "png":
        case "svg":
            if (@unlink($invalidImg) === false) {
                throw new \Exception(
                    sprintf("Unable to delete %s before replacing it by a generic image.", $invalidImg)
                );
            }
            imagepng($newImg, $invalidImg);
            break;
        default:
            break;
    }
};


// Get Script options
$options = getopt('h::', ['help::']);
if (($options && array_key_exists('help', $options)) || ($options && array_key_exists('h', $options))) {
    echo "This script will sanitize all your svg files and replace your corrupted images by a generic image. \n";
    exit(0);
}

$files = $listImages();

if (empty($files['svgImages']) && empty($files['invalidImages'])) {
    echo PHP_EOL . "Nothing to do, everything is fine." . PHP_EOL;
    exit(0);
}

if (!empty($files['svgImages'])) {
    echo "The following SVGs can be sanitized to prevent any injections:"  . PHP_EOL;
    foreach ($files['svgImages'] as $svgImage) {
        $pattern = str_replace('/', '\/', __DIR__ . '/../www/img/media/');
        echo preg_replace('/' . $pattern . '/', '', $svgImage) . PHP_EOL;
    }
    echo PHP_EOL;
}

if (!empty($files['invalidImages'])) {
    echo "The following images have an invalid MIME type or a mismatch between MIME type and " .
    "file extension and can be replaced by a generic image:" . PHP_EOL;
    foreach ($files['invalidImages'] as $invalidImg) {
        $pattern = str_replace('/', '\/', __DIR__ . '/../www/img/media/');
        echo preg_replace('/' . $pattern . '/', '', $invalidImg) . PHP_EOL;
    }
    echo PHP_EOL;
}

$proceed = $askQuestion('Would you like to proceed to sanitize ? [y/N]: ');
if (strtolower($proceed) === 'y') {
    foreach ($files['svgImages'] as $svgImage) {
        try {
            $sanitizeSvg($svgImage);
        } catch (\Exception $ex) {
            echo "ERROR - " . $ex->getMessage();
            exit(1);
        }
    }

    foreach ($files['invalidImages'] as $invalidImg) {
        try {
            $convertCorruptedImageOrFail($invalidImg);
        } catch (\Exception $ex) {
            echo "ERROR - " . $ex->getMessage();
            exit(1);
        }
    }

    echo "Sanitize done." . PHP_EOL;
} else {
    exit(0);
}
