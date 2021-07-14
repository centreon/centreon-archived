import * as React from 'react';

import { find, findIndex, isNil, lensPath, omit, propEq, set } from 'ramda';
import { useTranslation } from 'react-i18next';
import useDeepCompareEffect from 'use-deep-compare-effect';

import {
  useRequest,
  setUrlQueryParameters,
  getUrlQueryParameters,
} from '@centreon/ui';

import { labelNewFilter } from '../translatedLabels';

import { clearCachedFilter, storeFilter } from './storedFilter';
import { listCustomFilters } from './api';
import { listCustomFiltersDecoder } from './api/decoders';
import { Criteria, CriteriaValue } from './Criterias/models';
import {
  unhandledProblemsFilter,
  allFilter,
  isCustom,
  Filter,
  resourceProblemsFilter,
  newFilter,
} from './models';
import { getDefaultFilter } from './default';
import { build, parse } from './Criterias/searchQueryLanguage';

type EditPanelOpenDitpach = React.Dispatch<React.SetStateAction<boolean>>;
type CustomFiltersDispatch = React.Dispatch<
  React.SetStateAction<Array<Filter>>
>;

export interface FilterState {
  appliedFilter: Filter;
  applyCurrentFilter: () => void;
  applyFilter: (filter: Filter) => void;
  clearFilter: () => void;
  currentFilter: Filter;
  customFilters: Array<Filter>;
  customFiltersLoading: boolean;
  editPanelOpen: boolean;
  filterWithParsedSearch: Filter;
  filters: Array<Filter>;
  getCriteriaValue: (name: string) => CriteriaValue | undefined;
  loadCustomFilters: () => Promise<Array<Filter>>;
  search: string;
  setAppliedFilter: (filter: Filter) => void;
  setCriteria: ({ name, value }: { name: string; value }) => void;
  setCriteriaAndNewFilter: ({
    name,
    value,
    apply,
  }: {
    apply?: boolean;
    name: string;
    value;
  }) => void;
  setCurrentFilter: (filter: Filter) => void;
  setCustomFilters: CustomFiltersDispatch;
  setEditPanelOpen: EditPanelOpenDitpach;
  setNewFilter: () => void;
  setSearch: (string) => void;
}

const useFilter = (): FilterState => {
  const { t } = useTranslation();
  const {
    sendRequest: sendListCustomFiltersRequest,
    sending: customFiltersLoading,
  } = useRequest({
    decoder: listCustomFiltersDecoder,
    request: listCustomFilters,
  });

  const [customFilters, setCustomFilters] = React.useState<Array<Filter>>([]);
  const [currentFilter, setCurrentFilter] = React.useState(getDefaultFilter());
  const [appliedFilter, setAppliedFilter] = React.useState(getDefaultFilter());
  const [search, setSearch] = React.useState('');

  const [editPanelOpen, setEditPanelOpen] = React.useState<boolean>(false);

  const filterWithParsedSearch = {
    ...currentFilter,
    criterias: [
      ...parse(search),
      find(propEq('name', 'sort'), currentFilter.criterias) as Criteria,
    ],
  };

  const loadCustomFilters = (): Promise<Array<Filter>> => {
    return sendListCustomFiltersRequest().then(({ result }) => {
      setCustomFilters(result.map(omit(['order'])));

      return result;
    });
  };

  const getFilterWithUpdatedCriteria = ({ name, value }): Filter => {
    const index = findIndex(propEq('name', name))(
      filterWithParsedSearch.criterias,
    );
    const lens = lensPath(['criterias', index, 'value']);

    return set(lens, value, filterWithParsedSearch);
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

  const setCriteria = ({ name, value = false }): void => {
    setCurrentFilter(getFilterWithUpdatedCriteria({ name, value }));
  };

  const setCriteriaAndNewFilter = ({ name, value, apply = false }): void => {
    const isCustomFilter = isCustom(currentFilter);
    const updatedFilter = {
      ...getFilterWithUpdatedCriteria({ name, value }),
      ...(!isCustomFilter && newFilter),
    };

    setSearch(build(updatedFilter.criterias));

    if (apply) {
      applyFilter(updatedFilter);
      return;
    }

    setCurrentFilter(updatedFilter);
  };

  useDeepCompareEffect(() => {
    setSearch(build(currentFilter.criterias));
  }, [currentFilter.criterias]);

  React.useEffect(() => {
    storeFilter(filterWithParsedSearch);

    const queryParameters = [
      {
        name: 'filter',
        value: filterWithParsedSearch,
      },
    ];

    setUrlQueryParameters(queryParameters);
  }, [filterWithParsedSearch]);

  React.useEffect(() => {
    if (!getUrlQueryParameters().fromTopCounter) {
      return;
    }

    setUrlQueryParameters([
      {
        name: 'fromTopCounter',
        value: false,
      },
    ]);

    setCurrentFilter(getDefaultFilter());
  }, [getUrlQueryParameters().fromTopCounter]);

  React.useEffect(() => (): void => {
    clearCachedFilter();
  });

  const setNewFilter = (): void => {
    if (isCustom(currentFilter)) {
      return;
    }

    const emptyFilter = {
      criterias: currentFilter.criterias,
      id: '',
      name: t(labelNewFilter),
    };

    setCurrentFilter(emptyFilter);
  };

  const getCriteriaValue = (name: string): CriteriaValue | undefined => {
    const criteria = find<Criteria>(propEq('name', name))(
      filterWithParsedSearch.criterias,
    );

    if (isNil(criteria)) {
      return undefined;
    }

    return criteria.value;
  };

  const applyFilter = (filter: Filter): void => {
    setCurrentFilter(filter);
    setAppliedFilter(filter);
    setSearch(build(filter.criterias));
  };

  const applyCurrentFilter = (): void => {
    applyFilter(filterWithParsedSearch);
  };

  const clearFilter = (): void => {
    applyFilter(allFilter);
  };

  return {
    appliedFilter,
    applyCurrentFilter,
    applyFilter,
    clearFilter,
    currentFilter,
    customFilters,
    customFiltersLoading,
    editPanelOpen,
    filterWithParsedSearch,
    filters,
    getCriteriaValue,
    loadCustomFilters,
    search,
    setAppliedFilter,
    setCriteria,
    setCriteriaAndNewFilter,
    setCurrentFilter,
    setCustomFilters,
    setEditPanelOpen,
    setNewFilter,
    setSearch,
  };
};

export default useFilter;
