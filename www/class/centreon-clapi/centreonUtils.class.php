<?php
/*
 * Copyright 2005-2015 CENTREON
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
 * As a special exception, the copyright holders of this program give CENTREON
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of CENTREON choice, provided that
 * CENTREON also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : command@centreon.com
 *
 */

namespace CentreonClapi;

class CentreonUtils
{
    /**
     * @var string
     */
    private static $centreonPath;

    /**
     * @var string
     */
    private static $clapiUserName;


    /**
     * Get centreon application path
     *
     * @return string
     */
    public static function getCentreonPath()
    {
        if (isset(self::$centreonPath)) {
            return self::$centreonPath;
        }
        $db = new \CentreonDB('centreon');
        $res = $db->query("SELECT `value` FROM options WHERE `key` = 'oreon_path'");
        $row = $res->fetchRow();
        self::$centreonPath = $row['value'];
        return self::$centreonPath = $row['value'];
    }

    /**
     * Get centreon directory
     *
     * @return string
     */
    public static function getCentreonDir()
    {
        $db = new \CentreonDB('centreon');
        $res = $db->query("SELECT `value` FROM options WHERE `key` = 'oreon_path' LIMIT 1");
        $row = $res->fetchRow();
        if (isset($row['value'])) {
            return $row['value'];
        }
        return "";
    }

    /**
     * Converts strings such as #S# #BS# #BR#
     *
     * @param string $pattern
     * @return string
     */
    public function convertSpecialPattern($pattern)
    {
        $pattern = str_replace("#S#", "/", $pattern);
        $pattern = str_replace("#BS#", "\\", $pattern);
        $pattern = str_replace("#BR#", "\n", $pattern);
        return $pattern;
    }

    /**
     * Get Image Id
     *
     * @param string $imagename
     * @param int|null
     */
    public function getImageId($imagename)
    {
        $tab = preg_split("/\//", $imagename);
        isset($tab[0]) ? $dirname = $tab[0] : $dirname = null;
        isset($tab[1]) ? $imagename = $tab[1] : $imagename = null;

        if (!isset($imagename) || !isset($dirname)) {
            return null;
        }

        $query = "SELECT img.img_id ".
                        "FROM view_img_dir dir, view_img_dir_relation rel, view_img img ".
                        "WHERE dir.dir_id = rel.dir_dir_parent_id " .
                        "AND rel.img_img_id = img.img_id ".
                        "AND img.img_path = '".$imagename."' ".
                        "AND dir.dir_name = '".$dirname."' " .
                        "LIMIT 1";
        $db = new \CentreonDB('centreon');
        $res = $db->query($query);
        $img_id = null;
        $row = $res->fetchRow();
        if (isset($row['img_id']) && $row['img_id']) {
            $img_id = $row['img_id'];
        }
        return $img_id;
    }

    /**
     * Convert Line Breaks \n or \r\n to <br/>
     *
     * @param string $str | string to convert
     * @return string
     */
    public static function convertLineBreak($str)
    {
        $str = str_replace("\r\n", "<br/>", $str);
        $str = str_replace("\n", "<br/>", $str);
        return $str;
    }

    public static function setUserName($userName)
    {
        self::$clapiUserName = $userName;
    }

    public static function getUserName()
    {
        return self::$clapiUserName;
    }
}
