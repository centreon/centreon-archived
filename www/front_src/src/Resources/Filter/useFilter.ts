import * as React from 'react';

import {
  equals,
  find,
  findIndex,
  isNil,
  lensPath,
  omit,
  pipe,
  propEq,
  reject,
  set,
  sortBy,
} from 'ramda';
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
import {
  Criteria,
  CriteriaValue,
  selectableCriterias,
} from './Criterias/models';
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
  currentFilter: Filter;
  customFilters: Array<Filter>;
  customFiltersLoading: boolean;
  editPanelOpen: boolean;
  filterWithParsedSearch: Filter;
  filters: Array<Filter>;
  getCriteriaValue: (name: string) => CriteriaValue | undefined;
  getMultiSelectCriterias: () => Array<Criteria>;
  isCurrentFilterApplied: boolean;
  loadCustomFilters: () => Promise<Array<Filter>>;
  search: string;
  setAppliedFilter: (filter: Filter) => void;
  setCriteria: ({ name, value }: { name: string; value }) => void;
  setCriteriaAndNewFilter: ({ name, value }: { name: string; value }) => void;
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
    criterias: parse(search),
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

  const setCriteria = ({ name, value }): void => {
    setCurrentFilter(getFilterWithUpdatedCriteria({ name, value }));
  };

  const setCriteriaAndNewFilter = ({ name, value }): void => {
    const isCustomFilter = isCustom(currentFilter);
    const updatedFilter = {
      ...getFilterWithUpdatedCriteria({ name, value }),
      ...(!isCustomFilter && newFilter),
    };

    setSearch(build(updatedFilter.criterias));

    setCurrentFilter(updatedFilter);
  };

  useDeepCompareEffect(() => {
    setSearch(build(currentFilter.criterias));
  }, [currentFilter.criterias]);

  React.useEffect(() => {
    storeFilter(currentFilter);

    const queryParameters = [
      {
        name: 'filter',
        value: currentFilter,
      },
    ];

    setUrlQueryParameters(queryParameters);
  }, [currentFilter]);

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
    setAppliedFilter(emptyFilter);
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

  const getMultiSelectCriterias = (): Array<Criteria> => {
    const getSelectableCriteriaByName = (name: string) =>
      selectableCriterias[name];

    const isNonSelectableCriteria = (criteria: Criteria) =>
      pipe(({ name }) => name, getSelectableCriteriaByName, isNil)(criteria);

    const getSortId = ({ name }: Criteria) =>
      getSelectableCriteriaByName(name).sortId;

    return pipe(
      reject(isNonSelectableCriteria) as (criterias) => Array<Criteria>,
      sortBy(getSortId),
    )(filterWithParsedSearch.criterias);
  };

  const applyFilter = (filter: Filter): void => {
    setCurrentFilter(filter);
    setAppliedFilter(filter);
  };

  const isCurrentFilterApplied =
    equals(currentFilter, appliedFilter) &&
    equals(appliedFilter, filterWithParsedSearch);

  const applyCurrentFilter = (): void => {
    applyFilter(filterWithParsedSearch);
  };

  return {
    appliedFilter,
    applyCurrentFilter,
    applyFilter,
    currentFilter,
    customFilters,
    customFiltersLoading,
    editPanelOpen,
    filterWithParsedSearch,
    filters,
    getCriteriaValue,
    getMultiSelectCriterias,
    isCurrentFilterApplied,
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
