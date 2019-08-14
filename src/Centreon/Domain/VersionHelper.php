<?php

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
