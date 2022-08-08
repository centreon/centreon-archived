<?php
/*
 * Copyright 2005 - 2019 Centreon (https://www.centreon.com/)
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * For more information : contact@centreon.com
 *
 */
declare(strict_types=1);

namespace Centreon\Domain;

/**
 * This class can be used to compare and harmonized two version numbers
 *
 * @package Centreon\Domain
 */
class VersionHelper
{
    public const EQUAL = '==';
    public const LT = '<';
    public const GT = '>';
    public const LE = '<=';
    public const GE = ">=";

    /**
     * Compare two version numbers.
     * Before comparison the depth between version numbers will be harmonized.
     *
     * @uses VersionHelper::regularizeDepthVersion() to harmonize the version numbers
     * @uses version_compare after version numbers harmonization
     *
     * @param string $version1 First version to compare
     * @param string $version2 Second version to compare
     * @param string $operator Comparison operator (default: VersionHelper::EQUAL)
     * @return bool Returns the comparison result
     */
    public static function compare(string $version1, string $version2, string $operator = self::EQUAL): bool
    {
        $floatSeparationSymbol = '.';
        if ((substr_count($version1, ',') + substr_count($version2, ',')) > 0) {
            $floatSeparationSymbol = ',';
        }
        $depthVersion1 = substr_count($version1, $floatSeparationSymbol);
        $depthVersion2 = substr_count($version2, $floatSeparationSymbol);
        if ($depthVersion1 > $depthVersion2) {
            $version2 = self::regularizeDepthVersion($version2, $depthVersion1, $floatSeparationSymbol);
        }
        if ($depthVersion2 > $depthVersion1) {
            $version1 = self::regularizeDepthVersion($version1, $depthVersion2, $floatSeparationSymbol);
        }
        return version_compare($version1, $version2, $operator);
    }

    /**
     * Updates the depth of the version number.
     *
     * @param string $version Version number to update
     * @param int $depth Depth destination
     * @param string $glue
     * @return string Returns the updated version number with the destination depth
     */
    public static function regularizeDepthVersion(string $version, int $depth = 2, string $glue = '.'): string
    {
        $actualDepth = substr_count($version, $glue);
        if ($actualDepth == $depth) {
            return $version;
        } elseif ($actualDepth > $depth) {
            $parts = array_slice(explode($glue, $version), 0, ($depth + 1));
            return implode($glue, $parts);
        }
        for ($loop = $actualDepth; $loop < $depth; $loop++) {
            $version .= $glue . '0';
        }
        return $version;
    }
}
