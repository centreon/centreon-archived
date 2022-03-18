import { atomWithStorage } from 'jotai/utils';

import { Page } from '../models';

export const selectedNavigationItemsAtom = atomWithStorage<Record<
  string,
  propsSelectedNavigationItems
> | null>('selectedNavigationItems', null);

export const itemsHoveredByDefaultAtom = atomWithStorage<Record<
  string,
  Page | null
> | null>('itemsHoveredByDefault', null);

export interface SelectedNavigationItem {
  index: number | null;
  label: string;
  url?: string | null;
}
