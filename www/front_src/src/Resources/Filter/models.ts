import {
  labelUnhandledProblems,
  labelResourceProblems,
  labelAll,
  labelUnhandled,
  labelAcknowledged,
  labelInDowntime,
  labelHost,
  labelService,
} from '../translatedLabels';

export interface Filter {
  id: string;
  name: string;
}

const unhandledProblemsState = {
  id: 'unhandled_problems',
  name: labelUnhandled,
};
const acknowledgedState = { id: 'acknowledged', name: labelAcknowledged };
const inDowntimeState = { id: 'in_downtime', name: labelInDowntime };

const states = [unhandledProblemsState, acknowledgedState, inDowntimeState];

const hostResourceType = { id: 'host', name: labelHost };
const serviceResourceType = { id: 'service', name: labelService };

const resourceTypes = [hostResourceType, serviceResourceType];

const okStatus = { id: 'OK', name: 'Ok' };
const upStatus = { id: 'UP', name: 'Up' };
const warningStatus = { id: 'WARNING', name: 'Warning' };
const downStatus = { id: 'DOWN', name: 'Down' };
const criticalStatus = { id: 'CRITICAL', name: 'Critical' };
const unreachableStatus = { id: 'UNREACHABLE', name: 'Unreachable' };
const unknownStatus = { id: 'UNKNOWN', name: 'Unknown' };
const pendingStatus = { id: 'PENDING', name: 'Pending' };

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

const unhandledProblemsFilter = {
  id: 'unhandled_problems',
  name: labelUnhandledProblems,
  criterias: {
    resourceTypes,
    states: [unhandledProblemsState],
    statuses: [
      warningStatus,
      downStatus,
      criticalStatus,
      unreachableStatus,
      unknownStatus,
    ],
  },
};

const resourceProblemsFilter = {
  id: 'resource_problems',
  name: labelResourceProblems,
  criterias: {
    resourceTypes,
    states: [unhandledProblemsState],
    statuses: [
      warningStatus,
      downStatus,
      criticalStatus,
      unreachableStatus,
      unknownStatus,
    ],
  },
};

const emptyFilter = {
  id: 'empty',
  label: '',
};

const filterById = {
  resource_problems: resourceProblemsFilter,
  all: allFilter,
  unhandled_problems: unhandledProblemsFilter,
};

export {
  allFilter,
  emptyFilter,
  unhandledProblemsFilter,
  resourceProblemsFilter,
  resourceTypes,
  states,
  statuses,
  filterById,
};
