<?php
/**
 * Copyright 2005-2019 Centreon
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
        $depthVersion1 = substr_count($version1, '.');
        $depthVersion2 = substr_count($version2, '.');
        if ($depthVersion1 > $depthVersion2) {
            $version2 = self::regularizeDepthVersion($version2, $depthVersion1);
        }
        if ($depthVersion2 > $depthVersion1) {
            $version1 = self::regularizeDepthVersion($version1, $depthVersion2);
        }
        return version_compare($version1, $version2, $operator);
    }

    /**
     * Updates the depth of the version number.
     *
     * @param string $version Version number to update
     * @param int $depth Depth destination
     * @return string Returns the updated version number with the destination depth
     */
    public static function regularizeDepthVersion(string $version, int $depth = 2): string
    {
        $actualDepth = substr_count($version, '.');
        if ($actualDepth == $depth) {
            return $version;
        } elseif ($actualDepth > $depth) {
            $parts = array_slice(explode('.', $version), 0, ($depth + 1));
            return implode('.', $parts);
        }
        for ($loop = $actualDepth; $loop < $depth; $loop++) {
            $version .= '.0';
        }
        return $version;
    }
}
