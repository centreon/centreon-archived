import { atom } from 'jotai';
import { atomWithDefault, atomWithStorage } from 'jotai/utils';
import {
  find,
  findIndex,
  indexBy,
  isNil,
  lensPath,
  map,
  merge,
  mergeRight,
  mergeWith,
  pipe,
  prop,
  propEq,
  reduce,
  reject,
  set as update,
  values,
} from 'ramda';
import { TFunction } from 'react-i18next';

import { getUrlQueryParameters } from '@centreon/ui';

import { baseKey } from '../storage';
import { labelNewFilter } from '../translatedLabels';

import {
  Criteria,
  CriteriaDisplayProps,
  CriteriaValue,
  selectableCriterias,
} from './Criterias/models';
import {
  allFilter,
  Filter,
  isCustom,
  newFilter,
  resourceProblemsFilter,
  unhandledProblemsFilter,
} from './models';
import { build, parse } from './Criterias/searchQueryLanguage';
import { getStoredOrDefaultFilter } from './storedFilter';

export const filterKey = `${baseKey}filter`;

export const storedFilterAtom = atomWithStorage<Filter>(
  filterKey,
  unhandledProblemsFilter,
);

export const getDefaultFilterDerivedAtom = atom((): Filter => {
  const storedFilter = getStoredOrDefaultFilter(unhandledProblemsFilter);
  const urlQueryParameters = getUrlQueryParameters();
  const filterQueryParameter = urlQueryParameters.filter as Filter | undefined;

  const hasCriterias = Array.isArray(filterQueryParameter?.criterias);

  if (hasCriterias) {
    const filterFromUrl = urlQueryParameters.filter as Filter;

    const mergedCriterias = pipe(
      map(indexBy<Criteria>(prop('name'))),
      reduce(mergeWith(merge), {}),
      values,
    )([allFilter.criterias, filterFromUrl.criterias]);

    return {
      ...mergeRight(newFilter, filterFromUrl),
      criterias: mergedCriterias,
    };
  }

  return storedFilter;
});

export const customFiltersAtom = atom<Array<Filter>>([]);
export const currentFilterAtom = atomWithDefault<Filter>((get) =>
  get(getDefaultFilterDerivedAtom),
);
export const appliedFilterAtom = atomWithDefault<Filter>((get) =>
  get(getDefaultFilterDerivedAtom),
);
export const editPanelOpenAtom = atom(false);
export const searchAtom = atom('');
export const sendingFilterAtom = atom(false);

export const filterWithParsedSearchDerivedAtom = atom((get) => ({
  ...get(currentFilterAtom),
  criterias: [
    ...parse(get(searchAtom)),
    find(propEq('name', 'sort'), get(currentFilterAtom).criterias) as Criteria,
  ],
}));

export const getFilterWithUpdatedCriteriaDerivedAtom = atom(
  (get) =>
    ({ name, value }): Filter => {
      const index = findIndex(propEq('name', name))(
        get(filterWithParsedSearchDerivedAtom).criterias,
      );
      const lens = lensPath(['criterias', index, 'value']);

      return update(lens, value, get(filterWithParsedSearchDerivedAtom));
    },
);

export const setCriteriaDerivedAtom = atom(
  null,
  (get, set, { name, value = false }) => {
    const getFilterWithUpdatedCriteria = get(
      getFilterWithUpdatedCriteriaDerivedAtom,
    );

    set(currentFilterAtom, getFilterWithUpdatedCriteria({ name, value }));
  },
);

export const applyFilterDerivedAtom = atom(null, (get, set, filter: Filter) => {
  set(currentFilterAtom, filter);
  set(appliedFilterAtom, filter);
  set(searchAtom, build(filter.criterias));
});

export const setCriteriaAndNewFilterDerivedAtom = atom(
  null,
  (get, set, { name, value, apply = false }) => {
    const currentFilter = get(currentFilterAtom);
    const getFilterWithUpdatedCriteria = get(
      getFilterWithUpdatedCriteriaDerivedAtom,
    );

    const isCustomFilter = isCustom(currentFilter);
    const updatedFilter = {
      ...getFilterWithUpdatedCriteria({ name, value }),
      ...(!isCustomFilter && newFilter),
    };

    set(searchAtom, build(updatedFilter.criterias));

    if (apply) {
      set(applyFilterDerivedAtom, updatedFilter);

      return;
    }

    set(currentFilterAtom, updatedFilter);
  },
);

export const setNewFilterDerivedAtom = atom(null, (get, set, t: TFunction) => {
  const currentFilter = get(currentFilterAtom);

  if (isCustom(currentFilter)) {
    return;
  }

  const emptyFilter = {
    criterias: currentFilter.criterias,
    id: '',
    name: t(labelNewFilter),
  };

  set(currentFilterAtom, emptyFilter);
});

export const getCriteriaValueDerivedAtom = atom(
  (get) =>
    (name: string): CriteriaValue | undefined => {
      const filterWithParsedSearch = get(filterWithParsedSearchDerivedAtom);

      const criteria = find<Criteria>(propEq('name', name))(
        filterWithParsedSearch.criterias,
      );

      if (isNil(criteria)) {
        return undefined;
      }

      return criteria.value;
    },
);

export const applyCurrentFilterDerivedAtom = atom(null, (get, set) => {
  set(applyFilterDerivedAtom, get(filterWithParsedSearchDerivedAtom));
});

export const clearFilterDerivedAtom = atom(null, (_, set) => {
  set(applyFilterDerivedAtom, allFilter);
});

export const multiSelectCriteriasDerivedAtom = atom((get) => {
  const filterWithParsedSearch = get(filterWithParsedSearchDerivedAtom);

  const getSelectableCriteriaByName = (name: string): CriteriaDisplayProps =>
    selectableCriterias[name];

  const isNonSelectableCriteria = (criteria: Criteria): boolean =>
    pipe(({ name }) => name, getSelectableCriteriaByName, isNil)(criteria);

  return pipe(
    reject(isNonSelectableCriteria) as (criterias) => Array<Criteria>,
  )(filterWithParsedSearch.criterias);
});

export const filtersDerivedAtom = atom((get) => [
  unhandledProblemsFilter,
  allFilter,
  resourceProblemsFilter,
  ...get(customFiltersAtom),
]);
