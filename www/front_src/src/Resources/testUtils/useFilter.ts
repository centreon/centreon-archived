import { useEffect } from 'react';

import { omit } from 'ramda';
import useDeepCompareEffect from 'use-deep-compare-effect';
import { useAtomValue, useUpdateAtom } from 'jotai/utils';
import { useAtom } from 'jotai';

import {
  useRequest,
  setUrlQueryParameters,
  getUrlQueryParameters
} from '@centreon/ui';

import {
  applyCurrentFilterDerivedAtom,
  applyFilterDerivedAtom,
  currentFilterAtom,
  customFiltersAtom,
  editPanelOpenAtom,
  filterWithParsedSearchDerivedAtom,
  getCriteriaValueDerivedAtom,
  searchAtom,
  setCriteriaDerivedAtom,
  storedFilterAtom
} from '../Filter/filterAtoms';
import { listCustomFilters } from '../Filter/api';
import { listCustomFiltersDecoder } from '../Filter/api/decoders';
import { Filter } from '../Filter/models';
import { build } from '../Filter/Criterias/searchQueryLanguage';
import { FilterState } from '../Filter/useFilter';

const useFilter = (): FilterState => {
  const {
    sendRequest: sendListCustomFiltersRequest,
    sending: customFiltersLoading
  } = useRequest({
    decoder: listCustomFiltersDecoder,
    request: listCustomFilters
  });

  const [customFilters, setCustomFilters] = useAtom(customFiltersAtom);
  const [currentFilter, setCurrentFilter] = useAtom(currentFilterAtom);
  const filterWithParsedSearch = useAtomValue(
    filterWithParsedSearchDerivedAtom
  );
  const getCriteriaValue = useAtomValue(getCriteriaValueDerivedAtom);
  const defaultFilter = useAtomValue(storedFilterAtom);
  const setSearch = useUpdateAtom(searchAtom);
  const applyFilter = useUpdateAtom(applyFilterDerivedAtom);
  const applyCurrentFilter = useUpdateAtom(applyCurrentFilterDerivedAtom);
  const setCriteria = useUpdateAtom(setCriteriaDerivedAtom);
  const storeFilter = useUpdateAtom(storedFilterAtom);
  const setEditPanelOpen = useUpdateAtom(editPanelOpenAtom);

  const loadCustomFilters = (): Promise<Array<Filter>> => {
    return sendListCustomFiltersRequest().then(({ result }) => {
      setCustomFilters(result.map(omit(['order'])));

      return result;
    });
  };

  useEffect(() => {
    loadCustomFilters();
  }, []);

  useDeepCompareEffect(() => {
    setSearch(build(currentFilter.criterias));
  }, [currentFilter.criterias]);

  useEffect(() => {
    if (getUrlQueryParameters().fromTopCounter) {
      return;
    }

    storeFilter(filterWithParsedSearch);

    const queryParameters = [
      {
        name: 'filter',
        value: filterWithParsedSearch
      }
    ];

    setUrlQueryParameters(queryParameters);
  }, [filterWithParsedSearch]);

  useEffect(() => {
    if (!getUrlQueryParameters().fromTopCounter) {
      return;
    }

    setUrlQueryParameters([
      {
        name: 'fromTopCounter',
        value: false
      }
    ]);

    applyFilter(defaultFilter);
  }, [getUrlQueryParameters().fromTopCounter]);

  return {
    applyCurrentFilter,
    currentFilter,
    customFilters,
    customFiltersLoading,
    getCriteriaValue,
    loadCustomFilters,
    setCriteria,
    setCurrentFilter,
    setEditPanelOpen
  };
};

export default useFilter;
