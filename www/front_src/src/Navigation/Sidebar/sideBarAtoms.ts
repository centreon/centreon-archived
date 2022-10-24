import { atom } from 'jotai';
import { atomWithStorage } from 'jotai/utils';
import { isNil, keys, omit } from 'ramda';

import { Page } from '../models';

export const selectedNavigationItemsAtom = atomWithStorage<Record<
  string,
  Page
> | null>('selectedNavigationItems', null);

export const hoveredNavigationItemsAtom = atom<Record<string, Page> | null>(
  null
);

export const setHoveredNavigationItemsDerivedAtom = atom(
  null,
  (get, set, { levelName, currentPage }) => {
    const navigationKeysToRemove = keys(get(hoveredNavigationItemsAtom)).filter(
      (navigationItem) => {
        return navigationItem > levelName;
      }
    );

    if (isNil(navigationKeysToRemove)) {
      set(hoveredNavigationItemsAtom, {
        ...get(hoveredNavigationItemsAtom),
        [levelName]: currentPage
      });

      return;
    }
    set(hoveredNavigationItemsAtom, {
      ...omit(navigationKeysToRemove, get(hoveredNavigationItemsAtom)),
      [levelName]: currentPage
    });
  }
);
