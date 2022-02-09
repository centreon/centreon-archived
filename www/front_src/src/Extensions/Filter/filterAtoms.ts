import { atom } from 'jotai';
import { atomWithDefault } from 'jotai/utils';
import { findIndex, lensPath, propEq, set as update } from 'ramda';

import { Criteria } from './Criterias/models';
import getDefaultCriterias from './Criterias/default';

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

export const getUpdatedFilterCriteriaDerivedAtom = atom(
  (get) =>
    ({ name, value }): Array<Criteria> => {
      const index = findIndex(propEq('name', name))(
        get(currentFilterCriteriasAtom),
      );

      const lens = lensPath([index, 'value']);

      return update(lens, value, get(currentFilterCriteriasAtom));
    },
);

export const applyFilterDerivedAtom = atom(
  null,
  (get, set, criterias: Array<Criteria>) => {
    set(currentFilterCriteriasAtom, criterias);
    set(appliedFilterCriteriasAtom, criterias);
  },
);

export const setFilterCriteriaDerivedAtom = atom(
  null,
  (get, set, { name, value, apply = false }) => {
    const getUpdatedFilterCriteria = get(getUpdatedFilterCriteriaDerivedAtom);

    const updatedFilter = getUpdatedFilterCriteria({ name, value });

    if (apply) {
      set(applyFilterDerivedAtom, updatedFilter);

      return;
    }

    set(currentFilterCriteriasAtom, updatedFilter);
  },
);

export const applyCurrentFilterDerivedAtom = atom(null, (get, set) => {
  set(applyFilterDerivedAtom, get(currentFilterCriteriasAtom));
});

export const clearFilterDerivedAtom = atom(null, (_, set) => {
  set(applyFilterDerivedAtom, getDefaultCriterias());
});
