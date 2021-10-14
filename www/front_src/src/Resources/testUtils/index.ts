import { findIndex, lensPath, propEq, set } from 'ramda';

import { CriteriaValue } from '../Filter/Criterias/models';
import { searchableFields } from '../Filter/Criterias/searchQueryLanguage';
import { Filter } from '../Filter/models';
import { buildResourcesEndpoint } from '../Listing/api/endpoint';
import { SortOrder } from '../models';

interface EndpointParams {
  hostGroups?: Array<string>;
  limit?: number;
  monitoringServers?: Array<string>;
  page?: number;
  resourceTypes?: Array<string>;
  search?: string;
  serviceGroups?: Array<string>;
  sort?;
  states?: Array<string>;
  statuses?: Array<string>;
}

const defaultStatuses = ['WARNING', 'DOWN', 'CRITICAL', 'UNKNOWN'];
const defaultResourceTypes = [];
const defaultStates = ['unhandled_problems'];

const secondSortCriteria = { last_status_change: SortOrder.desc };

const getListingEndpoint = ({
  page = 1,
  limit = 30,
  sort = {
    status_severity_code: SortOrder.asc,
    ...secondSortCriteria,
  },
  statuses = defaultStatuses,
  states = defaultStates,
  resourceTypes = defaultResourceTypes,
  hostGroups = [],
  serviceGroups = [],
  monitoringServers = [],
  search,
}: EndpointParams): string =>
  buildResourcesEndpoint({
    hostGroups,
    limit,
    monitoringServers,
    page,
    resourceTypes,
    search: search
      ? {
          regex: {
            fields: searchableFields,
            value: search,
          },
        }
      : undefined,
    serviceGroups,
    sort,
    states,
    statuses,
  });

const cancelTokenRequestParam = { cancelToken: {} };

interface CriteriaValueProps {
  filter: Filter;
  name: string;
}

const getCriteriaValue = ({
  filter,
  name,
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
  criteriaValue,
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
  secondSortCriteria,
};
