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
} from '../../translatedLabels';
import {
  buildHostGroupsEndpoint,
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

const selectableResourceTypes = [hostResourceType, serviceResourceType];

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
  label: string;
  options?: Array<SelectEntry>;
  buildAutocompleteEndpoint?;
}

export interface CriteriaById {
  [criteria: string]: CriteriaDisplayProps;
}

const selectableCriterias: CriteriaById = {
  resource_types: {
    label: labelResource,
    options: selectableResourceTypes,
  },
  states: {
    label: labelState,
    options: selectableStates,
  },
  statuses: {
    label: labelStatus,
    options: selectableStatuses,
  },
  host_groups: {
    label: labelHostGroup,
    buildAutocompleteEndpoint: buildHostGroupsEndpoint,
  },
  service_groups: {
    label: labelServiceGroup,
    buildAutocompleteEndpoint: buildServiceGroupsEndpoint,
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
