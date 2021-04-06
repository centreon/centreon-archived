import * as React from 'react';

import { hasPath, mergeDeepLeft, mergeDeepRight, pipe } from 'ramda';

import {
  useRequest,
  setUrlQueryParameters,
  getUrlQueryParameters,
} from '@centreon/ui';

import {
  getStoredOrDefaultFilter,
  clearCachedFilter,
  storeFilter,
} from './storedFilter';
import { Filter, Criterias, CriteriaValue } from './models';
import useAdapters from './api/adapters';
import { listCustomFilters } from './api';
import { listCustomFiltersDecoder } from './api/decoders';
import useFilterModels from './useFilterModels';

type FilterDispatch = React.Dispatch<React.SetStateAction<Filter>>;
type CriteriaValuesDispatch = React.Dispatch<
  React.SetStateAction<Array<CriteriaValue>>
>;
type SearchDispatch = React.Dispatch<React.SetStateAction<string | undefined>>;
type EditPanelOpenDitpach = React.Dispatch<React.SetStateAction<boolean>>;
type CustomFiltersDispatch = React.Dispatch<
  React.SetStateAction<Array<Filter>>
>;

export interface FilterState {
  currentSearch?: string;
  customFilters: Array<Filter>;
  customFiltersLoading: boolean;
  editPanelOpen: boolean;
  filter: Filter;
  hostGroups: Array<CriteriaValue>;
  loadCustomFilters: () => Promise<Array<Filter>>;
  nextSearch?: string;
  resourceTypes: Array<CriteriaValue>;
  serviceGroups: Array<CriteriaValue>;
  setCurrentSearch: SearchDispatch;
  setCustomFilters: CustomFiltersDispatch;
  setEditPanelOpen: EditPanelOpenDitpach;
  setFilter: FilterDispatch;
  setHostGroups: CriteriaValuesDispatch;
  setNextSearch: SearchDispatch;
  setResourceTypes: CriteriaValuesDispatch;
  setServiceGroups: CriteriaValuesDispatch;
  setStates: CriteriaValuesDispatch;
  setStatuses: CriteriaValuesDispatch;
  states: Array<CriteriaValue>;
  statuses: Array<CriteriaValue>;
  updatedFilter: Filter;
}

const useFilter = (): FilterState => {
  const {
    sendRequest: sendListCustomFiltersRequest,
    sending: customFiltersLoading,
  } = useRequest({
    decoder: listCustomFiltersDecoder,
    request: listCustomFilters,
  });

  const { unhandledProblemsFilter, allFilter, newFilter } = useFilterModels();
  const { toFilter, toFilterWithTranslatedCriterias } = useAdapters();

  const getDefaultFilter = (): Filter => {
    const defaultFilter = getStoredOrDefaultFilter(unhandledProblemsFilter);

    const urlQueryParameters = getUrlQueryParameters();

    if (hasPath(['filter'], urlQueryParameters)) {
      return pipe(
        mergeDeepLeft(urlQueryParameters.filter as Filter) as (t) => Filter,
        mergeDeepRight(allFilter) as (t) => Filter,
        toFilterWithTranslatedCriterias,
      )(newFilter) as Filter;
    }

    return defaultFilter;
  };

  const getDefaultCriterias = (): Criterias => getDefaultFilter().criterias;
  const getDefaultSearch = (): string | undefined =>
    getDefaultCriterias().search;
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

  const [customFilters, setCustomFilters] = React.useState<Array<Filter>>([]);
  const [filter, setFilter] = React.useState(getDefaultFilter());
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

  const [editPanelOpen, setEditPanelOpen] = React.useState<boolean>(false);

  const loadCustomFilters = (): Promise<Array<Filter>> => {
    return sendListCustomFiltersRequest().then(({ result }) => {
      const retrievedCustomFilters = result.map(toFilter);
      setCustomFilters(retrievedCustomFilters);

      return retrievedCustomFilters;
    });
  };

  const updatedFilter = {
    criterias: {
      hostGroups,
      resourceTypes,
      search: nextSearch,
      serviceGroups,
      states,
      statuses,
    },
    id: filter.id,
    name: filter.name,
  };

  React.useEffect(() => {
    loadCustomFilters();
  }, []);

  React.useEffect(() => {
    setCurrentSearch(nextSearch);
  }, [states, statuses, resourceTypes, hostGroups, serviceGroups]);

  React.useEffect(() => {
    storeFilter({
      criterias: {
        hostGroups,
        resourceTypes,
        search: nextSearch,
        serviceGroups,
        states,
        statuses,
      },
      id: filter.id,
      name: filter.name,
    });

    const queryParameters = [
      {
        name: 'filter',
        value: updatedFilter,
      },
    ];

    setUrlQueryParameters(queryParameters);
  }, [
    filter,
    nextSearch,
    resourceTypes,
    states,
    statuses,
    hostGroups,
    serviceGroups,
  ]);

  React.useEffect(() => {
    if (!getUrlQueryParameters().fromTopCounter) {
      return;
    }

    const { criterias } = getDefaultFilter();

    setUrlQueryParameters([
      {
        name: 'fromTopCounter',
        value: false,
      },
    ]);

    setFilter(getDefaultFilter());
    setResourceTypes(criterias.resourceTypes);
    setStatuses(criterias.statuses);
    setStates(criterias.states || []);
    setCurrentSearch(criterias.search);
    setNextSearch(criterias.search);
  }, [getUrlQueryParameters().fromTopCounter]);

  React.useEffect(() => (): void => {
    clearCachedFilter();
  });

  return {
    currentSearch,
    customFilters,
    customFiltersLoading,
    editPanelOpen,
    filter,
    hostGroups,
    loadCustomFilters,
    nextSearch,
    resourceTypes,
    serviceGroups,
    setCurrentSearch,
    setCustomFilters,
    setEditPanelOpen,
    setFilter,
    setHostGroups,
    setNextSearch,
    setResourceTypes,
    setServiceGroups,
    setStates,
    setStatuses,
    states,
    statuses,
    updatedFilter,
  };
};

export default useFilter;
