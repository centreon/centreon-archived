import { atomWithStorage } from 'jotai/utils';

export const navigationItemSelectedAtom = atomWithStorage<Record<
  string,
  propsNavigationItemSelected
> | null>('navigationItemSelected', null);

export interface propsNavigationItemSelected {
  index: number | null;
  label: string;
  url?: string | null;
}
