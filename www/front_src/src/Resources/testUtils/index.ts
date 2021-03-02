import { findIndex, lensPath, propEq, set } from 'ramda';

import { CriteriaValue } from '../Filter/Criterias/models';
import { Filter } from '../Filter/models';
import { buildResourcesEndpoint } from '../Listing/api/endpoint';

interface EndpointParams {
  sort?;
  page?: number;
  limit?: number;
  search?: string;
  states?: Array<string>;
  statuses?: Array<string>;
  resourceTypes?: Array<string>;
  hostGroupIds?: Array<number>;
  serviceGroupIds?: Array<number>;
  monitoringServerIds?: Array<number>;
}

const defaultStatuses = ['WARNING', 'DOWN', 'CRITICAL', 'UNKNOWN'];
const defaultResourceTypes = [];
const defaultStates = ['unhandled_problems'];

const searchableFields = [
  'h.name',
  'h.alias',
  'h.address',
  's.description',
  'information',
];

const getListingEndpoint = ({
  page = 1,
  limit = 30,
  sort = { status_severity_code: 'asc' },
  statuses = defaultStatuses,
  states = defaultStates,
  resourceTypes = defaultResourceTypes,
  hostGroupIds = [],
  serviceGroupIds = [],
  monitoringServerIds = [],
  search,
}: EndpointParams): string =>
  buildResourcesEndpoint({
    page,
    limit,
    sort,
    statuses,
    states,
    search: search
      ? {
          regex: {
            value: search,
            fields: [
              'h.name',
              'h.alias',
              'h.address',
              's.description',
              'information',
            ],
          },
        }
      : undefined,
    resourceTypes,
    hostGroupIds,
    serviceGroupIds,
    monitoringServerIds,
  });

const cancelTokenRequestParam = { cancelToken: {} };

const mockAppStateSelector = (useSelector: jest.Mock): void => {
  const appState = {
    intervals: {
      AjaxTimeReloadMonitoring: 60,
    },
  };

  useSelector.mockImplementation((callback) => callback(appState));
};

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
  filter: Filter;
  criteriaName: string;
  criteriaValue: CriteriaValue;
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
  mockAppStateSelector,
  getListingEndpoint,
  cancelTokenRequestParam,
  defaultStatuses,
  defaultResourceTypes,
  defaultStates,
  searchableFields,
  getCriteriaValue,
  getFilterWithUpdatedCriteria,
};
