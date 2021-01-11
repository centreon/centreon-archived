import { useTranslation } from 'react-i18next';
import { isNil } from 'ramda';

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
  labelAll,
  labelNewFilter,
  labelUnhandledProblems,
  labelResourceProblems,
} from '../translatedLabels';
import { defaultSortField, defaultSortOrder } from '../Listing/columns';

import { Filter, CriteriaValue } from './models';

interface FilterModelsContext {
  criteriaValueNameById: { [id: string]: string };
  allFilter: Filter;
  unhandledProblemsFilter: Filter;
  resourceProblemsFilter: Filter;
  newFilter: Filter;
  resourceTypes: Array<CriteriaValue>;
  states: Array<CriteriaValue>;
  statuses: Array<CriteriaValue>;
  standardFilterById: { [id: string]: Filter };
  isCustom: (filter: Filter) => boolean;
}

const useFilterModels = (): FilterModelsContext => {
  const { t } = useTranslation();

  const criteriaValueNameById = {
    acknowledged: t(labelAcknowledged),
    in_downtime: t(labelInDowntime),
    unhandled_problems: t(labelUnhandled),
    host: t(labelHost),
    service: t(labelService),
    OK: t(labelOk),
    UP: t(labelUp),
    WARNING: t(labelWarning),
    DOWN: t(labelDown),
    CRITICAL: t(labelCritical),
    UNREACHABLE: t(labelUnreachable),
    UNKNOWN: t(labelUnknown),
    PENDING: t(labelPending),
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

  const newFilter = {
    id: '',
    name: t(labelNewFilter),
  } as Filter;

  const allFilter: Filter = {
    id: 'all',
    name: t(labelAll),
    criterias: {
      resourceTypes: [],
      states: [],
      statuses: [],
      hostGroups: [],
      serviceGroups: [],
      search: undefined,
    },
    sort: [defaultSortField, defaultSortOrder],
  };

  const unhandledProblemsFilter: Filter = {
    id: 'unhandled_problems',
    name: t(labelUnhandledProblems),
    criterias: {
      search: undefined,
      resourceTypes: [],
      states: [unhandledState],
      statuses: [warningStatus, downStatus, criticalStatus, unknownStatus],
      hostGroups: [],
      serviceGroups: [],
    },
    sort: [defaultSortField, defaultSortOrder],
  };

  const resourceProblemsFilter: Filter = {
    id: 'resource_problems',
    name: t(labelResourceProblems),
    criterias: {
      resourceTypes: [],
      states: [],
      statuses: [warningStatus, downStatus, criticalStatus, unknownStatus],
      hostGroups: [],
      serviceGroups: [],
      search: undefined,
    },
    sort: [defaultSortField, defaultSortOrder],
  };

  const standardFilterById = {
    resource_problems: resourceProblemsFilter,
    all: allFilter,
    unhandled_problems: unhandledProblemsFilter,
  };

  const isCustom = ({ id }: Filter): boolean => {
    return isNil(standardFilterById[id]);
  };

  return {
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
};

export default useFilterModels;
