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

class Formatter
{
    private const DATE_FORMAT = 'Y/m/d';
    private const TIME_FORMAT = 'H:i:s';
    private const DATE_TIME_FORMAT = 'Y/m/d (H:i:s)';
    private array $hosts = [];

    private const SERVICE_ACKNOWLEDGEMENT_MSG_TYPE = 10;
    private const HOST_ACKNOWLEDGEMENT_MSG_TYPE = 11;
    private const ACKNOWLEDGMENT_MESSAGE_TYPE = 'ACK';
    private array $serviceStatuses = ['0' => 'OK', '1' => 'WARNING', '2' => 'CRITICAL', '3' => 'UNKNOWN'];
    private array $hostStatuses = ['0' => 'UP', '1' => 'DOWN', '2' => 'UNREACHABLE',];
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

    public function setStart(int $start): void
    {
        $this->start = $start;
    }

    public function setEnd(int $end): void
    {
        $this->end = $end;
    }

    public function setNotification(string $notification): void
    {
        $this->notification = $notification;
    }

    public function setAlert(string $alert): void
    {
        $this->alert = $alert;
    }

    public function setError(string $error): void
    {
        $this->error = $error;
    }

    public function setUp(string $up): void
    {
        $this->up = $up;
    }

    public function setDown(string $down): void
    {
        $this->down = $down;
    }

    public function setUnreachable(string $unreachable): void
    {
        $this->unreachable = $unreachable;
    }

    /**
     * @param string $ok
     */
    public function setOk(string $ok): void
    {
        $this->ok = $ok;
    }

    /**
     * @param string $warning
     */
    public function setWarning(string $warning): void
    {
        $this->warning = $warning;
    }

    /**
     * @param string $critical
     */
    public function setCritical(string $critical): void
    {
        $this->critical = $critical;
    }

    /**
     * @param string $unknown
     */
    public function setUnknown(string $unknown): void
    {
        $this->unknown = $unknown;
    }

    public function setHosts(array $hosts): void
    {
        $this->hosts = $hosts;
    }

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

    public function getLogHeads(): array
    {
        return ['Day', 'Time', 'Host', 'Address', 'Service', 'Status', 'Type', 'Retry', 'Output', 'Contact', 'Cmd',];
    }

    public function formatLogs(iterable $logs): iterable
    {
        foreach ($logs as $log) {
            yield $this->formatLog($log);
        }
    }

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

    private function dateFromTimestamp(int $timestamp): string
    {
        return date(self::DATE_FORMAT, $timestamp);
    }

    private function timeFromTimestamp(int $timestamp): string
    {
        return date(self::TIME_FORMAT, $timestamp);
    }

    private function formatOutput(string $output, string $status): string
    {
        if ($output === '' && $status !== '') {
            return 'INITIAL STATE';
        }

        return $output;
    }

    private function formatAddress(string $hostName): string
    {
        if (array_key_exists($hostName, $this->hosts)) {
            return (string)$this->hosts[$hostName];
        }

        return '';
    }

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

    private function msgTypeIsAcknowledged(string $msgType): bool
    {
        return in_array($msgType, [self::HOST_ACKNOWLEDGEMENT_MSG_TYPE, self::SERVICE_ACKNOWLEDGEMENT_MSG_TYPE]);
    }

    private function formatType(string $type, string $msgType): string
    {
        // For an ACK there is no point to display TYPE column
        if ($this->msgTypeIsAcknowledged($msgType)) {
            return '';
        }

        if (array_key_exists($type, $this->notificationTypes)) {
            return $this->notificationTypes[$type];
        }

        if ($type === '2' || $type === '3') {
            return 'NOTIF';
        }

        return $type;
    }

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

    private function formatEnd(): string
    {
        return $this->formatDateTime($this->end);
    }

    private function formatStart(): string
    {
        return $this->formatDateTime($this->start);
    }

    private function formatDateTime(int $date): string
    {
        if ($date <= 0) {
            return '';
        }

        return date(self::DATE_TIME_FORMAT, $date);
    }
}
