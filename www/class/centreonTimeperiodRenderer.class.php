<?php
/*
 * Copyright 2005-2011 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */


/**
 * Original author is Tensibai, cleaned up by sho.
 * Used for rendering time periods in order to make them more human-readable.
 *
 * @author Tensibai
 * @author Sylvestre Ho
 */
class CentreonTimeperiodRenderer
{
    protected $db;
    protected $tpid;
    protected $tpname;
    protected $tpalias;
    protected $timerange;
    protected $timeline;
    protected $includedTp;
    protected $excludedTp;
    protected $exceptionList;

    /**
     * Constructor
     *
     * @param CentreonDB $db
     * @param int $tpid
     * @param strign $inex
     * @return void
     */
    public function __construct($db, $tpid, $inex) {
        $dayTab = array("tp_sunday" => array(), "tp_monday" => array(), "tp_tuesday" => array(), "tp_wednesday" => array(), "tp_thursday" => array(), "tp_friday" => array(), "tp_saturday" => array());
        $this->timerange = $dayTab;
        $this->timeline = $dayTab;
        $this->includedTp = array();
        $this->excludedTp = array();
        $this->exceptionList = array();
        $this->db = $db;
        $this->tpid = $tpid;
        $query = "SELECT tp_name, tp_alias, tp_monday, tp_tuesday, tp_wednesday, tp_thursday, tp_friday, tp_saturday, tp_sunday
        		  FROM timeperiod
        		  WHERE tp_id = '" . $tpid . "'";
        $res = $this->db->query($query);
        if (!$res->numRows()) {
            throw new Exception("Timeperiod not found");
        }
        $row = $res->fetchRow();
        $this->tpname = $row["tp_name"];
        $this->tpalias = $row["tp_alias"];
        foreach ($this->timerange as $key => $val) {
            if ($row[$key]) {
                $tmparr = explode(",",$row[$key]);
                foreach ($tmparr as $tr) {
                    $tmptr = $this->getTimeRange($this->tpid, $this->tpname, $inex, $tr);
                    $this->timerange[$key][] = $tmptr;
                }
            }
        }
        $this->updateInclusions();
        $this->updateExclusions();
        $this->orderTimeRanges();
        $this->db = null;
    }

    /**
     * Set time bars
     *
     * @return void
     */
    public function timeBars() {
        $coef = 4;
        foreach ($this->timerange as $day => $ranges) {
            $timeindex=0;
            $timeline[0]=array("start"    => 0,
							   "style"    => "unset",
							   "end"      => 0,
							   "size"     => 0,
							   "Text"     => "init",
							   "From"     => "",
							   "Textual"  => "");
            if (isset($ranges[0])) {
                $last["in"] = "";
                $last["nb"] = 0;
                for ($i = 0; $i <= 1440; $i++) {
                    $actual["in"] = "";
                    $actual["nb"] = 0;
                    foreach ($ranges as $k => $r) {
                        if ($r['tstart'] <= $i && $i <= $r['tend']) {
                            $actual["in"] .= $actual["in"] != "" ? ",".$k : $k;
                            $actual["nb"]++;
                        }
                    }
                    if ($actual["in"] != $last["in"] || $i == 1440) {
                        if ($i == 0) {
                            $last = $actual;
                        }
                        $timeline[$timeindex]["end"] = $i;
                        $timeline[$timeindex]["size"] = round(($i - $timeline[$timeindex]["start"])/$coef);
                        switch ($last["nb"] ) {
                            case 0:
                                $ts = $timeline[$timeindex]["start"];
                                $timeline[$timeindex]["style"] = "unset";
                                $timeline[$timeindex]["Textual"] = sprintf("%02d",intval($ts/60)).":".sprintf("%02d",$ts%60)."-".sprintf("%02d",intval($i/60)).":".sprintf("%02d",$i%60);
                                $timeline[$timeindex]["From"] = "No timeperiod covering ".$timeline[$timeindex]["Textual"];
                                break;
                            default:
                                $idlist = explode(",",$last["in"]);
                                foreach ($idlist as $v) {
                                    if ($ranges[$v]['inex'] == 0) {
                                        $timeline[$timeindex]["style"] = "excluded";
                                    }
                                    $txt = $ranges[$v]['textual'];
                                    $inex = $ranges[$v]['inex'] ? "include" : "exclude";
                                    $from = $ranges[$v]['fromTpName']." ".$inex." ".$txt;
                                    $timeline[$timeindex]["From"] .= $timeline[$timeindex]["From"] != "" ? ",".$from : $from;
                                    $timeline[$timeindex]["Textual"] .= $timeline[$timeindex]["Textual"] != "" ? ",".$txt : $txt;
                                }
                        }
                        switch ($last["nb"] ) {
                            case 0:
                                break;
                            case 1:
                                $timeline[$timeindex]["style"] = ($ranges[$last["in"]]['inex'] == 1) ? "included" : "excluded";
                                break;
                            default:
                                $timeline[$timeindex]["style"] = "warning";
                                $timeline[$timeindex]["From"] = "OVERLAPS: ".$timeline[$timeindex]["From"];
                                break;
                        }
                        if ($i < 1440) {
                            $timeline[++$timeindex] = array("start"    => $i,
														    "style"    => "unset",
														    "end"      => 0,
				                                            "size"     => 0,
                                				            "Text"     => "New in loop",
				                                            "From"     => "",
                                				            "Textual"  => "");
                        }
                    }
                    $last = $actual;
                }
            }
            $endtime = $timeline[$timeindex]["end"];
            if ($endtime < 1440) {
                $textual = sprintf("%02d", intval($endtime/60)).":".sprintf("%02d", $endtime%60)."-24:00";
                $timeline[$timeindex] = array("start"    => $endtime,
										      "style"    => "unset",
											  "end"      => 1440,
											  "size"     => (1440 - $endtime) / $coef,
											  "Text"     => "No Timeperiod covering ".$textual,
	                                          "From"     => "No Timeperiod covering ".$textual,
        	                                  "Textual"  => $textual);
            }
            $this->timeline[$day] = $timeline;
            unset($timeline);
        }
    }

