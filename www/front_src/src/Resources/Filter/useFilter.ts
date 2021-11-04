import * as React from 'react';

import { omit } from 'ramda';
import useDeepCompareEffect from 'use-deep-compare-effect';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';

import {
  useRequest,
  setUrlQueryParameters,
  getUrlQueryParameters,
} from '@centreon/ui';

import { listCustomFilters } from './api';
import { listCustomFiltersDecoder } from './api/decoders';
import { Filter } from './models';
import { build } from './Criterias/searchQueryLanguage';
import {
  applyFilterDerivedAtom,
  currentFilterAtom,
  customFiltersAtom,
  filterWithParsedSearchDerivedAtom,
  searchAtom,
  storedFilterAtom,
} from './filterAtoms';
import { CriteriaValue } from './Criterias/models';

export interface FilterState {
  applyCurrentFilter?: () => void;
  currentFilter?: Filter;
  customFilters?: Array<Filter>;
  customFiltersLoading: boolean;
  getCriteriaValue?: (name: string) => CriteriaValue | undefined;
  loadCustomFilters: () => Promise<Array<Filter>>;
  setCriteria?: ({ name, value }: { name: string; value }) => void;
  setCurrentFilter?: (filter: Filter) => void;
  setEditPanelOpen?: (update: boolean) => void;
}

const useFilter = (): FilterState => {
  const {
    sendRequest: sendListCustomFiltersRequest,
    sending: customFiltersLoading,
  } = useRequest({
    decoder: listCustomFiltersDecoder,
    request: listCustomFilters,
  });

  const currentFilter = useAtomValue(currentFilterAtom);
  const filterWithParsedSearch = useAtomValue(
    filterWithParsedSearchDerivedAtom,
  );
  const defaultFilter = useAtomValue(storedFilterAtom);
  const setCustomFilters = useUpdateAtom(customFiltersAtom);
  const setSearch = useUpdateAtom(searchAtom);
  const applyFilter = useUpdateAtom(applyFilterDerivedAtom);
  const storeFilter = useUpdateAtom(storedFilterAtom);

  const loadCustomFilters = (): Promise<Array<Filter>> => {
    return sendListCustomFiltersRequest().then(({ result }) => {
      setCustomFilters(result.map(omit(['order'])));

      return result;
    });
  };

  React.useEffect(() => {
    loadCustomFilters();
  }, []);

  useDeepCompareEffect(() => {
    setSearch(build(currentFilter.criterias));
  }, [currentFilter.criterias]);

  React.useEffect(() => {
    if (getUrlQueryParameters().fromTopCounter) {
      return;
    }

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

    applyFilter(defaultFilter);
  }, [getUrlQueryParameters().fromTopCounter]);

  return {
    customFiltersLoading,
    loadCustomFilters,
  };
};

export default useFilter;
