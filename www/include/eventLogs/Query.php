<?php

class Query
{
    private const SELECTED_TABLE_COLS = [
        'logs.ctime',
        'logs.host_id',
        'logs.host_name',
        'logs.service_id',
        'logs.service_description',
        'logs.msg_type',
        'logs.notification_cmd',
        'logs.notification_contact',
        'logs.output',
        'logs.retry',
        'logs.status',
        'logs.type',
        'logs.instance_name'
    ];

    private array $queryParts = [];
    private array $messageTypeFilters = [];
    private bool $isAdmin;
    private array $pollerIds = [];
    private string $accessGroupString = '';
    private int $start;
    private int $end;
    private string $outputFilterQuery = '';
    private string $outputFilterValue = '';
    private array $notificationHostStatusIds = [];
    private array $notificationServiceStatusIds = [];
    private array $alertHostStatusIds = [];
    private array $alertServiceStatusIds = [];
    private string $msgReq;

    public function setAccessGroupString(string $accessGroupString): void
    {
        $this->accessGroupString = $accessGroupString;
    }

    public function setIsAdmin(bool $isAdmin): void
    {
        $this->isAdmin = $isAdmin;
    }

    public function setPollerIds(array $pollerIds): void
    {
        $this->pollerIds = $pollerIds;
    }

    public function setStart(int $start): void
    {
        $this->start = $start;
    }

    public function setEnd(int $end): void
    {
        $this->end = $end;
    }

    /**
     * @todo Refactor
     */
    public function setMsgReq(string $msgReq): void
    {
        $this->msgReq = $msgReq;
    }

    public function addOutputFilter(string $output): void
    {
        $this->outputFilterQuery = 'logs.output like :output';
        $this->outputFilterValue = '%' . $output . '%';
    }

    public function addNotificationHostStatusIds(array $statusIds): void
    {
        $this->notificationHostStatusIds = $statusIds;
    }

    public function addNotificationServiceStatusIds(array $statusIds): void
    {
        $this->notificationServiceStatusIds = $statusIds;
    }

    public function addAlertHostStatusIds(array $statusIds): void
    {
        $this->alertHostStatusIds = $statusIds;
    }

    public function addAlertServiceStatusIds(array $statusIds): void
    {
        $this->alertServiceStatusIds = $statusIds;
    }

    public function getQuery(): string
    {
        $this->addSelect();
        $this->addFrom();
        $this->addWhere();

        return join(' ', $this->queryParts);
    }

    private function addSelect(): void
    {
        $this->queryParts[] = 'SELECT SQL_CALC_FOUND_ROWS';
        if (!$this->isAdmin) {
            $this->queryParts[] = 'DISTINCT';
        }
        $this->queryParts[] = join(',', self::SELECTED_TABLE_COLS);
    }

    private function addFrom(): void
    {
        $this->queryParts[] = 'FROM logs';
        $this->joinInstances();
        $this->joinACL();
    }

    private function joinInstances(): void
    {
        if (!empty($this->pollerIds)) {
            $this->queryParts[] = 'INNER JOIN instances i ON i.name = logs.instance_name AND i.instance_id IN ( ' . implode(',', $this->pollerIds) . ')';
        }
    }

    private function joinACL(): void
    {
        if (!$this->isAdmin) {
            $this->queryParts[] = 'INNER JOIN centreon_acl acl ON (logs.host_id = acl.host_id AND (acl.service_id IS NULL OR acl.service_id = logs.service_id))';
        }
    }

    private function addWhere(): void
    {
        $this->queryParts[] = 'WHERE';
        if (!$this->isAdmin) {
            $this->queryParts[] = 'acl.group_id IN (' . $this->accessGroupString . ') AND ';
        }

        $this->queryParts[] = sprintf("logs.ctime >'%d'", $this->start);
        $this->queryParts[] = sprintf("AND logs.ctime <= '%d'", $this->end);
        $this->queryParts[] = $this->outputFilterQuery;
        $this->applyMessageTypeFilter();
//        $this->queryParts[] = $this->msgReq;
    }

    private function applyMessageTypeFilter(): void
    {
        $this->applyNotificationHostFilter();
        $this->applyNotificationServiceFilter();
        $this->applyAlertHostFilter();
        $this->applyAlertServiceFilter();

        if (!empty($this->messageTypeFilters)) {
            $messageQueryFilters = join(' OR ', $this->messageTypeFilters);
            $this->queryParts[] = 'AND (' . $messageQueryFilters . ')';
        }
    }

    private function applyNotificationHostFilter(): void
    {
        if (!empty($this->notificationHostStatusIds)) {
            $statusList = implode(',', $this->notificationHostStatusIds);
            $query = "(`msg_type` = '3' AND `status` IN (" . $statusList . "))";
            $this->messageTypeFilters[] = $query;
        }
    }

    private function applyNotificationServiceFilter(): void
    {
        if (!empty($this->notificationServiceStatusIds)) {
            $statusList = implode(',', $this->notificationServiceStatusIds);
            $query = "(`msg_type` = '2' AND `status` IN (" . $statusList . "))";
            $this->messageTypeFilters[] = $query;
        }
    }

    private function applyAlertHostFilter(): void
    {
        if (!empty($this->alertHostStatusIds)) {
            $statusList = implode(',', $this->alertHostStatusIds);
            $query = "(`msg_type` IN ('1', '10', '11') AND `status` IN (" . $statusList . "))";
            $this->messageTypeFilters[] = $query;
        }
    }

    private function applyAlertServiceFilter(): void
    {
        if (!empty($this->alertServiceStatusIds)) {
            $statusList = implode(',', $this->alertServiceStatusIds);
            $query = "(`msg_type` IN ('0', '10', '11') AND `status` IN (" . $statusList . "))";
            $this->messageTypeFilters[] = $query;
        }
    }
}
