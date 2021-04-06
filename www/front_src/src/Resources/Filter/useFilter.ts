import * as React from 'react';

import {
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

import {
  clearCachedFilter,
  clearCachedFilterExpanded,
  storeFilter,
  storeFilterExpanded,
} from './storedFilter';
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
import { getDefaultFilter, getDefaultFilterExpanded } from './default';

type SearchDispatch = React.Dispatch<React.SetStateAction<string | undefined>>;
type EditPanelOpenDitpach = React.Dispatch<React.SetStateAction<boolean>>;
type CustomFiltersDispatch = React.Dispatch<
  React.SetStateAction<Array<Filter>>
>;

export interface FilterState {
  customFilters: Array<Filter>;
  customFiltersLoading: boolean;
  editPanelOpen: boolean;
  filter: Filter;
  filterExpanded: boolean;
  filters: Array<Filter>;
  getCriteriaValue: (name: string) => CriteriaValue | undefined;
  getMultiSelectCriterias: () => Array<Criteria>;
  loadCustomFilters: () => Promise<Array<Filter>>;
  nextSearch?: string;
  setCriteria: ({ name, value }: { name: string; value }) => void;
  setCriteriaAndNewFilter: ({ name, value }: { name: string; value }) => void;
  setCustomFilters: CustomFiltersDispatch;
  setEditPanelOpen: EditPanelOpenDitpach;
  setFilter: (filter: Filter) => void;
  setNewFilter: () => void;
  setNextSearch: SearchDispatch;
  toggleFilterExpanded: () => void;
  updatedFilter: Filter;
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

  const getDefaultCriterias = (): Array<Criteria> =>
    getDefaultFilter().criterias;

  const getDefaultSearchCriteria = (): Criteria =>
    getDefaultCriterias().find(propEq('name', 'search')) as Criteria;

  const [customFilters, setCustomFilters] = React.useState<Array<Filter>>([]);
  const [filter, setFilter] = React.useState(getDefaultFilter());
  const [nextSearch, setNextSearch] = React.useState<string | undefined>(
    getDefaultSearchCriteria().value as string,
  );
  const [filterExpanded, setFilterExpanded] = React.useState(
    getDefaultFilterExpanded(),
  );

  const [editPanelOpen, setEditPanelOpen] = React.useState<boolean>(false);

  const loadCustomFilters = (): Promise<Array<Filter>> => {
    return sendListCustomFiltersRequest().then(({ result }) => {
      setCustomFilters(result.map(omit(['order'])));

      return result;
    });
  };

  const getFilterWithUpdatedCriteria = ({ name, value }): Filter => {
    const index = findIndex(propEq('name', name))(filter.criterias);
    const lens = lensPath(['criterias', index, 'value']);

    return set(lens, value, filter);
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
    setFilter(getFilterWithUpdatedCriteria({ name, value }));
  };

  const setCriteriaAndNewFilter = ({ name, value }): void => {
    const isCustomFilter = isCustom(filter);

    setFilter({
      ...getFilterWithUpdatedCriteria({ name, value }),
      ...(!isCustomFilter && newFilter),
    });
  };

  useDeepCompareEffect(() => {
    setCriteria({ name: 'search', value: nextSearch });
  }, [...reject(propEq('name', 'search'), filter.criterias)]);

  React.useEffect(() => {
    const updatedFilter = getFilterWithUpdatedCriteria({
      name: 'search',
      value: nextSearch,
    });

    storeFilter(updatedFilter);

    const queryParameters = [
      {
        name: 'filter',
        value: updatedFilter,
      },
    ];

    setUrlQueryParameters(queryParameters);
  }, [filter, nextSearch]);

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

    setFilter(getDefaultFilter());
    const { criterias } = getDefaultFilter();
    const search = find<Criteria>(propEq('name', 'search'))(criterias);
    setNextSearch((search?.value as string) || '');
  }, [getUrlQueryParameters().fromTopCounter]);

  React.useEffect(() => (): void => {
    clearCachedFilter();
    clearCachedFilterExpanded();
  });

  React.useEffect(() => {
    setUrlQueryParameters([
      {
        name: 'filterExpanded',
        value: filterExpanded,
      },
    ]);

    storeFilterExpanded(filterExpanded);
  }, [filterExpanded]);

  const updatedFilter = getFilterWithUpdatedCriteria({
    name: 'search',
    value: nextSearch,
  });

  const setNewFilter = (): void => {
    if (isCustom(filter)) {
      return;
    }

    setFilter({ criterias: filter.criterias, id: '', name: t(labelNewFilter) });
  };

  const getCriteriaValue = (name: string): CriteriaValue | undefined => {
    const criteria = find<Criteria>(propEq('name', name))(filter.criterias);

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
    )(filter.criterias);
  };

  const toggleFilterExpanded = (): void => {
    setFilterExpanded(!filterExpanded);
  };

  return {
    customFilters,
    customFiltersLoading,
    editPanelOpen,
    filter,
    filterExpanded,
    filters,
    getCriteriaValue,
    getMultiSelectCriterias,
    loadCustomFilters,
    nextSearch,
    setCriteria,
    setCriteriaAndNewFilter,
    setCustomFilters,
    setEditPanelOpen,
    setFilter,
    setNewFilter,
    setNextSearch,
    toggleFilterExpanded,
    updatedFilter,
  };
};

export default useFilter;
