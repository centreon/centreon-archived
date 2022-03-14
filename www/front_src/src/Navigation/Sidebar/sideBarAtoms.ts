import { atomWithStorage } from 'jotai/utils';

export const selectedNavigationItemsAtom = atomWithStorage<Record<
  string,
  propsSelectedNavigationItems
> | null>('selectedNavigationItems', null);

export interface propsSelectedNavigationItems {
  index: number | null;
  label: string;
  url?: string | null;
}
