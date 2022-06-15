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

class Request
{
    private const STATUS_UP = 0;
    private const STATUS_DOWN = 1;
    private const STATUS_UNREACHABLE = 2;
    private const STATUS_OK = 0;
    private const STATUS_WARNING = 1;
    private const STATUS_CRITICAL = 2;
    private const STATUS_UNKNOWN = 3;
    private const STATUS_PENDING = 4;

    private ?int $is_admin;
    private array $lca = [];
    private ?string $lang = null;
    private ?string $id = null;
    private ?int $num = null;
    private ?int $limit = null;
    private int $start = 0;
    private ?string $startDate = null;
    private ?string $startTime = null;
    private int $end = 0;
    private ?string $endDate = null;
    private ?string $endTime = null;
    private ?int $period = null;
    private string $engine = 'false';
    private string $up = 'true';
    private string $down = 'true';
    private string $unreachable = 'true';
    private string $ok = 'true';
    private string $warning = 'true';
    private string $critical = 'true';
    private string $unknown = 'true';
    private string $notification = 'false';
    private string $alert = 'true';
    private string $oh = 'false';
    private string $error = 'false';
    private string $output = '';
    private string $searchH = 'VIDE';
    private string $searchS = 'VIDE';
    private string $searchHost = '';
    private string $searchService = '';
    private string $export = '0';
    private array $hostMsgStatusSet = [];
    private array $svcMsgStatusSet = [];
    private array $tabHostIds = [];
    private array $tabSvc = [];

    public function __construct()
    {
        $this->populateClassPropertiesFromRequestParameters();
    }

    public function setIsAdmin(?int $is_admin): void
    {
        $this->is_admin = $is_admin;
    }

    public function setLca(array $lca): void
    {
        $this->lca = $lca;
    }

    public function getLang(): ?string
    {
        return $this->lang;
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getOpenid()
    {
        return filter_var($this->getId() ?? '-1',FILTER_SANITIZE_STRING);
    }

    public function getNum(): ?int
    {
        return $this->num;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }

    public function getStart(): int
    {
        if ($this->startDate != '') {
            preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $this->startDate, $matchesD);
            preg_match("/^([0-9]*):([0-9]*)/", $this->startTime, $matchesT);
            $this->start = mktime((int) $matchesT[1], (int) $matchesT[2], 0, (int) $matchesD[1], (int) $matchesD[2], (int) $matchesD[3]);
        }

        // setting the startDate/Time using the user's chosen period
        // and checking if the start date/time was set by the user, to avoid to display/export the whole data since 1/1/1970
        if ($this->getPeriod() > 0 || $this->start === 0) {
            $this->start = time() - $this->getPeriod();
        }

        return $this->start;
    }

    public function getStartDate(): ?string
    {
        return $this->startDate;
    }

    public function getEnd(): int
    {
        $this->end = time();

        if ($this->endDate != '') {
            preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $this->endDate, $matchesD);
            preg_match("/^([0-9]*):([0-9]*)/", $this->endTime, $matchesT);
            $this->end = mktime((int) $matchesT[1], (int) $matchesT[2], 0, (int) $matchesD[1], (int) $matchesD[2], (int) $matchesD[3]);
        }

