import * as React from 'react';

import {
  find,
  findIndex,
  hasPath,
  isNil,
  lensPath,
  mergeDeepLeft,
  mergeDeepRight,
  pipe,
  propEq,
  reject,
  set,
} from 'ramda';
import { useTranslation } from 'react-i18next';

import {
  useRequest,
  setUrlQueryParameters,
  getUrlQueryParameters,
} from '@centreon/ui';

import { labelNewFilter } from '../translatedLabels';

import {
  getStoredOrDefaultFilter,
  clearCachedFilter,
  storeFilter,
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
  newFilter,
  isCustom,
  Filter,
} from './models';

type SearchDispatch = React.Dispatch<React.SetStateAction<string | undefined>>;
type EditPanelOpenDitpach = React.Dispatch<React.SetStateAction<boolean>>;
type CustomFiltersDispatch = React.Dispatch<
  React.SetStateAction<Array<Filter>>
>;

export interface FilterState {
  customFilters: Array<Filter>;
  filter: Filter;
  updatedFilter: Filter;
  setFilter: (filter: Filter) => void;
  setNewFilter: () => void;
  setCriteria: ({ name, value }: { name: string; value }) => void;
  nextSearch?: string;
  setNextSearch: SearchDispatch;
  getCriteriaValue: (name: string) => CriteriaValue | undefined;
  loadCustomFilters: () => Promise<Array<Filter>>;
  setCustomFilters: CustomFiltersDispatch;
  customFiltersLoading: boolean;
  editPanelOpen: boolean;
  setEditPanelOpen: EditPanelOpenDitpach;
  getMultiSelectCriterias: () => Array<Criteria>;
}

const useFilter = (): FilterState => {
  const { t } = useTranslation();
  const {
    sendRequest: sendListCustomFiltersRequest,
    sending: customFiltersLoading,
  } = useRequest({
    request: listCustomFilters,
    decoder: listCustomFiltersDecoder,
  });

  const getDefaultFilter = (): Filter => {
    const defaultFilter = getStoredOrDefaultFilter(unhandledProblemsFilter);

    const urlQueryParameters = getUrlQueryParameters();

    if (hasPath(['filter'], urlQueryParameters)) {
      return pipe(
        mergeDeepLeft(urlQueryParameters.filter as Filter) as (t) => Filter,
        mergeDeepRight(allFilter) as (t) => Filter,
      )(newFilter) as Filter;
    }

    return defaultFilter;
  };

  const getDefaultCriterias = (): Array<Criteria> =>
    getDefaultFilter().criterias;

  const getDefaultSearchCriteria = (): Criteria =>
    getDefaultCriterias().find(propEq('name', 'search')) as Criteria;

  const [customFilters, setCustomFilters] = React.useState<Array<Filter>>([]);
  const [filter, setFilter] = React.useState(getDefaultFilter());
  const [nextSearch, setNextSearch] = React.useState<string | undefined>(
    getDefaultSearchCriteria().value as string,
  );

  const [editPanelOpen, setEditPanelOpen] = React.useState<boolean>(false);

  const loadCustomFilters = (): Promise<Array<Filter>> => {
    return sendListCustomFiltersRequest().then(({ result }) => {
      setCustomFilters(result);

      return result;
    });
  };

  const getFilterWithUpdatedCriteria = ({ name, value }): Filter => {
    const index = findIndex(propEq('name', name))(filter.criterias);
    const lens = lensPath(['criterias', index, 'value']);

    return set(lens, value, filter);
  };

  React.useEffect(() => {
    loadCustomFilters();
  }, []);

  const setCriteria = ({ name, value }): void => {
    setFilter(getFilterWithUpdatedCriteria({ name, value }));
  };

  React.useEffect(() => {
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
  });

  const updatedFilter = getFilterWithUpdatedCriteria({
    name: 'search',
    value: nextSearch,
  });

  const setNewFilter = (): void => {
    if (isCustom(filter)) {
      return;
    }

    setFilter({ id: '', name: t(labelNewFilter), criterias: filter.criterias });
  };

  const getCriteriaValue = (name: string): CriteriaValue | undefined => {
    const criteria = find<Criteria>(propEq('name', name))(filter.criterias);

    if (isNil(criteria)) {
      return undefined;
    }

    return criteria.value;
  };

  const getMultiSelectCriterias = (): Array<Criteria> => {
    return reject<Criteria>(({ name }) => isNil(selectableCriterias[name]))(
      filter.criterias,
    );
  };

  return {
    filter,
    setFilter,
    setCriteria,
    updatedFilter,
    customFilters,
    nextSearch,
    setNextSearch,
    loadCustomFilters,
    setCustomFilters,
    customFiltersLoading,
    editPanelOpen,
    setEditPanelOpen,
    setNewFilter,
    getCriteriaValue,
    getMultiSelectCriterias,
  };
};

export default useFilter;
