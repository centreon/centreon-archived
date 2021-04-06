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
  hostGroups: Array<CriteriaValue>;
  resourceTypes: Array<CriteriaValue>;
  search?: string;
  serviceGroups: Array<CriteriaValue>;
  states: Array<CriteriaValue>;
  statuses: Array<CriteriaValue>;
}

export interface Filter {
  criterias: Criterias;
  id: string | number;
  name: string;
}

export interface RawCriteria {
  name: string;
  object_type?: string;
  type: string;
  value?: Array<CriteriaValue> | string | boolean;
}

export interface RawFilter {
  criterias: Array<RawCriteria>;
  id: number;
  name: string;
}

const criteriaValueNameById = {
  CRITICAL: labelCritical,
  DOWN: labelDown,
  OK: labelOk,
  PENDING: labelPending,
  UNKNOWN: labelUnknown,
  UNREACHABLE: labelUnreachable,
  UP: labelUp,
  WARNING: labelWarning,
  acknowledged: labelAcknowledged,
  host: labelHost,
  in_downtime: labelInDowntime,
  service: labelService,
  unhandled_problems: labelUnhandled,
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
  criterias: {
    hostGroups: [],
    resourceTypes: [],
    search: undefined,
    serviceGroups: [],
    states: [],
    statuses: [],
  },
  id: 'all',
  name: labelAll,
};

const newFilter = {
  id: '',
  name: labelNewFilter,
};

const unhandledProblemsFilter: Filter = {
  criterias: {
    hostGroups: [],
    resourceTypes: [],
    search: undefined,
    serviceGroups: [],
    states: [unhandledState],
    statuses: [warningStatus, downStatus, criticalStatus, unknownStatus],
  },
  id: 'unhandled_problems',
  name: labelUnhandledProblems,
};

const resourceProblemsFilter: Filter = {
  criterias: {
    hostGroups: [],
    resourceTypes: [],
    search: undefined,
    serviceGroups: [],
    states: [],
    statuses: [warningStatus, downStatus, criticalStatus, unknownStatus],
  },
  id: 'resource_problems',
  name: labelResourceProblems,
};

const standardFilterById = {
  all: allFilter,
  resource_problems: resourceProblemsFilter,
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
