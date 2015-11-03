<?php

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
        $db = Centreon_Db_Manager::factory('centreon');
        $res = $db->query("SELECT `value` FROM options WHERE `key` = 'oreon_path'");
        $row = $res->fetch();
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
        $db = Centreon_Db_Manager::factory('centreon');
        $res = $db->query("SELECT `value` FROM options WHERE `key` = 'oreon_path' LIMIT 1");
        $row = $res->fetch();
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
        $db = Centreon_Db_Manager::factory('centreon');
        $res = $db->query($query);
        $img_id = null;
        $row = $res->fetch();
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
    public static function convertLineBreak($str) {
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
