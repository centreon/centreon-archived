import * as React from 'react';

import { useRequest } from '@centreon/ui';

import {
  getStoredOrDefaultFilter,
  clearCachedFilter,
  storeFilter,
} from './storedFilter';
import { Filter, Criterias, CriteriaValue } from './models';
import { toFilter } from './api/adapters';
import { listCustomFilters } from './api';
import { listCustomFiltersDecoder } from './api/decoders';

const getDefaultFilter = (): Filter => getStoredOrDefaultFilter();
const getDefaultCriterias = (): Criterias => getDefaultFilter().criterias;
const getDefaultSearch = (): string | undefined => getDefaultCriterias().search;
const getDefaultResourceTypes = (): Array<CriteriaValue> =>
  getDefaultCriterias().resourceTypes;
const getDefaultStates = (): Array<CriteriaValue> =>
  getDefaultCriterias().states;
const getDefaultStatuses = (): Array<CriteriaValue> =>
  getDefaultCriterias().statuses;
const getDefaultHostGroups = (): Array<CriteriaValue> =>
  getDefaultCriterias().hostGroups;
const getDefaultServiceGroups = (): Array<CriteriaValue> =>
  getDefaultCriterias().serviceGroups;

type FilterDispatch = React.Dispatch<React.SetStateAction<Filter>>;
type CriteriaValuesDispatch = React.Dispatch<
  React.SetStateAction<Array<CriteriaValue>>
>;
type SearchDispatch = React.Dispatch<React.SetStateAction<string | undefined>>;

export interface FilterState {
  customFilters?: Array<Filter>;
  filter: Filter;
  updatedFilter: Filter;
  setFilter: FilterDispatch;
  currentSearch?: string;
  setCurrentSearch: SearchDispatch;
  nextSearch?: string;
  setNextSearch: SearchDispatch;
  resourceTypes: Array<CriteriaValue>;
  setResourceTypes: CriteriaValuesDispatch;
  states: Array<CriteriaValue>;
  setStates: CriteriaValuesDispatch;
  statuses: Array<CriteriaValue>;
  setStatuses: CriteriaValuesDispatch;
  hostGroups: Array<CriteriaValue>;
  setHostGroups: CriteriaValuesDispatch;
  serviceGroups: Array<CriteriaValue>;
  setServiceGroups: CriteriaValuesDispatch;
  loadCustomFilters: () => Promise<Array<Filter>>;
}

const useFilter = (): FilterState => {
  const { sendRequest: sendListCustomFiltersRequest } = useRequest({
    request: listCustomFilters,
    decoder: listCustomFiltersDecoder,
  });

  const [customFilters, setCustomFilters] = React.useState<Array<Filter>>();
  const [filter, setFilter] = React.useState(getStoredOrDefaultFilter());
  const [currentSearch, setCurrentSearch] = React.useState<string | undefined>(
    getDefaultSearch(),
  );
  const [nextSearch, setNextSearch] = React.useState<string | undefined>(
    getDefaultSearch(),
  );
  const [resourceTypes, setResourceTypes] = React.useState<
    Array<CriteriaValue>
  >(getDefaultResourceTypes());
  const [states, setStates] = React.useState<Array<CriteriaValue>>(
    getDefaultStates(),
  );
  const [statuses, setStatuses] = React.useState<Array<CriteriaValue>>(
    getDefaultStatuses(),
  );
  const [hostGroups, setHostGroups] = React.useState<Array<CriteriaValue>>(
    getDefaultHostGroups(),
  );
  const [serviceGroups, setServiceGroups] = React.useState<
    Array<CriteriaValue>
  >(getDefaultServiceGroups());

  const loadCustomFilters = (): Promise<Array<Filter>> => {
    setCustomFilters(undefined);

    return sendListCustomFiltersRequest().then(({ result }) => {
      const retrievedCustomFilters = result.map(toFilter);
      setCustomFilters(retrievedCustomFilters);

      return retrievedCustomFilters;
    });
  };

  const updatedFilter = {
    id: filter.id,
    name: filter.name,
    criterias: {
      search: nextSearch,
      resourceTypes,
      states,
      statuses,
      hostGroups,
      serviceGroups,
    },
  };

  React.useEffect(() => {
    loadCustomFilters();
  }, []);

  React.useEffect(() => {
    setCurrentSearch(nextSearch);
  }, [states, statuses, resourceTypes, hostGroups, serviceGroups]);

  React.useEffect(() => {
    storeFilter({
      ...filter,
      criterias: {
        resourceTypes,
        states,
        statuses,
        hostGroups,
        serviceGroups,
        search: nextSearch,
      },
    });
  }, [
    filter,
    nextSearch,
    resourceTypes,
    states,
    statuses,
    hostGroups,
    serviceGroups,
  ]);

  React.useEffect(() => (): void => {
    clearCachedFilter();
  });

  return {
    filter,
    setFilter,
    updatedFilter,
    customFilters,
    currentSearch,
    setCurrentSearch,
    nextSearch,
    setNextSearch,
    resourceTypes,
    setResourceTypes,
    states,
    setStates,
    statuses,
    setStatuses,
    hostGroups,
    setHostGroups,
    serviceGroups,
    setServiceGroups,
    loadCustomFilters,
  };
};

export default useFilter;
