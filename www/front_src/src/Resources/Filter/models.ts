import {
  labelUnhandledProblems,
  labelResourceProblems,
  labelAll,
  labelUnhandled,
  labelAcknowledged,
  labelInDowntime,
  labelHost,
  labelService,
  labelOk,
  labelUp,
  labelWarning,
  labelDown,
  labelCritical,
  labelUnreachable,
  labelUnknown,
  labelPending,
} from '../translatedLabels';

export interface Filter {
  id: string;
  name: string;
}

export interface Criterias {
  resourceTypes: Array<Filter>;
  states: Array<Filter>;
  statuses: Array<Filter>;
  hostGroups: Array<Filter>;
  serviceGroups: Array<Filter>;
}

export type FilterGroup = {
  search?: string;
  criterias: Criterias;
} & Filter;

const unhandledState = {
  id: 'unhandled_problems',
  name: labelUnhandled,
};
const acknowledgedState = { id: 'acknowledged', name: labelAcknowledged };
const inDowntimeState = { id: 'in_downtime', name: labelInDowntime };

const states = [unhandledState, acknowledgedState, inDowntimeState];

const hostResourceType = { id: 'host', name: labelHost };
const serviceResourceType = { id: 'service', name: labelService };

const resourceTypes = [hostResourceType, serviceResourceType];

const okStatus = { id: 'OK', name: labelOk };
const upStatus = { id: 'UP', name: labelUp };
const warningStatus = { id: 'WARNING', name: labelWarning };
const downStatus = { id: 'DOWN', name: labelDown };
const criticalStatus = { id: 'CRITICAL', name: labelCritical };
const unreachableStatus = { id: 'UNREACHABLE', name: labelUnreachable };
const unknownStatus = { id: 'UNKNOWN', name: labelUnknown };
const pendingStatus = { id: 'PENDING', name: labelPending };

const statuses = [
  okStatus,
  upStatus,
  warningStatus,
  downStatus,
  criticalStatus,
  unreachableStatus,
  unknownStatus,
  pendingStatus,
];

const allFilter = {
  id: 'all',
  name: labelAll,
  criterias: {
    resourceTypes: [],
    states: [],
    statuses: [],
    hostGroups: [],
    serviceGroups: [],
  },
};

const unhandledProblemsFilter: FilterGroup = {
  id: 'unhandled_problems',
  name: labelUnhandledProblems,
  criterias: {
    resourceTypes: [],
    states: [unhandledState],
    statuses: [warningStatus, downStatus, criticalStatus, unknownStatus],
    hostGroups: [],
    serviceGroups: [],
  },
};

const resourceProblemsFilter: FilterGroup = {
  id: 'resource_problems',
  name: labelResourceProblems,
  criterias: {
    resourceTypes: [],
    states: [],
    statuses: [warningStatus, downStatus, criticalStatus, unknownStatus],
    hostGroups: [],
    serviceGroups: [],
  },
};

const filterById = {
  resource_problems: resourceProblemsFilter,
  all: allFilter,
  unhandled_problems: unhandledProblemsFilter,
};

export {
  allFilter,
  unhandledProblemsFilter,
  resourceProblemsFilter,
  resourceTypes,
  states,
  statuses,
  filterById,
};
