<?php
/*
 * Copyright 2005-2015 Centreon
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

include_once(realpath(dirname(__FILE__) . "/../../config/centreon.config.php"));

class CentreonGMT
{
    protected $timezoneById;
    protected $timezones;
    protected $myGMT;
    public $use;
    /**
     *
     * @var array
     */
    protected $aListTimezone;

    /**
     *
     * @var type
     */
    protected $db;

    /**
     *
     * @var type
     */
    protected $dbc;

    /**
     *
     * @param array $myTimezone
     */
    protected $myTimezone;

    /**
     *
     * @param array $hostLocations
     */
    protected $hostLocations = array();

    /**
     * Default timezone setted in adminstration/options
     * @var string $sDefaultTimezone
     */
    protected $sDefaultTimezone;
    
    /**
     *
     * @param string $myOffset
     */
    protected $myOffset;

    public function __construct($DB)
    {
        $this->db = $DB;
        $this->dbc = new CentreonDB("centstorage");

        /*
         * Define Table of GMT line
         */
        $this->timezoneById = $this->getList();

        /*
         * Flag activ / inactiv
         */
        $this->use = 1;
    }

    /**
     *
     * @return string
     */
    public function used()
    {
        return $this->use;
    }

    /**
     *
     * @param string $value
     */
    public function setMyGMT($value)
    {
        $this->myGMT = $value;
    }

    /**
     *
     * @return array
     */
    public function getGMTList()
    {
        return $this->timezoneById;
    }

    /**
     *
     * @return string
     */
    public function getMyGMT()
    {
        return $this->myGMT;
    }

    
    /**
     * This method return timezone of user
     *
     * @return string
     */
    public function getMyTimezone()
    {
        if (is_null($this->myTimezone)) {
            if (isset($this->timezoneById[$this->myGMT])) {
                $this->myTimezone = $this->timezoneById[$this->myGMT];
            } else {
                $this->getCentreonTimezone();
                if (!empty($this->sDefaultTimezone) && !empty($this->timezoneById[$this->sDefaultTimezone])) {
                    $this->myTimezone = $this->timezoneById[$this->sDefaultTimezone];
                } else { //if we take the empty PHP
                    $this->myTimezone = date_default_timezone_get();
                }
            }
        }
        return $this->myTimezone;
    }
    
    /**
     *
     * @return string
     */
    public function getMyOffset()
    {
        if (is_null($this->myOffset)) {
            if (count($this->aListTimezone) == 0) {
                $this->getList();
            }
            $this->myOffset = $this->aListTimezone[$this->myGMT]['timezone_offset'];
        }
        return $this->myOffset;
    }
    
    /**
     *
     * @return string
     */
    public function getMyGMTForRRD()
    {
        $sOffset = '';
        if (count($this->timezoneById) == 0) {
            $this->getList();
        }

        if (isset($this->aListTimezone[$this->myGMT]['timezone_offset'])) {
            $sOffset = $this->aListTimezone[$this->myGMT]['timezone_offset'];
        }
        return $sOffset;
    }

    /**
     *
     * @param type $format
     * @param string $date
     * @param type $gmt
     * @return string
     */
    public function getDate($format, $date, $gmt = null)
    {
        $return = "";
        if (!$date) {
            $date = "N/A";
        }
        if ($date == "N/A") {
            return $date;
        }

        if (!isset($gmt)) {
            $gmt = $this->myGMT;
        }

        if (isset($date) && isset($gmt)) {
            $sDate = new DateTime();
            $sDate->setTimestamp($date);
            $sDate->setTimezone(new DateTimeZone($this->getActiveTimezone($gmt)));
            $return = $sDate->format($format);
        }
        
        return $return;
    }
    
    /**
     *
     * @param type $date
     * @param type $gmt
     * @param type $reverseOffset
     * @return string
     */
    public function getUTCDate($date, $gmt = null, $reverseOffset = 1)
    {
        $return = "";
        if (!isset($gmt)) {
            $gmt = $this->myGMT;
        }

        if (isset($date) && isset($gmt)) {
            if (!is_numeric($date)) {
                $sDate = new DateTime($date);
            } else {
                $sDate = new DateTime();
                $sDate->setTimestamp($date);
            }
            
            $sDate->setTimezone(new DateTimeZone($this->getActiveTimezone($gmt)));
            
            $iTimestamp = $sDate->getTimestamp();
            $sOffset = $sDate->getOffset();
            $return = $iTimestamp + ($sOffset * $reverseOffset);
        }

        return $return;
    }
    
    /**
     *
     * @param type $date
     * @param type $gmt
     * @return string
     */
    public function getUTCDateFromString($date, $gmt = null, $reverseOffset = 1)
    {
        $return = "";
        if (!isset($gmt)) {
            $gmt = $this->myGMT;
        }
        if (isset($date) && isset($gmt)) {
            if (!is_numeric($date)) {
                $sDate = new DateTime($date);
            } else {
                $sDate = new DateTime();
                $sDate->setTimestamp($date);
            }

            $localDate = new DateTime();
            $sDate->setTimezone(new DateTimeZone($this->getActiveTimezone($gmt)));
            $iTimestamp = $sDate->getTimestamp();
            $sOffset = $sDate->getOffset();
            $sLocalOffset = $localDate->getOffset();
            $return = $iTimestamp - (($sOffset - $sLocalOffset) * $reverseOffset);
        }
        
        return $return;
    }
    

    /**
     *
     * @param type $gmt
     * @return string
     */
    public function getDelaySecondsForRRD($gmt)
    {
        $str = "";
        if ($gmt) {
            if ($gmt > 0) {
                $str .= "+";
            }
        } else {
            return "";
        }
    }

    /**
     *
     * @global type $pearDB
     * @param type $sid
     * @param type $DB
     * @return int
     */
    public function getMyGMTFromSession($sid = null, $DB = null)
    {
        global $pearDB;
        
        if (!isset($sid)) {
            return 0;
        }
        if (!isset($pearDB) && isset($DB)) {
            $pearDB = $DB;
        }
        
        $DBRESULT = $pearDB->query("SELECT `contact_location` FROM `contact`, `session` " .
                "WHERE `session`.`user_id` = `contact`.`contact_id` " .
                "AND `session_id` = '" . CentreonDB::escape($sid) . "' LIMIT 1");
        if (PEAR::isError($DBRESULT)) {
            $this->myGMT = 0;
        }
        $info = $DBRESULT->fetchRow();
        $DBRESULT->free();
        $this->myGMT = $info["contact_location"];
    }
    
    /**
     *
     * @global type $pearDB
     * @param int $userId
     * @param type $DB
     */
    public function getMyGTMFromUser($userId, $DB = null)
    {
        global $pearDB;
        
        if (!empty($userId)) {
        
            if (!isset($pearDB) && isset($DB)) {
                $pearDB = $DB;
            }

            $DBRESULT = $pearDB->query("SELECT `contact_location` FROM `contact` " .
                    "WHERE `contact`.`contact_id` = " . $userId ." LIMIT 1");
            if (PEAR::isError($DBRESULT)) {
                $this->myGMT = 0;
            }
            $info = $DBRESULT->fetchRow();
            $DBRESULT->free();
            $this->myGMT = $info["contact_location"];
        } else {
            $this->myGMT = 0;
        }
    }

    /**
     *
     * @param type $host_id
     * @param type $date_format
     * @return \DateTime
     */
    public function getHostCurrentDatetime($host_id, $date_format = 'c')
    {
        $locations = $this->getHostLocations();
        $sDate = new DateTime();
        $sDate->setTimezone(new DateTimeZone($this->getActiveTimezone($locations[$host_id])));
        return $sDate;
    }
    
    /**
     *
     * @param type $date
     * @param type $hostId
     * @param type $dateFormat
     * @param type $reverseOffset
     * @return string
     */
    public function getUTCDateBasedOnHostGMT($date, $hostId, $dateFormat = 'c', $reverseOffset = 1)
    {
        $locations = $this->getHostLocations();

        if (isset($locations[$hostId]) && $locations[$hostId] != '0') {
            $date = $this->getUTCDate($date, $locations[$hostId], $reverseOffset);
        }

        return date($dateFormat, $date);
    }

    /**
     *
     * @param type $date
     * @param type $hostId
     * @param type $dateFormat
     * @return string
     */
    public function getUTCTimestampBasedOnHostGMT($date, $hostId, $dateFormat = 'c')
    {
        $locations = $this->getHostLocations();

        if (isset($locations[$hostId]) && $locations[$hostId] != '0') {
            $date = $this->getUTCDate($date, $locations[$hostId]);
        }
 
        return $date;
    }
    
    /**
     *
     * @param type $hostId
     * @return array
     */
    public function getUTCLocationHost($hostId)
    {
        $locations = $this->getHostLocations();

        if (isset($locations[$hostId])) {
            return $locations[$hostId];
        }

        return null;
    }
    
    /**
     * Get the list of timezone
     *
     * @return array
     */
    public function getList()
    {
        $aDatas = array();
        
        $queryList = "SELECT timezone_id, timezone_name, timezone_offset FROM timezone ORDER BY timezone_name asc";
        $res = $this->db->query($queryList);
        if (PEAR::isError($res)) {
            return array();
        }
 
        $aDatas[null] = null;
        while ($row = $res->fetchRow()) {
            $this->timezones[$row['timezone_name']] =  $row['timezone_id'];
            $aDatas[$row['timezone_id']] = $row['timezone_name'];
            $this->aListTimezone[$row['timezone_id']] = $row;
        }
         
        return $aDatas;
    }
    
    /**
     *
     * @param type $values
     * @return array
     */
    public function getObjectForSelect2($values = array(), $options = array())
    {
        $items = array();
        
        $explodedValues = implode(',', $values);
        if (empty($explodedValues)) {
            $explodedValues = "''";
        }

        # get list of selected timezones
        $query = "SELECT timezone_id, timezone_name "
            . "FROM timezone "
            . "WHERE timezone_id IN (" . $explodedValues . ") "
            . "ORDER BY timezone_name ";
        
        $resRetrieval = $this->db->query($query);
        while ($row = $resRetrieval->fetchRow()) {
            $items[] = array(
                'id' => $row['timezone_id'],
                'text' => $row['timezone_name']
            );
        }

        return $items;
    }
    
    /**
     * Get list of timezone of host
     * @return array
     */
    public function getHostLocations()
    {
        if (count($this->hostLocations)) {
            return $this->hostLocations;
        }

        $this->hostLocations = array();

        $query = 'SELECT host_id, timezone FROM hosts WHERE enabled = 1 ';
        $res  = $this->dbc->query($query);
        if (!PEAR::isError($res)) {
            while ($row = $res->fetchRow()) {
                $this->hostLocations[$row['host_id']] = str_replace(':', '', $row['timezone']);
            }
        }
        return $this->hostLocations;
    }
    
    /**
     * Get default timezone setted in admintration/options
     *
     * @return string
     */
    public function getCentreonTimezone()
    {
        if (is_null($this->sDefaultTimezone)) {
            $sTimezone = '';

            $query = "SELECT `value` FROM `options` WHERE `key` = 'gmt' LIMIT 1";
            $res  = $this->db->query($query);
            if (!PEAR::isError($res)) {
                $row = $res->fetchRow();
                $sTimezone = $row["value"];
            }
            $this->sDefaultTimezone = $sTimezone;
        }
        return $this->sDefaultTimezone;
    }
    
    /**
     * This method verifies the timezone which is to be used in the other appellants methods.
     * In priority, it uses timezone of the object, else timezone of centreon, then lattest timezone PHP
     *
     * @param string $gmt
     * @return string timezone
     */
    private function getActiveTimezone($gmt)
    {
        $sTimezone = "";
        if (count($this->timezones) == 0) {
            $this->getList();
        }
        
        if (isset($this->timezones[$gmt])) {
            $sTimezone = $gmt;
        } else if (isset($this->timezoneById[$gmt])) {
            $sTimezone = $this->timezoneById[$gmt];
        } else {
            $this->getCentreonTimezone();
            if (!empty($this->sDefaultTimezone) && !empty($this->timezones[$this->sDefaultTimezone])) {
                $sTimezone = $this->timezones[$this->sDefaultTimezone];
            } else { //if we take the empty PHP
                $sTimezone = date_default_timezone_get();
            }
        }
        return $sTimezone;
    }
}
