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
namespace Centreon\Legacy\EventLogs\Export;

class Formatter
{
    private const DATE_FORMAT = 'Y/m/d';
    private const TIME_FORMAT = 'H:i:s';
    private const DATE_TIME_FORMAT = 'Y/m/d (H:i:s)';
    private const SERVICE_ACKNOWLEDGEMENT_MSG_TYPE = 10;
    private const HOST_ACKNOWLEDGEMENT_MSG_TYPE = 11;
    private const ACKNOWLEDGMENT_MESSAGE_TYPE = 'ACK';
    private const INITIAL_STATE_VALUE = 'INITIAL STATE';
    private const NOTIFICATION_TYPE_VALUE = 'NOTIF';
    /**
     * @var string[]
     */
    private array $hosts = [];
    /**
     * @var string[]
     */
    private array $serviceStatuses = ['0' => 'OK', '1' => 'WARNING', '2' => 'CRITICAL', '3' => 'UNKNOWN'];
    /**
     * @var string[]
     */
    private array $hostStatuses = ['0' => 'UP', '1' => 'DOWN', '2' => 'UNREACHABLE',];
    /**
     * @var string[]
     */
    private array $notificationTypes = ['1' => 'HARD', '0' => 'SOFT'];
    private int $start = 0;
    private int $end = 0;
    private string $notification = '';
    private string $alert = '';
    private string $error = '';
    private string $up = '';
    private string $down = '';
    private string $unreachable = '';
    private string $ok = '';
    private string $warning = '';
    private string $critical = '';
    private string $unknown = '';

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
     * @param string[] $hosts
     * @return void
     */
    public function setHosts(array $hosts): void
    {
        $this->hosts = $hosts;
    }

    /**
     * Generates an array with metadata (filter values from http request)
     * @return mixed[]
     */
    public function formatMetaData(): array
    {
        return  [
            ['Begin date', 'End date'],
            [$this->formatStart(), $this->formatEnd()],
            [],
            ['Type', 'Notification', 'Alert', 'error'],
            ['', $this->notification, $this->alert, $this->error],
            [],
            ['Host', 'Up', 'Down', 'Unreachable'],
            ['', $this->up, $this->down, $this->unreachable],
            [],
            ['Service', 'Ok', 'Warning', 'Critical', 'Unknown'],
            ['', $this->ok, $this->warning, $this->critical, $this->unknown],
            [],
        ];
    }

    /**
     * Column names for CSV data table
     * @return string[]
     */
    public function getLogHeads(): array
    {
        return ['Day', 'Time', 'Host', 'Address', 'Service', 'Status', 'Type', 'Retry', 'Output', 'Contact', 'Cmd',];
    }

    /**
     * Generates formatted CSV  data
     * @param \PDOStatement $logs
     * @return \Iterator<String[]>
     */
    public function formatLogs(iterable $logs): iterable
    {
        foreach ($logs as $log) {
            yield $this->formatLog($log);
        }
    }

    /**
     * Formats individual log data for CSV
     * @param String[] $log
     * @return String[]
     */
    private function formatLog(array $log): array
    {
        return [
            'Day' => $this->dateFromTimestamp((int)$log['ctime']),
            'Time' => $this->timeFromTimestamp((int)$log['ctime']),
            'Host' => $log['host_name'],
            'Address' => $this->formatAddress($log['host_name']),
            'Service' => $log['service_description'],
            'Status' => $this->formatStatus($log['status'], $log['msg_type'], $log['service_description']),
            'Type' => $this->formatType($log['type'], $log['msg_type']),
            'Retry' => $this->formatRetry($log['retry'], $log['msg_type']),
            'Output' => $this->formatOutput($log['output'], $log['status']),
            'Contact' => $log['notification_contact'],
            'Cmd' => $log['notification_cmd'],
        ];
    }

    /**
     * Formats timestamp to date string
     * @param int $timestamp
     * @return string
     */
    private function dateFromTimestamp(int $timestamp): string
    {
        return date(self::DATE_FORMAT, $timestamp);
    }

    /**
     * Formats timestamp to time string
     * @param int $timestamp
     * @return string
     */
    private function timeFromTimestamp(int $timestamp): string
    {
        return date(self::TIME_FORMAT, $timestamp);
    }

    /**
     * Formats output value
     *
     * @param string $output
     * @param string $status
     * @return string
     */
    private function formatOutput(string $output, string $status): string
    {
        if ($output === '' && $status !== '') {
            return self::INITIAL_STATE_VALUE;
        }

        return $output;
    }

    /**
     * Formats host name to IP address
     * @param string $hostName
     * @return string
     */
    private function formatAddress(string $hostName): string
    {
        if (array_key_exists($hostName, $this->hosts)) {
            return (string)$this->hosts[$hostName];
        }

        return '';
    }

    /**
     * Formats status value query parameter to CSV data
     * @param string $status
     * @param string $msgType
     * @param string $serviceDescription
     * @return string
     */
    private function formatStatus(string $status, string $msgType, string $serviceDescription): string
    {
        if ($this->msgTypeIsAcknowledged($msgType)) {
            return self::ACKNOWLEDGMENT_MESSAGE_TYPE;
        }

        if ($serviceDescription && array_key_exists($status, $this->serviceStatuses)) {
            return $this->serviceStatuses[$status];
        }

        if (array_key_exists($status, $this->hostStatuses)) {
            return $this->hostStatuses[$status];
        }

        return $status;
    }

    /**
     * Checks that message type is one from the available list
     * @param string $msgType
     * @return bool
     */
    private function msgTypeIsAcknowledged(string $msgType): bool
    {
        return in_array($msgType, [self::HOST_ACKNOWLEDGEMENT_MSG_TYPE, self::SERVICE_ACKNOWLEDGEMENT_MSG_TYPE]);
    }

    /**
     * Formats type for CSV data
     * @param string $type
     * @param string $msgType
     * @return string
     */
    private function formatType(string $type, string $msgType): string
    {
        // For an ACK there is no point to display TYPE column
        if ($this->msgTypeIsAcknowledged($msgType)) {
            return '';
        }

        if (array_key_exists($type, $this->notificationTypes)) {
            return $this->notificationTypes[$type];
        }

        if ($this->typeIsNotification($type)) {
            return self::NOTIFICATION_TYPE_VALUE;
        }

        return $type;
    }

    /**
     * Checks that type is one of allowed one
     * @param string $type
     * @return bool
     */
    private function typeIsNotification(string $type): bool
    {
        return in_array($type, ['2', '3']);
    }

    /**
     * Formats retry query argument
     * @param string $retry
     * @param string $msgType
     * @return string
     */
    private function formatRetry(string $retry, string $msgType): string
    {
        // For an ACK there is no point to display RETRY column
        if ($this->msgTypeIsAcknowledged($msgType)) {
            return '';
        }

        if ((int)$msgType > 1) {
            return '';
        }

        return $retry;
    }

    /**
     * Converts end date to datetime format
     * @return string
     */
    private function formatEnd(): string
    {
        return $this->formatDateTime($this->end);
    }

    /**
     * Converts start date to datetime format
     *
     * @return string
     */
    private function formatStart(): string
    {
        return $this->formatDateTime($this->start);
    }

    /**
     * Formats timestamp to datetime string
     * @param int $date
     * @return string
     */
    private function formatDateTime(int $date): string
    {
        if ($date <= 0) {
            return '';
        }

        return date(self::DATE_TIME_FORMAT, $date);
    }
}
