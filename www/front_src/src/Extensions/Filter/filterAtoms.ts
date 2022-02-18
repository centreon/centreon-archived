import { atom } from 'jotai';
import { atomWithDefault } from 'jotai/utils';
import { findIndex, lensPath, propEq, set as update } from 'ramda';

import { Criteria } from './Criterias/models';
import getDefaultCriterias from './Criterias/default';
import { build, parse } from './Criterias/searchQueryLanguage';

export const getFilterDefaultCriteriasDerivedAtom = atom(
  () => (): Array<Criteria> => {
    return getDefaultCriterias();
  },
);
export const currentFilterCriteriasAtom = atomWithDefault<Array<Criteria>>(
  (get) => get(getFilterDefaultCriteriasDerivedAtom)(),
);

export const appliedFilterCriteriasAtom = atomWithDefault<Array<Criteria>>(
  (get) => get(getFilterDefaultCriteriasDerivedAtom)(),
);

export const searchAtom = atom('');
export const sendingFilterAtom = atom(false);

export const filterWithParsedSearchDerivedAtom = atom((get) => {
  return parse(get(searchAtom), get(currentFilterCriteriasAtom));
});

export const getUpdatedFilterCriteriaDerivedAtom = atom(
  (get) =>
    ({ name, value }): Array<Criteria> => {
      const index = findIndex(propEq('name', name))(
        get(filterWithParsedSearchDerivedAtom),
      );

      const lens = lensPath([index, 'value']);

      return update(lens, value, get(filterWithParsedSearchDerivedAtom));
    },
);

export const applyFilterDerivedAtom = atom(
  null,
  (get, set, criterias: Array<Criteria>) => {
    set(currentFilterCriteriasAtom, criterias);
    set(appliedFilterCriteriasAtom, criterias);
    set(searchAtom, build(criterias));
  },
);

export const setFilterCriteriaDerivedAtom = atom(
  null,
  (get, set, { name, value, apply = false }) => {
    const getUpdatedFilterCriteria = get(getUpdatedFilterCriteriaDerivedAtom);

    const updatedFilter = getUpdatedFilterCriteria({ name, value });

    set(searchAtom, build(updatedFilter));

    if (apply) {
      set(applyFilterDerivedAtom, updatedFilter);

      return;
    }

    set(currentFilterCriteriasAtom, updatedFilter);
  },
);

export const applyCurrentFilterDerivedAtom = atom(null, (get, set) => {
  set(applyFilterDerivedAtom, get(filterWithParsedSearchDerivedAtom));
});

export const clearFilterDerivedAtom = atom(null, (_, set) => {
  set(applyFilterDerivedAtom, getDefaultCriterias());
});
