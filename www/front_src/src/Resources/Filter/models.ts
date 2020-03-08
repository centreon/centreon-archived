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

export type FilterGroup = {
  criterias: {
    resourceTypes: Array<Filter>;
    states: Array<Filter>;
    statuses: Array<Filter>;
  };
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

const okStatus = { id: 'ok', name: labelOk };
const upStatus = { id: 'up', name: labelUp };
const warningStatus = { id: 'warning', name: labelWarning };
const downStatus = { id: 'down', name: labelDown };
const criticalStatus = { id: 'critical', name: labelCritical };
const unreachableStatus = { id: 'unreachable', name: labelUnreachable };
const unknownStatus = { id: 'unknown', name: labelUnknown };
const pendingStatus = { id: 'pending', name: labelPending };

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
    resourceTypes,
    states,
    statuses,
  },
};

const unhandledProblemsFilter: FilterGroup = {
  id: 'unhandled_problems',
  name: labelUnhandledProblems,
  criterias: {
    resourceTypes,
    states: [unhandledState],
    statuses: [
      warningStatus,
      downStatus,
      criticalStatus,
      unreachableStatus,
      unknownStatus,
    ],
  },
};

const resourceProblemsFilter: FilterGroup = {
  id: 'resource_problems',
  name: labelResourceProblems,
  criterias: {
    resourceTypes,
    states: [unhandledState],
    statuses: [
      warningStatus,
      downStatus,
      criticalStatus,
      unreachableStatus,
      unknownStatus,
    ],
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
