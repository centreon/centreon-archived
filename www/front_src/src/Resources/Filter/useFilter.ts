import * as React from 'react';

import { hasPath, mergeDeepLeft, mergeDeepRight, pipe } from 'ramda';

import {
  useRequest,
  setUrlQueryParameters,
  getUrlQueryParameters,
} from '@centreon/ui';

import { SortOrder } from '../Listing/models';

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
  customFilters: Array<Filter>;
  filter: Filter;
  updatedFilter: Omit<Filter, 'sort'>;
  filters: Array<Filter>;
  setFilter: FilterDispatch;
  setNewFilter: (sort: [string, SortOrder]) => void;
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
  setCustomFilters: CustomFiltersDispatch;
  customFiltersLoading: boolean;
  editPanelOpen: boolean;
  setEditPanelOpen: EditPanelOpenDitpach;
}

const useFilter = (): FilterState => {
  const {
    sendRequest: sendListCustomFiltersRequest,
    sending: customFiltersLoading,
  } = useRequest({
    request: listCustomFilters,
    decoder: listCustomFiltersDecoder,
  });

  const {
    unhandledProblemsFilter,
    allFilter,
    newFilter,
    resourceProblemsFilter,
    isCustom,
  } = useFilterModels();

  const { toFilter, toFilterWithTranslatedCriterias } = useAdapters();

  const getDefaultFilter = (): Filter => {
    const defaultFilter = getStoredOrDefaultFilter(unhandledProblemsFilter);

    const urlQueryParameters = getUrlQueryParameters();

    if (hasPath(['filter'], urlQueryParameters)) {
      const filterFromQueryParameters = pipe(
        mergeDeepLeft(urlQueryParameters.filter as Filter) as (t) => Filter,
        mergeDeepRight(allFilter) as (t) => Filter,
        toFilterWithTranslatedCriterias,
      )(newFilter) as Filter;

      const getSort = (): [string, SortOrder] => {
        const { sortf, sorto } = urlQueryParameters;

        if (sorto && sortf) {
          return [sortf as string, sorto as SortOrder];
        }

        return defaultFilter.sort;
      };

      return {
        ...filterFromQueryParameters,
        sort: getSort(),
      };
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

  const filters = [
    unhandledProblemsFilter,
    allFilter,
    resourceProblemsFilter,
    ...customFilters,
  ];

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

  const setNewFilter = (sort: [string, SortOrder]): void => {
    if (isCustom(filter)) {
      return;
    }
    setFilter({
      ...newFilter,
      criterias: filter.criterias,
      sort,
    });
  };

  return {
    filter,
    setFilter,
    updatedFilter,
    filters,
    customFilters,
    setNewFilter,
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
    setCustomFilters,
    customFiltersLoading,
    editPanelOpen,
    setEditPanelOpen,
  };
};

export default useFilter;
