import { SelectEntry } from '@centreon/ui';

import { SortOrder } from '../../models';
import {
  labelAcknowledged,
  labelInDowntime,
  labelUnhandled,
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
  labelHostGroup,
  labelResource,
  labelServiceGroup,
  labelState,
  labelStatus,
  labelMonitoringServer,
  labelMetaService,
} from '../../translatedLabels';
import {
  buildHostGroupsEndpoint,
  buildMonitoringServersEndpoint,
  buildServiceGroupsEndpoint,
} from '../api/endpoint';

export type CriteriaValue = Array<SelectEntry> | string | [string, SortOrder];

export interface Criteria {
  name: string;
  object_type: string | null;
  type: string;
  value?: CriteriaValue;
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
  metaservice: labelMetaService,
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

const selectableStates = [unhandledState, acknowledgedState, inDowntimeState];

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

const metaServiceResourceTypeId = 'metaservice';
const metaServiceResourceType = {
  id: metaServiceResourceTypeId,
  name: criteriaValueNameById[metaServiceResourceTypeId],
};

const selectableResourceTypes = [
  hostResourceType,
  serviceResourceType,
  metaServiceResourceType,
];

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

const selectableStatuses = [
  okStatus,
  upStatus,
  warningStatus,
  downStatus,
  criticalStatus,
  unreachableStatus,
  unknownStatus,
  pendingStatus,
];

export interface CriteriaDisplayProps {
  autocompleteSearch?: Record<string, unknown>;
  buildAutocompleteEndpoint?;
  label: string;
  options?: Array<SelectEntry>;
  sortId: number;
}

export interface CriteriaById {
  [criteria: string]: CriteriaDisplayProps;
}

export enum SelectableCriteriasName {
  hostGroups = 'host_groups',
  monitoringServers = 'monitoring_servers',
  resourceTypes = 'resource_types',
  serviceGroups = 'service_groups',
  states = 'states',
  statuses = 'statuses',
}

const selectableCriterias: CriteriaById = {
  [SelectableCriteriasName.hostGroups]: {
    buildAutocompleteEndpoint: buildHostGroupsEndpoint,
    label: labelHostGroup,
    sortId: 3,
  },
  [SelectableCriteriasName.monitoringServers]: {
    autocompleteSearch: { conditions: [{ field: 'running', value: true }] },
    buildAutocompleteEndpoint: buildMonitoringServersEndpoint,
    label: labelMonitoringServer,
    sortId: 5,
  },
  [SelectableCriteriasName.resourceTypes]: {
    label: labelResource,
    options: selectableResourceTypes,
    sortId: 0,
  },
  [SelectableCriteriasName.serviceGroups]: {
    buildAutocompleteEndpoint: buildServiceGroupsEndpoint,
    label: labelServiceGroup,
    sortId: 4,
  },
  [SelectableCriteriasName.states]: {
    label: labelState,
    options: selectableStates,
    sortId: 1,
  },
  [SelectableCriteriasName.statuses]: {
    label: labelStatus,
    options: selectableStatuses,
    sortId: 2,
  },
};

export {
  unhandledState,
  warningStatus,
  downStatus,
  criticalStatus,
  unknownStatus,
  criteriaValueNameById,
  selectableResourceTypes,
  selectableStates,
  selectableStatuses,
  selectableCriterias,
};