        return $this->end;
    }

    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    public function getPeriod(): ?int
    {
        return $this->period ?? 0;
    }

    public function getEngine(): string
    {
        return htmlentities($this->engine);
    }

    public function getUp(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }
        return htmlentities($this->up);
    }

    public function getDown(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->down);
    }

    public function getUnreachable(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->unreachable);
    }

    public function getOk(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }
        return htmlentities($this->ok);
    }

    public function getWarning(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->warning);
    }

    public function getCritical(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->critical);
    }

    public function getUnknown(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }
        return htmlentities($this->unknown);
    }

    public function getNotification(): ?string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->notification);
    }

    public function getAlert(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->alert);
    }

    public function getOh(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->oh);
    }

    public function getError(): ?string
    {
        return $this->error;
    }

    public function getOutput(): string
    {
        return urldecode($this->output);
    }

    public function getSearchH(): ?string
    {
        return $this->searchH;
    }

    public function getSearchS(): ?string
    {
        return $this->searchS;
    }

    public function getSearchHost(): string
    {
        return htmlentities($this->searchHost, ENT_QUOTES, 'UTF-8');
    }

    public function getSearchService(): string
    {
        return htmlentities($this->searchService, ENT_QUOTES, 'UTF-8');
    }

    public function getExport(): string
    {
        return htmlentities($this->export, ENT_QUOTES, 'UTF-8');
    }

    public function getHostMsgStatusSet(): array
    {
        if ($this->getUp() === 'true') {
            $this->hostMsgStatusSet[] = sprintf("'%s'", self::STATUS_UP);
        }
        if ($this->getDown() === 'true') {
            $this->hostMsgStatusSet[] = sprintf("'%s'", self::STATUS_DOWN);
        }
        if ($this->getUnreachable() === 'true') {
            $this->hostMsgStatusSet[] = sprintf("'%s'", self::STATUS_UNREACHABLE);
        }

        return $this->hostMsgStatusSet;
    }

    public function getSvcMsgStatusSet(): array
    {
        if ($this->getOk() === 'true') {
            $this->svcMsgStatusSet[] = sprintf("'%s'", self::STATUS_OK);
        }
        if ($this->getWarning() === 'true') {
            $this->svcMsgStatusSet[] = sprintf("'%s'", self::STATUS_WARNING);
        }
        if ($this->getCritical() === 'true') {
            $this->svcMsgStatusSet[] = sprintf("'%s'", self::STATUS_CRITICAL);
        }
        if ($this->getUnknown() === 'true') {
            $this->svcMsgStatusSet[] = sprintf("'%s'", self::STATUS_UNKNOWN);
        }

        return $this->svcMsgStatusSet;
    }

    public function getTabHostIds(): array
    {
        $tab_id = preg_split("/\,/", $this->getOpenid());
        foreach ($tab_id as $openid) {
            $tab_tmp = preg_split("/\_/", $openid);
            $id = '';

            if (isset($tab_tmp[2])) {
                $id = (int)$tab_tmp[2];
            } elseif (isset($tab_tmp[1])) {
                $id = (int)$tab_tmp[1];
            }

            if ($id === '') {
                continue;
            }

            $type = $tab_tmp[0];
            if ($type == 'HG' && (isset($this->lca["LcaHostGroup"][$id]) || $this->is_admin)) {
                // Get hosts from hostgroups
                $hosts = getMyHostGroupHosts($id);
                if (count($hosts) == 0) {
                    $this->tabHostIds[] = "-1";
                } else {
                    foreach ($hosts as $h_id) {
                        if (isset($this->lca["LcaHost"][$h_id])) {
                            $this->tabHostIds[] = $h_id;
                        }
                    }
                }
            } elseif ($type == "HH" && isset($this->lca["LcaHost"][$id])) {
                $this->tabHostIds[] = $id;
            }
        }

        return $this->tabHostIds;
    }

    public function getTabSvc(): array
    {
        $tab_id = preg_split("/\,/", $this->getOpenid());
        foreach ($tab_id as $openid) {
            $tab_tmp = preg_split("/\_/", $openid);
            $id = "";
            $hostId = "";

            if (isset($tab_tmp[2])) {
                $hostId = (int)$tab_tmp[1];
                $id = (int)$tab_tmp[2];
            } elseif (isset($tab_tmp[1])) {
                $id = (int)$tab_tmp[1];
            }

            if ($id == "") {
                continue;
            }

            $type = $tab_tmp[0];
            if ($type == "HG" && (isset($this->lca["LcaHostGroup"][$id]) || $this->is_admin)) {
                // Get hosts from hostgroups
                $hosts = getMyHostGroupHosts($id);
                if (count($hosts) !== 0) {
                    foreach ($hosts as $h_id) {
                        if (isset($this->lca["LcaHost"][$h_id])) {
                            $this->tabSvc[$h_id] = $this->lca["LcaHost"][$h_id];
                        }
                    }
                }
            } elseif ($type == 'SG' && (isset($this->lca["LcaSG"][$id]) || $this->is_admin)) {
                $services = getMyServiceGroupServices($id);
                if (count($services) == 0) {
                    $this->tabSvc[] = "-1";
                } else {
                    foreach ($services as $svc_id => $svc_name) {
                        $tab_tmp = preg_split("/\_/", $svc_id);
                        $tmp_host_id = $tab_tmp[0];
                        $tmp_service_id = $tab_tmp[1];
                        if (isset($this->lca["LcaHost"][$tmp_host_id][$tmp_service_id])) {
                            $this->tabSvc[$tmp_host_id][$tmp_service_id] = $this->lca["LcaHost"][$tmp_host_id][$tmp_service_id];
                        }
                    }
                }
            } elseif ($type == "HH" && isset($this->lca["LcaHost"][$id])) {
                $this->tabSvc[$id] = $this->lca["LcaHost"][$id];
            } elseif ($type == "HS" && isset($this->lca["LcaHost"][$hostId][$id])) {
                $this->tabSvc[$hostId][$id] = $this->lca["LcaHost"][$hostId][$id];
            } elseif ($type == "MS") {
                $this->tabSvc["_Module_Meta"][$id] = "meta_" . $id;
            }
        }

        return $this->tabSvc;
    }

    private function populateClassPropertiesFromRequestParameters(): void
    {
        $inputArguments = $this->getInputFilters();

        $inputGet = filter_input_array(INPUT_GET, $inputArguments);
        $inputPost = filter_input_array(INPUT_POST, $inputArguments);

        foreach (array_keys($inputArguments) as $argumentName) {
            if (!empty($inputGet[$argumentName])) {
                $this->populateClassPropertyWithRequestArgument($argumentName, $inputGet[$argumentName]);
            } elseif (!empty($inputPost[$argumentName])) {
                $this->populateClassPropertyWithRequestArgument($argumentName, $inputPost[$argumentName]);
            }
        }
    }

    private function populateClassPropertyWithRequestArgument(mixed $argumentName, mixed $propertyValue)
    {
        $propertyName = $this->stringToCamelCase($argumentName);
        if (property_exists($this, $propertyName)) {
            $this->$propertyName = $propertyValue;
        }
    }

    private function stringToCamelCase($string): string
    {
        $str = str_replace('_', '', ucwords($string, '_'));

        return lcfirst($str);
    }

    private function getInputFilters(int $defaultLimit = 30): array
    {
        return [
            'lang' => FILTER_SANITIZE_STRING,
            'id' => FILTER_SANITIZE_STRING,
            'num' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => [
                    'default' => 0
                ]
            ],
            'limit' => [
                'filter' => FILTER_VALIDATE_INT,
                'options' => [
                    'default' => $defaultLimit
                ]
            ],
            'StartDate' => FILTER_SANITIZE_STRING,
            'EndDate' => FILTER_SANITIZE_STRING,
            'StartTime' => FILTER_SANITIZE_STRING,
            'EndTime' => FILTER_SANITIZE_STRING,
            'period' => FILTER_VALIDATE_INT,
            'engine' => FILTER_SANITIZE_STRING,
            'up' => FILTER_SANITIZE_STRING,
            'down' => FILTER_SANITIZE_STRING,
            'unreachable' => FILTER_SANITIZE_STRING,
            'ok' => FILTER_SANITIZE_STRING,
            'warning' => FILTER_SANITIZE_STRING,
            'critical' => FILTER_SANITIZE_STRING,
            'unknown' => FILTER_SANITIZE_STRING,
            'notification' => FILTER_SANITIZE_STRING,
            'alert' => FILTER_SANITIZE_STRING,
            'oh' => FILTER_SANITIZE_STRING,
            'error' => FILTER_SANITIZE_STRING,
            'output' => FILTER_SANITIZE_STRING,
            'search_H' => FILTER_SANITIZE_STRING,
            'search_S' => FILTER_SANITIZE_STRING,
            'search_host' => FILTER_SANITIZE_STRING,
            'search_service' => FILTER_SANITIZE_STRING,
            'export' => FILTER_SANITIZE_STRING,
        ];
    }
}
