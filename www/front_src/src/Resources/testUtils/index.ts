import { findIndex, lensPath, propEq, set } from 'ramda';

import { CriteriaValue } from '../Filter/Criterias/models';
import { searchableFields } from '../Filter/Criterias/searchQueryLanguage';
import { Filter } from '../Filter/models';
import { buildResourcesEndpoint } from '../Listing/api/endpoint';
import { SortOrder } from '../models';

interface EndpointParams {
  hostCategories?: Array<string>;
  hostGroups?: Array<string>;
  hostSeverities?: Array<string>;
  hostSeverityLevels?: Array<number>;
  limit?: number;
  monitoringServers?: Array<string>;
  page?: number;
  resourceTypes?: Array<string>;
  search?: string;
  serviceCategories?: Array<string>;
  serviceGroups?: Array<string>;
  serviceSeverities?: Array<string>;
  serviceSeverityLevels?: Array<number>;
  sort?;
  states?: Array<string>;
  statusTypes?: Array<string>;
  statuses?: Array<string>;
}

const defaultStatuses = ['WARNING', 'DOWN', 'CRITICAL', 'UNKNOWN'];
const defaultResourceTypes = [];
const defaultStates = ['unhandled_problems'];
const defaultStateTypes = ['hard'];

const defaultSecondSortCriteria = { last_status_change: SortOrder.desc };

const getListingEndpoint = ({
  page = 1,
  limit = 30,
  sort = {
    status_severity_code: SortOrder.asc,
    ...defaultSecondSortCriteria
  },
  statuses = defaultStatuses,
  states = defaultStates,
  resourceTypes = defaultResourceTypes,
  hostGroups = [],
  hostCategories = [],
  hostSeverities = [],
  hostSeverityLevels = [],
  serviceCategories = [],
  serviceGroups = [],
  serviceSeverities = [],
  serviceSeverityLevels = [],
  monitoringServers = [],
  search,
  statusTypes = defaultStateTypes
}: EndpointParams): string =>
  buildResourcesEndpoint({
    hostCategories,
    hostGroups,
    hostSeverities,
    hostSeverityLevels,
    limit,
    monitoringServers,
    page,
    resourceTypes,
    search: search
      ? {
          regex: {
            fields: searchableFields,
            value: search
          }
        }
      : undefined,
    serviceCategories,
    serviceGroups,
    serviceSeverities,
    serviceSeverityLevels,
    sort,
    states,
    statusTypes,
    statuses
  });

const cancelTokenRequestParam = { cancelToken: {} };

interface CriteriaValueProps {
  filter: Filter;
  name: string;
}

const getCriteriaValue = ({
  filter,
  name
}: CriteriaValueProps): CriteriaValue | undefined => {
  return filter.criterias.find(propEq('name', name))?.value;
};

interface FilterAndCriteriaToUpdate {
  criteriaName: string;
  criteriaValue: CriteriaValue;
  filter: Filter;
}

const getFilterWithUpdatedCriteria = ({
  filter,
  criteriaName,
  criteriaValue
}: FilterAndCriteriaToUpdate): Filter => {
  const index = findIndex(propEq('name', criteriaName))(filter.criterias);
  const lens = lensPath(['criterias', index, 'value']);

  return set(lens, criteriaValue, filter);
};

export {
  getListingEndpoint,
  cancelTokenRequestParam,
  defaultStatuses,
  defaultResourceTypes,
  defaultStates,
  searchableFields,
  getCriteriaValue,
  getFilterWithUpdatedCriteria,
  defaultSecondSortCriteria,
  defaultStateTypes
};
