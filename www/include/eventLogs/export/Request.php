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

class Request
{
    private const STATUS_UP = 0;
    private const STATUS_DOWN = 1;
    private const STATUS_UNREACHABLE = 2;
    private const STATUS_OK = 0;
    private const STATUS_WARNING = 1;
    private const STATUS_CRITICAL = 2;
    private const STATUS_UNKNOWN = 3;

    private ?int $is_admin;
    /**
     * @var array<string,mixed>
     */
    private array $lca = [];
    private ?string $lang = null;
    private ?string $id = null;
    private ?int $limit = null;
    private ?string $startDate = null;
    private ?string $startTime = null;
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
    /**
     * @var array<int, string>
     */
    private array $hostMsgStatusSet = [];
    /**
     * @var array<int, string>
     */
    private array $svcMsgStatusSet = [];
    /**
     * @var array<int, string>
     */
    private array $tabHostIds = [];
    /**
     * @var mixed[]
     */
    private array $tabSvc = [];

    public function __construct()
    {
        $this->populateClassPropertiesFromRequestParameters();
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
     * @param array<string,mixed> $lca
     * @return void
     */
    public function setLca(array $lca): void
    {
        $this->lca = $lca;
    }

    /**
     * @return string|null
     */
    public function getLang(): ?string
    {
        return $this->lang;
    }

    /**
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getOpenid(): string
    {
        $sanitized = \HtmlAnalyzer::sanitizeAndRemoveTags($this->getId() ?? '-1');

        if ($sanitized !== false) {
            return $sanitized;
        }

        return '';
    }

    /**
     * @return int|null
     */
    public function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * Retrieves timestamp for start date
     * @return int
     */
    public function getStart(): int
    {
        $start = 0;

        if ($this->startDate != '') {
            $start = $this->dateStringToTimestamp((string) $this->startDate);
        }

        // setting the startDate/Time using the user's chosen period
        // and checking if the start date/time was set by the user,
        // to avoid to display/export the whole data since 1/1/1970
        if ($this->getPeriod() > 0 || $start === 0) {
            $start = time() - $this->getPeriod();
        }

        return $start;
    }

    /**
     * * Retrieves timestamp for end date
     * @return int
     */
    public function getEnd(): int
    {
        $end = time();

        if ($this->endDate != '') {
            $end = $this->dateStringToTimestamp((string) $this->endDate);
        }

        return $end;
    }

    /**
     * Converts date string to timestamp
     * Expected format of date string : 06/22/2022 00:00
     *
     * @param string $dateString
     * @return int
     */
    private function dateStringToTimestamp(string $dateString): int
    {
        preg_match("/^([0-9]*)\/([0-9]*)\/([0-9]*)/", $dateString, $matchesD);
        preg_match("/^([0-9]*):([0-9]*)/", $dateString, $matchesT);

        $tmstp = mktime(
            (int) $matchesT[1],
            (int) $matchesT[2],
            0,
            (int) $matchesD[1],
            (int) $matchesD[2],
            (int) $matchesD[3]
        );

        if ($tmstp === false) {
            throw new \InvalidArgumentException('Unable to convert string to timestamp');
        }

        return $tmstp;
    }

    /**
     * @return string|null
     */
    public function getEndDate(): ?string
    {
        return $this->endDate;
    }

    /**
     * @return string|null
     */
    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    /**
     * @return int|null
     */
    public function getPeriod(): ?int
    {
        return $this->period ?? 0;
    }

    /**
     * @return string
     */
    public function getEngine(): string
    {
        return htmlentities($this->engine);
    }

    /**
     * @return string
     */
    public function getUp(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }
        return htmlentities($this->up);
    }