    /**
     * Compare
     *
     * @param array $a
     * @param array $b
     * @return int
     */
    protected function startCompare($a, $b) {
        if ($a['tstart'] == $b['tstart']) {
            return 0;
        }
        if ($a['tstart'] < $b['tstart']) {
            return -1;
        }
        return 1;
    }

    /**
     * Order Time Ranges
     *
     * @return void
     */
    protected function orderTimeRanges() {
        foreach ($this->timerange as $key => $val) {
            usort($val, array("CentreonTimeperiodRenderer", "startCompare"));
            $this->timerange[$key] = $val;
        }
    }

    /**
     * Update Time Range
     *
     * @param array $inexTr
     * @return void
     */
    protected function updateTimeRange($inexTr) {
        foreach ($inexTr as $key => $val) {
            if (isset($val[0])) {
                foreach ($val as $tp) {
                    $this->timerange[$key][] = $tp;
                }
            }
        }
    }

    /**
     * Update Inclusions
     *
     * @return void
     */
    protected function updateInclusions() {
        $query = "SELECT timeperiod_include_id
        		  FROM timeperiod_include_relations
        		  WHERE timeperiod_id= '". $this->tpid. "'";
        $res = $this->db->query($query);
        while ($row = $res->fetchRow()) {
            $inctp = new CentreonTimeperiodRenderer($this->db, $row["timeperiod_include_id"], 1);
            $this->updateTimeRange($inctp->timerange);
            foreach ($inctp->exceptionList as $key => $val) {
                $this->exceptionList[] = $val;
            }
        }
    }

    /**
     * Update Exclusions
     *
     * @return void
     */
    protected function updateExclusions() {
        $query = "SELECT * FROM timeperiod_exceptions WHERE timeperiod_id='".$this->tpid."'";
        $DBRESULT = $this->db->query($query);
        while ($row = $DBRESULT->fetchRow()) {
            $excep = $this->getException($row["timeperiod_id"], $this->tpname, $row["days"], $row["timerange"]);
            $this->exceptionList[] = $excep;
        }
        $query = "SELECT timeperiod_exclude_id FROM timeperiod_exclude_relations WHERE timeperiod_id='".$this->tpid."'";
        $DBRESULT = $this->db->query($query);
        while ($row = $DBRESULT->fetchRow()) {
            $extp = new CentreonTimePeriodRenderer($this->db, $row["timeperiod_exclude_id"], 0);
            $this->updateTimeRange($extp->timerange);
        }
    }

    /**
     * Get time range array
     *
     * @param int $id
     * @param string $name
     * @param string $in
     * @param string $range
     * @return array
     */
    protected function getTimeRange($id, $name, $in, $range) {
        $timeRange = array();
        $timeRange['fromTpId'] = $id;
        $timeRange['fromTpName'] = $name;
        $timeRange['inex'] = $in;
        $timeRange['textual'] = $range;
        if (!preg_match("/([0-9]+):([0-9]+)-([0-9]+)+:([0-9]+)/", $range, $trange)) {
            return null;
        }
        if ($id < 1) {
            return null;
        }
        $timeRange['tstart'] = ($trange[1] * 60) + $trange[2];
        $timeRange['tend'] = ($trange[3] * 60) + $trange[4];

        return $timeRange;
    }

    /**
     * Get Exception
     *
     * @param int $id
     * @param string $name
     * @param string $day
     * @param string $range
     * @return array
     */
    protected function getException($id, $name, $day, $range) {
        $exception = array();
        $exception['fromTpId'] = $id;
        $exception['fromTpName']  = $name;
        $exception['day'] = $day;
        $exception['range'] = $range;

        return $exception;
    }


    /**
     * Get Time Period Name
     *
     * @return string
     */
    public function getName() {
        return $this->tpname;
    }

    /**
     * Get Time Period Alias
     *
     * @return string
     */
    public function getAlias() {
        return $this->tpalias;
    }

    /**
     * Get Timeline
     *
     * @return array
     */
    public function getTimeline() {
        return $this->timeline;
    }

    /**
     * Get Exception List
     *
     * @return array
     */
    public function getExceptionList() {
        return $this->exceptionList;
    }
}