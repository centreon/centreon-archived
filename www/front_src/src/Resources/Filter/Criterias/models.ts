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
  labelType,
  labelServiceGroup,
  labelState,
  labelStatus,
  labelMonitoringServer,
  labelMetaService,
  labelStatusType,
  labelHard,
  labelSoft,
  labelHostCategory,
  labelServiceCategory,
  labelServiceSeverity,
  labelHostSeverity,
  labelHostSeverityLevel,
  labelServiceSeverityLevel,
  labelAnomalyDetection,
} from '../../translatedLabels';
import {
  buildHostGroupsEndpoint,
  buildHostCategoriesEndpoint,
  buildServiceCategoriesEndpoint,
  buildMonitoringServersEndpoint,
  buildServiceGroupsEndpoint,
  buildHostServeritiesEndpoint,
  buildServiceSeveritiesEndpoint,
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
  hard: labelHard,
  host: labelHost,
  in_downtime: labelInDowntime,
  metaservice: labelMetaService,
  service: labelService,
  soft: labelSoft,
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

const hardStateTypeId = 'hard';
const hardStateType = {
  id: hardStateTypeId,
  name: criteriaValueNameById[hardStateTypeId],
};

const softStateTypeId = 'soft';
const softStateType = {
  id: softStateTypeId,
  name: criteriaValueNameById[softStateTypeId],
};

const selectableStateTypes = [hardStateType, softStateType];

export interface CriteriaDisplayProps {
  autocompleteSearch?: { conditions: Array<Record<string, unknown>> };
  buildAutocompleteEndpoint?;
  label: string;
  options?: Array<SelectEntry>;
}

export interface CriteriaById {
  [criteria: string]: CriteriaDisplayProps;
}

export enum CriteriaNames {
  hostCategories = 'host_categories',
  hostGroups = 'host_groups',
  hostSeverities = 'host_severities',
  hostSeverityLevels = 'host_severity_levels',
  monitoringServers = 'monitoring_servers',
  resourceTypes = 'resource_types',
  serviceCategories = 'service_categories',
  serviceGroups = 'service_groups',
  serviceSeverities = 'service_severities',
  serviceSeverityLevels = 'service_severity_levels',
  states = 'states',
  statusTypes = 'status_types',
  statuses = 'statuses',
}

const selectableCriterias: CriteriaById = {
  [CriteriaNames.resourceTypes]: {
    label: labelType,
    options: selectableResourceTypes,
  },
  [CriteriaNames.states]: {
    label: labelState,
    options: selectableStates,
  },
  [CriteriaNames.statuses]: {
    label: labelStatus,
    options: selectableStatuses,
  },
  [CriteriaNames.statusTypes]: {
    label: labelStatusType,
    options: selectableStateTypes,
  },
  [CriteriaNames.hostGroups]: {
    buildAutocompleteEndpoint: buildHostGroupsEndpoint,
    label: labelHostGroup,
  },
  [CriteriaNames.serviceGroups]: {
    buildAutocompleteEndpoint: buildServiceGroupsEndpoint,
    label: labelServiceGroup,
  },
  [CriteriaNames.monitoringServers]: {
    autocompleteSearch: { conditions: [{ field: 'running', value: true }] },
    buildAutocompleteEndpoint: buildMonitoringServersEndpoint,
    label: labelMonitoringServer,
  },
  [CriteriaNames.hostCategories]: {
    buildAutocompleteEndpoint: buildHostCategoriesEndpoint,
    label: labelHostCategory,
  },
  [CriteriaNames.serviceCategories]: {
    buildAutocompleteEndpoint: buildServiceCategoriesEndpoint,
    label: labelServiceCategory,
  },
  [CriteriaNames.hostSeverities]: {
    buildAutocompleteEndpoint: buildHostServeritiesEndpoint,
    label: labelHostSeverity,
  },
  [CriteriaNames.serviceSeverities]: {
    buildAutocompleteEndpoint: buildServiceSeveritiesEndpoint,
    label: labelServiceSeverity,
  },
  [CriteriaNames.hostSeverityLevels]: {
    buildAutocompleteEndpoint: buildHostServeritiesEndpoint,
    label: labelHostSeverityLevel,
  },
  [CriteriaNames.serviceSeverityLevels]: {
    buildAutocompleteEndpoint: buildServiceSeveritiesEndpoint,
    label: labelServiceSeverityLevel,
  },
};

const authorizedFilterByModules = {
  'centreon-anomaly-detection': {
    'anomaly-detection': labelAnomalyDetection,
  },
};

export {
  authorizedFilterByModules,
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
  selectableStateTypes,
  hardStateType,
};