    /**
     * @return string
     */
    public function getDown(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->down);
    }

    /**
     * @return string
     */
    public function getUnreachable(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->unreachable);
    }

    /**
     * @return string
     */
    public function getOk(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }
        return htmlentities($this->ok);
    }

    /**
     * @return string
     */
    public function getWarning(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->warning);
    }

    /**
     * @return string
     */
    public function getCritical(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->critical);
    }

    /**
     * @return string
     */
    public function getUnknown(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }
        return htmlentities($this->unknown);
    }

    /**
     * @return string
     */
    public function getNotification(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->notification);
    }

    /**
     * @return string
     */
    public function getAlert(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->alert);
    }

    /**
     * @return string
     */
    public function getOh(): string
    {
        if ($this->getEngine() === 'true') {
            return 'false';
        }

        return htmlentities($this->oh);
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }

    /**
     * @return string
     */
    public function getOutput(): string
    {
        return urldecode($this->output);
    }

    /**
     * @return string|null
     */
    public function getSearchS(): ?string
    {
        return $this->searchS;
    }

    /**
     * @return string
     */
    public function getSearchHost(): string
    {
        return $this->charsToHtmlEntities($this->searchHost);
    }

    /**
     * @return string
     */
    public function getSearchService(): string
    {
        return$this->charsToHtmlEntities($this->searchService);
    }

    /**
     * @return string
     */
    public function getExport(): string
    {
        return $this->charsToHtmlEntities($this->export);
    }

    /**
     * Retrieves list of host message statuses depending on request parameters
     * @return String[]
     */
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

    /**
     * Retrieves list of service message statuses depending on request parameters
     * @return String[]
     */
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

    /**
     * Retrieves host ids depending on open id parameter
     * @return mixed[]
     */
    public function getTabHostIds(): array
    {
        $tab_id = preg_split("/\,/", $this->getOpenid());
        if ($tab_id === false) {
            throw new \InvalidArgumentException('Unable to parse open ID');
        }

        foreach ($tab_id as $openid) {
            $openIdChunks = $this->splitOpenId($openid);
            $id = $openIdChunks['id'];
            $type = $openIdChunks['type'];

            if ($id === '') {
                continue;
            }

            if ($type == 'HG' && (isset($this->lca["LcaHostGroup"][$id]) || $this->is_admin)) {
                // Get hosts from hostgroups @phpstan-ignore-next-line
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

    /**
     * Splits open id string into associative array of id, hostId and type
     *
     * @param string $openid
     * @return array<string, string>
     */
    private function splitOpenId(string $openid): array
    {
        $chunks = preg_split("/\_/", $openid);
        if (!is_array($chunks)) {
            return ['id' => '', 'hostId' => '', 'type' => ''];
        }

        $id = '';
        $hostId = '';

        if (isset($chunks[2])) {
            $hostId = $chunks[1];
            $id = $chunks[2];
        } elseif (isset($chunks[1])) {
            $id = $chunks[1];
        }

        return ['id' => $id, 'hostId' => $hostId, 'type' => $chunks[0]];
    }

    /**
     * Retrieves service ids depending on open id parameter
     * @return array<mixed>
     */
    public function getTabSvc(): array
    {
        $tab_id = preg_split("/\,/", $this->getOpenid());
        /** @phpstan-ignore-next-line */
        foreach ($tab_id as $openid) {
            $openIdChunks = $this->splitOpenId($openid);
            $id = $openIdChunks['id'];
            $type = $openIdChunks['type'];
            $hostId = $openIdChunks['hostId'];

            if ($id === '') {
                continue;
            }

            if ($type == "HG" && (isset($this->lca["LcaHostGroup"][$id]) || $this->is_admin)) {
                // Get hosts from hostgroups @phpstan-ignore-next-line
                $hosts = getMyHostGroupHosts($id);
                if (count($hosts) !== 0) {
                    foreach ($hosts as $h_id) {
                        if (isset($this->lca["LcaHost"][$h_id])) {
                            $this->tabSvc[$h_id] = $this->lca["LcaHost"][$h_id];
                        }
                    }
                }
            } elseif ($type == 'SG' && (isset($this->lca["LcaSG"][$id]) || $this->is_admin)) {
                /** @phpstan-ignore-next-line */
                $services = getMyServiceGroupServices($id);
                if (count($services) == 0) {
                    $this->tabSvc[] = "-1";
                } else {
                    foreach ($services as $svc_id => $svc_name) {
                        $tab_tmp = preg_split("/\_/", $svc_id);/** @phpstan-ignore-line @phpstan-ignore-next-line */
                        $tmp_host_id = $tab_tmp[0];
                        $tmp_service_id = $tab_tmp[1]; //@phpstan-ignore-line
                        if (isset($this->lca["LcaHost"][$tmp_host_id][$tmp_service_id])) {
                            $this->tabSvc[$tmp_host_id][$tmp_service_id] =
                                $this->lca["LcaHost"][$tmp_host_id][$tmp_service_id];
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

    /**
     * Populates class properties from request.
     * Request arguments are matched against property name. Request values are used as property values.
     * @return void
     */
    private function populateClassPropertiesFromRequestParameters(): void
    {
        $inputGet = [
            'lang' => isset($_GET['lang']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['lang']) : null,
            'id' => isset($_GET['lang']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['lang']) : null,
            'num' => filter_input(INPUT_GET, 'num', FILTER_VALIDATE_INT, ['options' => [ 'default' => 0]]),
            'limit' => filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT, ['options' => [ 'default' => 30]]),
            'StartDate' => isset($_GET['StartDate']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['StartDate']) : null,
            'EndDate' => isset($_GET['EndDate']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['EndDate']) : null,
            'StartTime' => isset($_GET['StartTime']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['StartTime']) : null,
            'EndTime' => isset($_GET['EndTime']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['EndTime']) : null,
            'period' => filter_input(INPUT_GET, 'num', FILTER_VALIDATE_INT),
            'engine' => isset($_GET['engine']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['engine']) : null,
            'up' => isset($_GET['up']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['up']) : null,
            'down' => isset($_GET['down']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['down']) : null,
            'unreachable' => isset($_GET['unreachable'])
                ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['unreachable'])
                : null,
            'ok' => isset($_GET['ok']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['ok']) : null,
            'warning' => isset($_GET['warning']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['warning']) : null,
            'critical' => isset($_GET['critical']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['critical']) : null,
            'unknown' => isset($_GET['unknown']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['unknown']) : null,
            'notification' => isset($_GET['notification'])
                ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['notification'])
                : null,
            'alert' => isset($_GET['alert']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['alert']) : null,
            'oh' => isset($_GET['oh']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['oh']) : null,
            'error' => isset($_GET['error']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['error']) : null,
            'output' => isset($_GET['output']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['output']) : null,
            'search_H' => isset($_GET['search_H']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['search_H']) : null,
            'search_S' => isset($_GET['search_S']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['search_S']) : null,
            'search_host' => isset($_GET['search_host'])
                ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['search_host'])
                : null,
            'search_service' => isset($_GET['search_service'])
                ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['search_service'])
                : null,
            'export' => isset($_GET['export']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_GET['export']) : null,
        ];

        $inputPost = [
            'lang' => isset($_POST['lang']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['lang']) : null,
            'id' => isset($_POST['lang']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['lang']) : null,
            'num' => filter_input(INPUT_POST, 'num', FILTER_VALIDATE_INT, ['options' => [ 'default' => 0]]),
            'limit' => filter_input(INPUT_POST, 'limit', FILTER_VALIDATE_INT, ['options' => [ 'default' => 30]]),
            'StartDate' => isset($_POST['StartDate'])
                ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['StartDate'])
                : null,
            'EndDate' => isset($_POST['EndDate']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['EndDate']) : null,
            'StartTime' => isset($_POST['StartTime'])
                ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['StartTime'])
                : null,
            'EndTime' => isset($_POST['EndTime']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['EndTime']) : null,
            'period' => filter_input(INPUT_POST, 'num', FILTER_VALIDATE_INT),
            'engine' => isset($_POST['engine']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['engine']) : null,
            'up' => isset($_POST['up']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['up']) : null,
            'down' => isset($_POST['down']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['down']) : null,
            'unreachable' => isset($_POST['unreachable'])
                ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['unreachable'])
                : null,
            'ok' => isset($_POST['ok']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['ok']) : null,
            'warning' => isset($_POST['warning']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['warning']) : null,
            'critical' => isset($_POST['critical']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['critical']) : null,
            'unknown' => isset($_POST['unknown']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['unknown']) : null,
            'notification' => isset($_POST['notification'])
                ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['notification'])
                : null,
            'alert' => isset($_POST['alert']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['alert']) : null,
            'oh' => isset($_POST['oh']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['oh']) : null,
            'error' => isset($_POST['error']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['error']) : null,
            'output' => isset($_POST['output']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['output']) : null,
            'search_H' => isset($_POST['search_H']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['search_H']) : null,
            'search_S' => isset($_POST['search_S']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['search_S']) : null,
            'search_host' => isset($_POST['search_host'])
                ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['search_host'])
                : null,
            'search_service' => isset($_POST['search_service'])
                ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['search_service'])
                : null,
            'export' => isset($_POST['export']) ? \HtmlAnalyzer::sanitizeAndRemoveTags($_POST['export']) : null,
        ];

        foreach (array_keys($inputGet) as $argumentName) {
            if (
                is_array($inputGet) &&
                array_key_exists($argumentName, $inputGet) &&
                !empty($inputGet[$argumentName])
            ) {
                $this->populateClassPropertyWithRequestArgument($argumentName, $inputGet[$argumentName]);
            } elseif (
                is_array($inputPost) &&
                array_key_exists($argumentName, $inputPost) &&
                !empty($inputPost[$argumentName])
            ) {
                $this->populateClassPropertyWithRequestArgument($argumentName, $inputPost[$argumentName]);
            }
        }
    }

    /**
     * Populates class properties from request.
     * Arguments are matched against property name. Values as property values.
     * @param mixed $argumentName
     * @param mixed $propertyValue
     * @return void
     */
    private function populateClassPropertyWithRequestArgument(mixed $argumentName, mixed $propertyValue): void
    {
        $propertyName = $this->stringToCamelCase($argumentName);
        if (property_exists($this, $propertyName)) {
            $this->$propertyName = $propertyValue;
        }
    }

    /**
     * Converts string to camelCase literal
     * @param string $string
     * @return string
     */
    private function stringToCamelCase(string $string): string
    {
        $str = str_replace('_', '', ucwords($string, '_'));

        return lcfirst($str);
    }

    /**
     * Convert string to HTML entities in order to secure against XSS vulnerabilities
     * @param string $string
     * @return string
     */
    private function charsToHtmlEntities(string $string): string
    {
        return htmlentities($string, ENT_QUOTES, 'UTF-8');
    }
}
