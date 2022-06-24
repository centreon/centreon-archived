<?php

/*
 * Copyright 2005 - 2022 Centreon (https://www.centreon.com/)
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

namespace Centreon\Legacy\EventLogs\Export;

use CentreonACL;
use CentreonDB;
use PDOStatement;

class QueryGenerator
{
    /**
     * @var array<array<int,mixed>>
     */
    private array $queryValues = [];
    private ?int $is_admin;
    private string $openid = '';
    private string $output = '';
    private CentreonACL $access;
    private int $start;
    private int $end;
    private string $up;
    private string $down;
    private string $unreachable;
    private string $ok;
    private string $warning;
    private string $critical;
    private string $unknown;
    private string $notification;
    private string $alert;
    private string $error;
    private string $oh;
    /**
     * @var String[]
     */
    private array $hostMsgStatusSet = [];
    /**
     * @var String[]
     */
    private array $svcMsgStatusSet = [];
    /**
     * @var String[]
     */
    private array $tabHostIds = [];
    private string $searchHost = '';
    /**
     * @var array<mixed>
     */
    private array $tabSvc;
    private string $searchService = '';
    private string $engine;
    private string|int $export;
    private int $num = 0;
    private ?int $limit;

    public function __construct(private CentreonDB $pearDBO)
    {
    }

    /**
     * @param int|null $is_admin
     * @return void
     */
    public function setIsAdmin(?int $is_admin): void
    {
        $this->is_admin = $is_admin;
    }

    /**
     * @param string $openId
     * @return void
     */
    public function setOpenId(string $openId): void
    {
        $this->openid = $openId;
    }

    /**
     * @param string $output
     * @return void
     */
    public function setOutput(string $output): void
    {
        $this->output = $output;
    }

    /**
     * @param CentreonACL $access
     * @return void
     */
    public function setAccess(CentreonACL $access): void
    {
        $this->access = $access;
    }

    /**
     * @param int $start
     * @return void
     */
    public function setStart(int $start): void
    {
        $this->start = $start;
    }

    /**
     * @param int $end
     * @return void
     */
    public function setEnd(int $end): void
    {
        $this->end = $end;
    }

    /**
     * @param string $up
     * @return void
     */
    public function setUp(string $up): void
    {
        $this->up = $up;
    }

    /**
     * @param string $down
     * @return void
     */
    public function setDown(string $down): void
    {
        $this->down = $down;
    }

    /**
     * @param string $unreachable
     * @return void
     */
    public function setUnreachable(string $unreachable): void
    {
        $this->unreachable = $unreachable;
    }

    /**
     * @param string $ok
     * @return void
     */
    public function setOk(string $ok): void
    {
        $this->ok = $ok;
    }

    /**
     * @param string $warning
     * @return void
     */
    public function setWarning(string $warning): void
    {
        $this->warning = $warning;
    }

    /**
     * @param string $critical
     * @return void
     */
    public function setCritical(string $critical): void
    {
        $this->critical = $critical;
    }

    /**
     * @param string $unknown
     * @return void
     */
    public function setUnknown(string $unknown): void
    {
        $this->unknown = $unknown;
    }

    /**
     * @param string $notification
     * @return void
     */
    public function setNotification(string $notification): void
    {
        $this->notification = $notification;
    }

    /**
     * @param string $alert
     * @return void
     */
    public function setAlert(string $alert): void
    {
        $this->alert = $alert;
    }

    /**
     * @param string $error
     * @return void
     */
    public function setError(string $error): void
    {
        $this->error = $error;
    }

    /**
     * @param string $oh
     * @return void
     */
    public function setOh(string $oh): void
    {
        $this->oh = $oh;
    }

    /**
     * @param String[] $hostMsgStatusSet
     * @return void
     */
    public function setHostMsgStatusSet(array $hostMsgStatusSet): void
    {
        $this->hostMsgStatusSet = $hostMsgStatusSet;
    }

    /***
     * @param String[] $svcMsgStatusSet
     * @return void
     */
    public function setSvcMsgStatusSet(array $svcMsgStatusSet): void
    {
        $this->svcMsgStatusSet = $svcMsgStatusSet;
    }

    /**
     * @param String[] $tabHostIds
     * @return void
     */
    public function setTabHostIds(array $tabHostIds): void
    {
        $this->tabHostIds = $tabHostIds;
    }

    /**
     * @param string $searchHost
     * @return void
     */
    public function setSearchHost(string $searchHost): void
    {
        $this->searchHost = $searchHost;
    }

    /**
     * @param String[] $tabSvc
     * @return void
     */
    public function setTabSvc(array $tabSvc): void
    {
        $this->tabSvc = $tabSvc;
    }

    /**
     * @param string $searchService
     * @return void
     */
    public function setSearchService(string $searchService): void
    {
        $this->searchService = $searchService;
    }

    /**
     * @param string $engine
     * @return void
     */
    public function setEngine(string $engine): void
    {
        $this->engine = $engine;
    }

    /**
     * @param int|string $export
     * @return void
     */
    public function setExport(int|string $export): void
    {
        $this->export = $export;
    }

    /**
     * @param int $num
     * @return void
     */
    public function setNum(int $num): void
    {
        $this->num = $num;
    }

    /**
     * @param int|null $limit
     * @return void
     */
    public function setLimit(?int $limit): void
    {
        $this->limit = $limit;
    }

    /**
     * Generates executable PROStatement for all database records
     * @return PDOStatement
     */
    public function getStatement(): PDOStatement
    {
        $req = $this->generateQuery();
        $stmt = $this->pearDBO->prepare($req);

        foreach ($this->queryValues as $bindId => $bindData) {
            foreach ($bindData as $bindType => $bindValue) {
                $stmt->bindValue($bindId, $bindValue, $bindType);
            }
        }

        return $stmt;
    }

    /**
     * Generates SQL statement with placeholders for all database records
     * @return string
     */
    private function generateQuery(): string
    {
        $whereOutput = $this->generateWhere();
        $msg_req = $this->generateMsgHost();

        // Build final request
        $req = "SELECT SQL_CALC_FOUND_ROWS " . (!$this->is_admin ? "DISTINCT" : "") . "
            logs.ctime,
            logs.host_id,
            logs.host_name,
            logs.service_id,
            logs.service_description,
            logs.msg_type,
            logs.notification_cmd,
            logs.notification_contact,
            logs.output,
            logs.retry,
            logs.status,
            logs.type,
            logs.instance_name
            FROM logs " . $this->generateInnerJoinQuery()
            . (
            !$this->is_admin ?
                " INNER JOIN centreon_acl acl ON (logs.host_id = acl.host_id AND (acl.service_id IS NULL OR "
                . " acl.service_id = logs.service_id)) "
                . " WHERE acl.group_id IN (" . $this->access->getAccessGroupsString() . ") AND " :
                "WHERE "
            )
            . " logs.ctime > '{$this->start}' AND logs.ctime <= '{$this->end}' {$whereOutput} {$msg_req}";

        /*
         * Add Host
         */
        $str_unitH = "";
        $str_unitH_append = "";
        $host_search_sql = "";
        if (count($this->tabHostIds) == 0 && count($this->tabSvc) == 0) {
            if ($this->engine == "false") {
                $req .= " AND `msg_type` NOT IN ('4','5') ";
                $req .= " AND logs.host_name NOT LIKE '\\_Module\\_BAM%' ";
            }
        } else {
            foreach ($this->tabHostIds as $host_id) {
                if ($host_id != "") {
                    $str_unitH .= $str_unitH_append . "'$host_id'";
                    $str_unitH_append = ", ";
                }
            }
            if ($str_unitH != "") {
                $str_unitH = "(logs.host_id IN ($str_unitH) AND (logs.service_id IS NULL OR logs.service_id = 0))";
                if (isset($this->searchHost) && $this->searchHost != "") {
                    $host_search_sql =
                        " AND logs.host_name LIKE '%" . $this->pearDBO->escape($this->searchHost) . "%' ";
                }
            }

            /*
             * Add services
             */
            $flag = 0;
            $str_unitSVC = "";
            $service_search_sql = "";
            if (
                (count($this->tabSvc) || count($this->tabHostIds)) &&
                (
                    $this->up == 'true' ||
                    $this->down == 'true' ||
                    $this->unreachable == 'true' ||
                    $this->ok == 'true' || $this->warning == 'true' ||
                    $this->critical == 'true' ||
                    $this->unknown == 'true'
                )
            ) {
                $req_append = "";
                foreach ($this->tabSvc as $host_id => $services) {
                    $str = "";
                    $str_append = "";
                    foreach ($services as $svc_id => $svc_name) {
                        if ($svc_id != "") {
                            $str .= $str_append . $svc_id;
                            $str_append = ", ";
                        }
                    }
                    if ($str != "") {
                        if ($host_id === '_Module_Meta') {
                            $str_unitSVC .= $req_append . " (logs.host_name = '" . $host_id . "' "
                                . "AND logs.service_id IN (" . $str . ")) ";
                        } else {
                            $str_unitSVC .= $req_append .
                                " (logs.host_id = '" . $host_id . "' AND logs.service_id IN ($str)) ";
                        }
                        $req_append = " OR";
                    }
                }
                if (isset($this->searchService) && $this->searchService != "") {
                    $service_search_sql =
                        " AND logs.service_description LIKE '%" . $this->pearDBO->escape($this->searchService) . "%' ";
                }
                if ($str_unitH != "" && $str_unitSVC != "") {
                    $str_unitSVC = " OR " . $str_unitSVC;
                }
                if ($str_unitH != "" || $str_unitSVC != "") {
                    $req .= " AND (" . $str_unitH . $str_unitSVC . ")";
                }
            } else {
                $req .= "AND 0 ";
            }
            $req .= " AND logs.host_name NOT LIKE '\\_Module\\_BAM%' ";
            $req .= $host_search_sql . $service_search_sql;
        }

        $limit = ($this->export !== "1" && $this->num) ? $this->generateLimit() : '';

        $req .= ' ORDER BY ctime DESC ' . $limit;

        return $req;
    }

    /**
     * Generates limit statement
     * @return string
     */
    private function generateLimit(): string
    {
        if ($this->num < 0) {
            $this->num = 0;
        }

        $offset = $this->num * $this->limit;
        $this->queryValues['offset'] = [\PDO::PARAM_INT => $offset];
        $this->queryValues['limit'] = [\PDO::PARAM_INT => $this->limit];

        return " LIMIT :offset, :limit";
    }

    /**
     * Creates join statement with instances and filters on poller IDs
     * @return string
     */
    private function generateInnerJoinQuery(): string
    {
        $innerJoinEngineLog = '';
        if ($this->engine == "true" && isset($this->openid) && $this->openid != "") {
            // filtering poller ids and keeping only real ids
            $pollerIds = explode(',', $this->openid);
            $filteredIds = array_filter($pollerIds, function ($id) {
                return is_numeric($id);
            });

            if (count($filteredIds) > 0) {
                foreach ($filteredIds as $index => $filteredId) {
                    $key = ':pollerId' . $index;
                    $this->queryValues[$key] = [\PDO::PARAM_INT => $filteredId];
                    $pollerIds[] = $key;
                }
                $innerJoinEngineLog = ' INNER JOIN instances i ON i.name = logs.instance_name'
                    . ' AND i.instance_id IN ( ' . implode(',', array_values($pollerIds)) . ')';
            }
        }

        return $innerJoinEngineLog;
    }

    /**
     * Generates Where statement
     * @return string
     */
    private function generateWhere(): string
    {
        $whereOutput = "";
        if (isset($this->output) && $this->output != "") {
            $this->queryValues[':output'] = [\PDO::PARAM_STR => '%' . $this->output . '%'];
            $whereOutput = " AND logs.output like :output ";
        }

        return $whereOutput;
    }

    /**
     * Generates sub request with filters
     * @return string
     */
    private function generateMsgHost(): string
    {
        $msg_req = '';

        $flag_begin = 0;

        if ($this->notification == 'true') {
            if (count($this->hostMsgStatusSet)) {
                $msg_req .= "(";
                $flag_begin = 1;
                $msg_req .= " (`msg_type` = '3' ";
                $msg_req .= " AND `status` IN (" . implode(',', $this->hostMsgStatusSet) . "))";
                $msg_req .= ") ";
            }
            if (count($this->svcMsgStatusSet)) {
                if ($flag_begin == 0) {
                    $msg_req .= "(";
                } else {
                    $msg_req .= " OR ";
                }
                $msg_req .= " (`msg_type` = '2' ";
                $msg_req .= " AND `status` IN (" . implode(',', $this->svcMsgStatusSet) . "))";
                if ($flag_begin == 0) {
                    $msg_req .= ") ";
                }
                $flag_begin = 1;
            }
        }

        if ($this->alert == 'true') {
            if (count($this->hostMsgStatusSet)) {
                if ($flag_begin) {
                    $msg_req .= " OR ";
                }
                if ($this->oh == true) {
                    $msg_req .= " ( ";
                    $flag_oh = true;
                }
                $flag_begin = 1;
                $msg_req .= " ((`msg_type` IN ('1', '10', '11') ";
                $msg_req .= " AND `status` IN (" . implode(',', $this->hostMsgStatusSet) . ")) ";
                $msg_req .= ") ";
            }
            if (count($this->svcMsgStatusSet)) {
                if ($flag_begin) {
                    $msg_req .= " OR ";
                }
                if ($this->oh == true && !isset($flag_oh)) {
                    $msg_req .= " ( ";
                }
                $flag_begin = 1;
                $msg_req .= " ((`msg_type` IN ('0', '10', '11') ";
                $msg_req .= " AND `status` IN (" . implode(',', $this->svcMsgStatusSet) . ")) ";
                $msg_req .= ") ";
            }
            if ($flag_begin) {
                $msg_req .= ")";
            }
            if ((count($this->hostMsgStatusSet) || count($this->svcMsgStatusSet)) && $this->oh == 'true') {
                $msg_req .= " AND ";
            }
            if ($this->oh == 'true') {
                $flag_begin = 1;
                $msg_req .= " `type` = '1' ";
            }
        }
        // Error filter is only used in the engine log page.
        if ($this->error == 'true') {
            if ($flag_begin == 0) {
                $msg_req .= "AND ";
            } else {
                $msg_req .= " OR ";
            }
            $msg_req .= " `msg_type` IN ('4','5') ";
        }
        if ($flag_begin) {
            $msg_req = " AND (" . $msg_req . ") ";
        }

        return $msg_req;
    }
}
