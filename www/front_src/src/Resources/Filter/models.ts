import { isNil } from 'ramda';
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
  labelNewFilter,
} from '../translatedLabels';

export interface CriteriaValue {
  id: number | string;
  name: string;
}

export interface Criterias {
  resourceTypes: Array<CriteriaValue>;
  states: Array<CriteriaValue>;
  statuses: Array<CriteriaValue>;
  hostGroups: Array<CriteriaValue>;
  serviceGroups: Array<CriteriaValue>;
  search?: string;
}

export interface Filter {
  id: string | number;
  name: string;
  criterias: Criterias;
}

export interface RawCriteria {
  name: string;
  objectType?: string;
  type: string;
  value?: Array<CriteriaValue> | string | boolean;
}

export interface RawFilter {
  id: number;
  name: string;
  criterias: Array<RawCriteria>;
}

const criteriaValueNameById = {
  acknowledged: labelAcknowledged,
  in_downtime: labelInDowntime,
  unhandled_problems: labelUnhandled,
  host: labelHost,
  service: labelService,
  OK: labelOk,
  UP: labelUp,
  WARNING: labelWarning,
  DOWN: labelDown,
  CRITICAL: labelCritical,
  UNREACHABLE: labelUnreachable,
  UNKNOWN: labelUnknown,
  PENDING: labelPending,
};

const unhandledStateId = 'unhandled_problems';
const unhandledState = {
  id: unhandledStateId,
  name: criteriaValueNameById[unhandledStateId],
};

const acknowledgedStateId = 'acknowledged';
const acknowledgedState = {
  id: 'acknowledged',
  name: criteriaValueNameById[acknowledgedStateId],
};

const inDowntimeStateId = 'in_downtime';
const inDowntimeState = {
  id: inDowntimeStateId,
  name: criteriaValueNameById[inDowntimeStateId],
};

const states = [unhandledState, acknowledgedState, inDowntimeState];

const hostResourceTypeId = 'host';
const hostResourceType = {
  id: hostResourceTypeId,
  name: criteriaValueNameById[hostResourceTypeId],
};

const serviceResourceTypeId = 'service';
const serviceResourceType = {
  id: serviceResourceTypeId,
  name: criteriaValueNameById[serviceResourceTypeId],
};

const resourceTypes = [hostResourceType, serviceResourceType];

const okStatusId = 'OK';
const okStatus = { id: okStatusId, name: criteriaValueNameById[okStatusId] };

const upStatusId = 'UP';
const upStatus = { id: upStatusId, name: criteriaValueNameById[upStatusId] };

const warningStatusId = 'WARNING';
const warningStatus = {
  id: warningStatusId,
  name: criteriaValueNameById[warningStatusId],
};

const downStatusId = 'DOWN';
const downStatus = {
  id: downStatusId,
  name: criteriaValueNameById[downStatusId],
};

const criticalStatusId = 'CRITICAL';
const criticalStatus = {
  id: criticalStatusId,
  name: criteriaValueNameById[criticalStatusId],
};

const unreachableStatusId = 'UNREACHABLE';
const unreachableStatus = {
  id: unreachableStatusId,
  name: criteriaValueNameById[unreachableStatusId],
};

const unknownStatusId = 'UNKNOWN';
const unknownStatus = {
  id: unknownStatusId,
  name: criteriaValueNameById[unknownStatusId],
};

const pendingStatusId = 'PENDING';
const pendingStatus = {
  id: pendingStatusId,
  name: criteriaValueNameById[pendingStatusId],
};

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
    search: undefined,
  },
};

const newFilter = {
  id: '',
  name: labelNewFilter,
};

const unhandledProblemsFilter: Filter = {
  id: 'unhandled_problems',
  name: labelUnhandledProblems,
  criterias: {
    search: undefined,
    resourceTypes: [],
    states: [unhandledState],
    statuses: [warningStatus, downStatus, criticalStatus, unknownStatus],
    hostGroups: [],
    serviceGroups: [],
  },
};

const resourceProblemsFilter: Filter = {
  id: 'resource_problems',
  name: labelResourceProblems,
  criterias: {
    resourceTypes: [],
    states: [],
    statuses: [warningStatus, downStatus, criticalStatus, unknownStatus],
    hostGroups: [],
    serviceGroups: [],
    search: undefined,
  },
};

const standardFilterById = {
  resource_problems: resourceProblemsFilter,
  all: allFilter,
  unhandled_problems: unhandledProblemsFilter,
};

const isCustom = ({ id }: Filter): boolean => {
  return isNil(standardFilterById[id]);
};

export {
  criteriaValueNameById,
  allFilter,
  unhandledProblemsFilter,
  resourceProblemsFilter,
  newFilter,
  resourceTypes,
  states,
  statuses,
  standardFilterById,
  isCustom,
};
